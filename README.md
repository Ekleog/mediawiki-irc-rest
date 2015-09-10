mediawiki-irc-rest
==================

This MediaWiki plugin aims to forward any modification towards an IRC channel.

It uses [node-irc-multibot](https://github.com/BinetReseau/node-irc-multibot/),
this way it can manage nickname registration and ChanServ-based invitation
without hassle.

Installation
============

Just copy the IRCNotifyRest into the `extensions/` directory and add the
following lines to your `LocalSettings.php` file:

```
$ircnotify_url = "";
$ircnotify_key = "";
require_once "extensions/IRCNotifyRest/IRCNotifyRest.php";
```

Configuration
=============

There are three things you will want to configure:

* `$ircnotify_url` should point to your node-irc-multibot entrypoint

* `$ircnotify_key` is the token you have set in node-irc-multibot's bot.yml

* If want to translate the messages (currently displayed in French given it is
the language of the wiki we are using it with), create a new JSON file in
`IRCNotifyRest/i18n/` (e.g., `IRCNotifyRest/i18n/en.json`), copy and paste the
content from an existing one (e.g., `IRCNotifyRest/i18n/fr.json`) and
enjoy translating ;)
