mediawiki-irc-rest
==================

This MediaWiki plugin aims to forward any modification towards an IRC channel.

It uses [node-irc-multibot](https://github.com/BinetReseau/node-irc-multibot/),
this way it can manage nickname registration and ChanServ-based invitation
without hassle.

Installation
============

Just copy the file somewhere and include it from your `LocalSettings.php` file.

Configuration
=============

There are three things you will want to configure:

* `$ircnotify_url` should point to your node-irc-multibot entrypoint

* `$ircnotify_key` is the token you have set in node-irc-multibot's bot.yml

* If want to translate the messages (currently displayed in French given it is
the language I am using it with), you will want to edit everything appended to
`$msg` in `ircnotify_rest_pagesave`, and to change the parameter to
`ircnotify_rest_send` in `ircnotify_rest_delete`, `ircnotify_rest_undelete`
and `ircnotify_rest_move`. Besides, there are a number of free online
translators that should help you tackle with French. ;)
