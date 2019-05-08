# Rename wp-login.php

[![WordPress Plugin version](https://img.shields.io/wordpress/plugin/v/rename-wp-login.svg?style=flat)](https://wordpress.org/plugins/rename-wp-login/)
[![WordPress Plugin WP tested version](https://img.shields.io/wordpress/v/rename-wp-login.svg?style=flat)](https://wordpress.org/plugins/rename-wp-login/)
[![WordPress Plugin downloads](https://img.shields.io/wordpress/plugin/dt/rename-wp-login.svg?style=flat)](https://wordpress.org/plugins/rename-wp-login/)
[![WordPress Plugin rating](https://img.shields.io/wordpress/plugin/r/rename-wp-login.svg?style=flat)](https://wordpress.org/plugins/rename-wp-login/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/iseulde/rename-wp-login/blob/master/license.txt)
[![Travis](https://secure.travis-ci.org/iseulde/rename-wp-login.png?branch=master)](http://travis-ci.org/iseulde/rename-wp-login)
[![Code Climate](https://codeclimate.com/github/iseulde/rename-wp-login/badges/gpa.svg)](https://codeclimate.com/github/iseulde/rename-wp-login)

*Rename wp-login.php* is a very light plugin that lets you easily and safely change wp-login.php to anything you want. It doesn’t literally rename or change files in core, nor does it add rewrite rules. It simply intercepts page requests and works on any WordPress website. The wp-admin directory and wp-login.php page become inaccessible, so you should bookmark or remember the url. Deactivating this plugin brings your site back exactly to the state it was before.

### Compatibility

All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.

It’s also compatible with any plugin that hooks in the login form, including

* BuddyPress,
* bbPress,
* Limit Login Attempts,
* and User Switching.

Obviously it doesn’t work with plugins that *hardcoded* wp-login.php.

Works with multisite, but not tested with subdomains. Activating it for a network allows you to set a networkwide default. Individual sites can still rename their login page to something else.

If you’re using a **page caching plugin** you should add the slug of the new login url to the list of pages not to cache.

If you wish, you can block wp-login.php with `.htaccess` from now on.

## Installation

1. Go to Plugins › Add New.
2. Search for *Rename wp-login.php*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Rename wp-login.php there.
5. You can change this option any time you want, just go back to Settings › Permalinks › Rename wp-login.php.

## I forgot my login url!

Either go to your MySQL database and look for the value of `rwl_page` in the options table, or remove the `rename-wp-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

On a multisite install the `rwl_page` option will be in the sitemeta table, if there is no such option in the options table.
