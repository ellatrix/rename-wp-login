# Rename wp-login.php

**Contributors:** avryl  
**Tags:** rename, login, wp-login, wp-login.php, brute force, attacks  
**Requires at least:** 3.6  
**Tested up to:** 3.6.1  
**Stable tag:** 1.5  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to whatever you want. It can also prevent a lot of brute force attacks.

## Description

With this plugin you can change wp-login.php to anything you want. The default is example.com/**login**/, and you can change this under Settings › Permalinks › Login.
Please bookmark or remember your login url, accessing wp-login.php or wp-admin will return a 404 not found status.

### Compatibility

Works perfectly on WordPress 3.6 or higher. The registration form, lost password form, login widget and expired sessions will keep working.

Compatible with plugins like:

* BuddyPress,
* Limit Login Attempts,
* User Switching,
* and any other plugin that hooks into the standard login form.

It does’t work with plugins that hardcoded wp-login.php, obviously.
You also must have pretty, or almost pretty, permalinks enabled.

If you’re using a **page caching plugin** like **W3 Total Cache** or **WP Super Cache**, add the word you renamed wp-login.php to (e.g. login) to the list of pages not to cache.

* For W3 Total Cache go to Performance › Page Cache › Advanced › Never cache the following pages, add your new login page on a new line and save all settings.
* For WP Super Cache go to Settings › WP Super Cache › Advanced › Accepted Filenames & Rejected URIs, add your new login page on a new line and save.

This plugin is **not** yet tested on installs that force **SSL** or use the **multisite** feature. I would appreciate any help with testing this.

### Benefits

Not only does it allow you to further customise your login page, it also prevents brute force attacks that are targeted specifically to wp-login.php. wp-login.php, and wp-admin if not logged in, will return a 404 not found status.

While you could use this plugin to prevent a lot of brute force attacks, it does not mean you don’t need a strong password. Read [this codex article](http://codex.wordpress.org/Brute_Force_Attacks) for more information on how to protect your website.

If you want to keep your login url secret, you should make sure there aren’t any links pointing to it on your website.

### Installation

1. Go to Plugins › Add New.
2. Search for *Rename wp-login.php*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Rename wp-login.php in the section Login.
5. You can change this option any time you want, just go back to Settings › Permalinks › Login.

### Frequently Asked Questions

#### I forgot my login url!

Either go to your MySQL database and look for the value of `rwl_page` in the options table, or remove the `rename-wp-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

### Changelog

#### 1.6

* Fixed the login link when `site_url()` ≠ `home_url()`

#### 1.5

* Made [User Switching](http://wordpress.org/plugins/user-switching/) compatible.

#### 1.4

* Faster page load.
* Fixed 404 error for permalink structures with a prefixed path. “Almost pretty” permalinks work now too.
* Code clean-up.

#### 1.3

* Prevents the plugin from working when there is no permalink structure.

#### 1.2

* Fixed status code custom login page.

#### 1.1

* Blocked access to wp-admin to prevent a redirect the the new login page.

#### 1.0

* Initial version.

### Upgrade Notice

#### 1.6

Always immediately update this plugin please!