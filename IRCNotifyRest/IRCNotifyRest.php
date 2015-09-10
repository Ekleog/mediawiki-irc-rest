<?php
/**
 * @file
 * @ingroup Extensions
 * @author Ekleog <leo@gaspard.io>
 * Mediawiki extension designed for integration with node-irc-multibot
 */

$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'Mediawiki IRC rest',
        'author' => array(
                'Ekleog',
                'Ilaï Deutel'
        ),
        'version'  => '1.1',
        'url' => 'https://github.com/Ekleog/mediawiki-irc-rest',
);

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
        $msg = "${color}7${bold}$user${norm} a fait un changement " .
                "${color}6" . ($isminor ? "mineur" : "majeur") . "${norm}" .
                " dans l'article ${color}11${bold}$article->mTitle${norm} " .
                "[${under}$article_link${norm}]\n";
        if ($summary)
                $msg .= "   ${color}15Commentaire: $summary${norm}\n";
        $rc = $revision ? $revision->getRecentChange() : NULL;
        if ($rc) {
                $diff = $rc->diffLinkTrail();
                if ($diff)
                        $msg .= "   Modifications : $article_link?" . $diff;
                else
                        $msg .= "   Création :";
                $oldlen = $rc->mAttribs['rc_old_len'];
                $newlen = $rc->mAttribs['rc_new_len'];
                $difflen = $newlen - $oldlen;
                $difflen = ($difflen >= 0) ? "${color}9+$difflen${norm}" : "${color}4$difflen${norm}";
                $msg .= " ($newlen octets) ($difflen)\n";
        } else {
                $msg .= "   Impossible de trouver plus d'informations";
        }

        ircnotify_rest_send($msg);
}

function ircnotify_rest_delete($article, $user, $reason, $id, $content, $logEntry) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $article_link = $article->getTitle()->getFullURL();
        ircnotify_rest_send("${color}7${bold}$user${norm} a ${color}4supprimé${norm} ${under}$article_link${norm}");
}

function ircnotify_rest_undelete($title, $create, $comment, $oldPageId) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $article_link = $title->getFullURL();
        ircnotify_rest_send("${color}7${bold}Quelqu'un${norm} a ${color}9annulé la suppression${norm} de ${under}$article_link${norm}");
}

function ircnotify_rest_move($title, $newTitle, $user, $oldid, $newid, $reason = null) {
        $color = "\x03";
        $bold  = "\x02";
        $under = "\x1F";
        $norm  = "\x0F";
        $old_link = $title->getFullURL();
        $new_link = $newTitle->getFullURL();
        ircnotify_rest_send("${color}7${bold}$user${norm} a ${color}15déplacé${norm} ${under}$old_link${norm} vers ${under}$new_link${norm}");
}

$wgHooks['PageContentSaveComplete'][] = array('ircnotify_rest_pagesave');
$wgHooks['ArticleDeleteComplete'][] = array('ircnotify_rest_delete');
$wgHooks['ArticleUndelete'][] = array('ircnotify_rest_undelete');
$wgHooks['TitleMoveComplete'][] = array('ircnotify_rest_move');
