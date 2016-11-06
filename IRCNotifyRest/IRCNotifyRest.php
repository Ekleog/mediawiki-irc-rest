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
    $article_link = $article->getTitle()->getFullURL('', false, PROTO_HTTPS);

    $msg = wfMessage('ircnotifyrest-pagesave')->rawParams(
        $user,
        wfMessage('ircnotifyrest-' . ($isminor ? 'minor' : 'major'))->plain(),
        $article->mTitle,
        $article_link
    )->plain();

    if ($summary)
        $msg .= wfMessage('ircnotifyrest-summary')->rawParams("$summary")->plain();

    $rc = $revision ? $revision->getRecentChange() : NULL;
    if ($rc) {
        $diff = $rc->diffLinkTrail();
        $oldlen = $rc->mAttribs['rc_old_len'];
        $newlen = $rc->mAttribs['rc_new_len'];
        $difflen = $newlen - $oldlen;
        $difflen = ($difflen >= 0) ? "\x039+$difflen\x0F" : "\x034$difflen\x0F";

        $msg .= $diff
            ? wfMessage('ircnotifyrest-diff')->rawParams(
                "$article_link?$diff",
                $newlen,
                $difflen
            )->plain()
            : wfMessage('ircnotifyrest-creation')->rawParams(
                $newlen,
                $difflen
            )->plain();
    } else {
        $msg .= wfMessage('ircnotifyrest-unabletofindmoreinfo')->plain();
    }

    ircnotify_rest_send($msg);
}

function ircnotify_rest_delete($article, $user, $reason, $id, $content, $logEntry) {
    $reason = $reason ? $reason : '';
    ircnotify_rest_send(wfMessage('ircnotifyrest-delete')
                        ->rawParams($user, $article->getTitle()->getFullURL('', false, PROTO_HTTPS), $reason)
                        ->plain());
}

function ircnotify_rest_undelete($title, $create, $comment, $oldPageId) {
    $comment = $comment ? $comment : '';
    ircnotify_rest_send(wfMessage('ircnotifyrest-undelete')
                        ->rawParams($title->getFullURL('', false, PROTO_HTTPS), $comment)
                        ->plain());
}

function ircnotify_rest_move($title, $newTitle, $user, $oldid, $newid, $reason = null) {
    $reason = $reason ? $reason : '';
    ircnotify_rest_send(wfMessage('ircnotifyrest-move')
                        ->rawParams($user, $title->getFullURL('', false, PROTO_HTTPS), $newTitle->getFullURL(), $reason)
                        ->plain());
}

$wgHooks['PageContentSaveComplete'][] = array('ircnotify_rest_pagesave');
$wgHooks['ArticleDeleteComplete'][] = array('ircnotify_rest_delete');
$wgHooks['ArticleUndelete'][] = array('ircnotify_rest_undelete');
$wgHooks['TitleMoveComplete'][] = array('ircnotify_rest_move');
