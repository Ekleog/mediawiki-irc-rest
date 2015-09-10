<?php
/**
 * @file
 * @ingroup Extensions
 * @author Léo Gaspard (Ekleog) <leo@gaspard.io>
 * @author Ilaï Deutel
 * Mediawiki extension designed for integration with node-irc-multibot
 */

$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'IRC Notify Rest',
        'author' => array(
                'Léo Gaspard',
                'Ilaï Deutel'
        ),
        'version' => '1.1',
        'url' => 'https://github.com/Ekleog/mediawiki-irc-rest',
        'descriptionmsg' => 'ircnotifyrest-desc'
);

$wgMessagesDirs['IRCNotifyRest'] = __DIR__ . '/i18n';

//$ircnotify_url and $ircnotify_key should be defined in LocalSettings.php
if (!isset($ircnotify_url) || !isset($ircnotify_key))
        return;

function ircnotify_rest_send($message) {
        global $ircnotify_url, $ircnotify_key;

        $data = array(
                "key"     => $ircnotify_key,
                "message" => $message,
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $ircnotify_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        curl_close($curl);
}

function ircnotify_rest_pagesave($article, $user, $content, $summary, $isminor, $iswatch, $section, $flags, $revision, $status, $baseRevId) {
        $article_link = $article->getTitle()->getFullURL();

        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $msg = wfMessage('ircnotifyrest-user')->rawParams("${color}7${bold}$user${norm}")->plain() .
               " ${color}6" . wfMessage('ircnotifyrest-' . ($isminor ? 'minor' : 'major')) . "${norm} " .
               wfMessage('ircnotifyrest-inpagename')->rawParams("${color}11${bold}$article->mTitle${norm}")->plain() .
               " [${under}$article_link${norm}]\n";
        if ($summary)
                $msg .= "   ${color}15" .
                        wfMessage('ircnotifyrest-inpagename')->rawParams("$summary")->plain() .
                        "${norm}\n   ";
        $rc = $revision ? $revision->getRecentChange() : NULL;
        if ($rc) {
                $diff = $rc->diffLinkTrail();
                $msg .= $diff ? wfMessage('ircnotifyrest-diff')->rawParams("$article_link?" . $diff)->plain() : wfMessage('ircnotifyrest-creation')->plain();
                $oldlen = $rc->mAttribs['rc_old_len'];
                $newlen = $rc->mAttribs['rc_new_len'];
                $difflen = $newlen - $oldlen;
                $difflen = ($difflen >= 0) ? "${color}9+$difflen${norm}" : "${color}4$difflen${norm}";
                $msg .= " ($newlen " . wfMessage('ircnotifyrest-bytes')->plain() . ") ($difflen)\n";
        } else {
                $msg .= wfMessage('ircnotifyrest-unabletofindmoreinfo')->plain();
        }

        ircnotify_rest_send($msg);
}

function ircnotify_rest_delete($article, $user, $reason, $id, $content, $logEntry) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $article_link = $article->getTitle()->getFullURL();
        ircnotify_rest_send(wfMessage('ircnotifyrest-delete')
                            ->rawParams("${color}7${bold}$user${norm}", "${color}4", "${norm}", "${under}$article_link${norm}")
                            ->plain());
}

function ircnotify_rest_undelete($title, $create, $comment, $oldPageId) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $article_link = $title->getFullURL();
        ircnotify_rest_send(wfMessage('ircnotifyrest-undelete')
                            ->rawParams("${color}7${bold}", "${norm}", "${color}9", "${norm}", "${under}$article_link${norm}")
                            ->plain());
}

function ircnotify_rest_move($title, $newTitle, $user, $oldid, $newid, $reason = null) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $old_link = $title->getFullURL();
        $new_link = $newTitle->getFullURL();
        ircnotify_rest_send(wfMessage('ircnotifyrest-move')
                            ->rawParams("${color}7${bold}$user${norm}", "${color}15", "${norm}", "${under}$old_link${norm}", "${under}$new_link${norm}")
                            ->plain());
}

$wgHooks['PageContentSaveComplete'][] = array('ircnotify_rest_pagesave');
$wgHooks['ArticleDeleteComplete'][] = array('ircnotify_rest_delete');
$wgHooks['ArticleUndelete'][] = array('ircnotify_rest_undelete');
$wgHooks['TitleMoveComplete'][] = array('ircnotify_rest_move');
