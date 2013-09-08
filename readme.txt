=== Rename wp-login.php ===

Contributors: avryl
Tags: rename, login, wp-login, wp-login.php, brute force, attacks
Requires at least: 3.6
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.

== Description ==

= What it does =

This plugin renames wp-login.php to whatever you want. The default is example.com/**login**/ if no such page already exists. Otherwise it will append a number, e.g. login-1.
You can change this under Settings › Permalinks › Login.
Please remember what you changed your login page to, accessing wp-login.php or wp-admin will not work and will return a 404 not found status.

= Compatibility =

Works with **BuddyPress**, **Limit Login Attempts** and most other plugins that customise the login page.
This plugin **doesn’t** break the registration form, lost password form, expired sessions or any of wp-login.php’s functionality. Plugins that hook into the standard login form will keep working.
It doesn’t break `wp_login_form()`, so the login widget will work too.

While it might work with earlier versions of WordPress, you should always update WordPress to the latest version.

If you’re using a **page caching plugin** like **W3 Total Cache** or **WP Super Cache**, add the word you renamed wp-login.php to (e.g. login) to the list of pages not to cache.

* For W3 Total Cache go to Performance › Page Cache › Advanced › Never cache the following pages, add your new login page on a new line and save all settings.
* For WP Super Cache go to Settings › WP Super Cache › Advanced › Accepted Filenames & Rejected URIs, add your new login page on a new line and save.

This plugin is **not** yet tested on installs that force **SSL** or use the **multisite** feature. I would appreciate any help with testing this.

= Benefits =

Not only does it allow you to further customise your login page, it also prevents brute force attacks that are targeted specifically to wp-login.php. wp-login.php will return a 404 not found status code, and wp-admin as well if you’re not logged in, as it would otherwise reveal the location of your new login page.

I made this plugin primarily because a client’s host blocked wp-login.php with an annoying Captcha. On some bigger websites Limit Login Atttempts also showed us that a lot of bots were trying to gain access through wp-login.php.

While you could use this plugin to prevent a lot of brute force attacks, it does not mean you don’t need a strong password. Read [this codex article](http://codex.wordpress.org/Brute_Force_Attacks) for more information on how to protect your website.

== Installation ==

1. Go to Plugins › Add New.
2. Search for *Rename wp-login.php*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Rename wp-login.php in the section Login.
5. You can change this option any time you want, just go back to Settings › Permalinks › Login.

== Frequently Asked Questions ==

= I forgot my login url!  =

There are two ways to solve your problem:

1. go to your MySQL database and look for the value of `rwl_page` in the options table, or
2. remove the `rename-wp-login` folder from your `plugins` folder, log in through the standard wp-login.php and reinstall the plugin.

== Changelog ==

= 1.4 =

* Faster page load.
* Fixed 404 error for permalink structures with a prefixed path. “Almost pretty” permalinks work now too.
* Code clean-up.

= 1.3 =

* Prevents the plugin from working when there is no permalink structure.

= 1.2 =

* Fixed status code custom login page.

= 1.1 =

* Blocked access to wp-admin to prevent a redirect the the new login page.

= 1.0 =

* Initial version.

== Upgrade Notice ==

= 1.4 =

Always immediately update this plugin please!