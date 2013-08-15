=== Plugin Name ===
Contributors: avryl
Tags: rename, login, wp-login, wp-login.php, brute force, attacks
Requires at least: 3.5
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Rename wp-login, and block it to prevent brute force attacks.

== Description ==

This plugin renames `wp-login.php` to whatever you want. The default is `login` if no such page already exists. Otherwise it will append a number, e.g. `login-1`. You can change this option under ‘Settings’ › ‘Permalinks’ › ‘Login’.

What are the benefits? Not only does it allow you to further customise your login page, it also prevents brute force attacks that are targeted specifically to the standard `wp-login.php`. `wp-login.php` will be blocked and returns a 404 status.

If you’re using a page caching plugin like **W3 Total Cache** or **WP Super Cache**, add the word you rename `wp-login.php` to (e.g. `login`) to the list of pages not to cache. For W3 Total Cache go to ‘Performance’ › ‘Page Cache’ › ‘Advanced’ › ‘Never cache the following pages’, add your new login page on a new line and save all settings. For WP Super Cache go to ‘Settings’ › ‘WP Super Cache’ › ‘Advanced’ › ‘Accepted Filenames & Rejected URIs’, add your new login page on a new line and save.

This plugin works with plugins that customise or hook into the standard login screen. It’s not yet tested on installs that force SSL or use the multisite feature.

== Installation ==

1. Go to ‘Plugins’ › ‘Add New’.
2. Search for ‘Rename wp-login’.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Rename `wp-login.php` in the section ‘Login’.
5. You can change this option any time you want, just go back to ‘Settings’ › ‘Permalinks’ › ‘Login’.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).

== Changelog ==

= 1.0 =
* Initial version.

== Upgrade Notice ==