=== WPS Hide Login ===

Contributors: tabrisrp, WPServeur
Tags: rename, login, wp-login, wp-login.php, custom login url
Requires at least: 4.1
Tested up to: 4.2
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to anything you want.

== Description ==

*WPS Hide Login* is a very light plugin that lets you easily and safely change the url of the login form to anything you want. It doesn’t literally rename or change files in core, nor does it add rewrite rules. It simply intercepts page requests and works on any WordPress website. The wp-admin directory and wp-login.php page become inaccessible, so you should bookmark or remember the url. Deactivating this plugin brings your site back exactly to the state it was before.

= Compatibility =

Requires WordPress 4.1 or higher. All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.

It’s also compatible with any plugin that hooks in the login form, including

* BuddyPress,
* bbPress,
* Limit Login Attempts,
* and User Switching.

Obviously it doesn’t work with plugins that *hardcoded* wp-login.php.

Works with multisite, but not tested with subdomains. Activating it for a network allows you to set a networkwide default. Individual sites can still rename their login page to something else.

If you’re using a **page caching plugin** you should add the slug of the new login url to the list of pages not to cache. For W3 Total Cache and WP Super Cache this plugin will give you a message with a link to the field you should update.

= GitHub =

https://github.com/tabrisrp/wps-hide-login

= Description Française =
WPS Hide Login est un plugin très léger qui vous permet facilement et en toute sécurité de modifier l'URL de connexion en ce que vous voulez.

Il ne renomme pas ou ne modifie pas de fichiers dans le noyau, et n'ajoute pas de règles de réécriture.  Il intercepte tout simplement les demandes de page et fonctionne sur n'importe quel site WordPress.

La page wp-login.php et le répertoire wp-admin deviennent donc inaccessibles, vous devrez donc bookmarker ou vous rappeler l'url. Désactiver ce plugin ramène tout simplement votre site à son état initial.

Compatibilité
Nécessite WordPress 4.1 ou supérieur.

Si vous utilisez un plugin de cache, vous devrez ajouter la nouvelle URL de connexion à la liste des pages à ne pas mettre en cache.

== Installation ==

1. Go to Plugins › Add New.
2. Search for *WPS Hide Login*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Change your login url there.
5. You can change this option any time you want, just go back to Settings › General › WPS Hide Login.

== Screenshots ==
1. Setting on single site installation
2. Setting for network wide

== Frequently Asked Questions ==

= I forgot my login url!  =

Either go to your MySQL database and look for the value of `whl_page` in the options table, or remove the `wps-hide-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

On a multisite install the `whl_page` option will be in the sitemeta table, if there is no such option in the options table.

== Changelog ==

= 1.1.2 =
* Modified priority on hooks to fix a problem with some configurations

= 1.1.1 =
* Check for Rename wp-login.php activation before activating WPS Hide Login to prevent conflict

= 1.1 =
* Fix : CSRF security issue when saving option value in single site and multisite mode. Thanks to @Secupress
* Improvement : changed option location from permalinks to general, because register_setting doesn't work on permalinks page.
* Improvement : notice after saving is now dismissible (compatibility with WP 4.2)
* Uninstall function is now in it's separate file uninstall.php
* Some cleaning and reordering of code

= 1.0 =

* Initial version. This is a fork of the Rename wp-login.php plugin, which is unmaintained https://wordpress.org/plugins/rename-wp-login/. All previous changelogs can be found there.
