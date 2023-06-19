=== Alda Currency ===
Contributors:      Alda Vigdís
Tags:              block, currency, money
Tested up to:      6.2
Requires at least: 6.2
Stable tag:        0.1.0
License:           GPL-3.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

Automatically convert between currencies from an interactive block

== Description ==

This plugin provides a simple interactive currency conversion block that can be
inserted into your posts, pages and your WordPress FSE layout.

Choose your base currency under Settings and enter the values relative to that
currency under Currency Rates after clicking "Alda Currency" in the admin
sidebar.

Do note that currency rates are not updated automatically and need to be
manually entered under "Alda Currency" in your WordPress Admin sidebar.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/alda-currency` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= The block does not render on my site — what gives? =

This block depends on being able to run the React JavaScript framework on top of
your WordPress site. This framework, which the block editor is based on, is only
provided to your website frontend with version 6.2 or newer of WordPress.

The best weay to fix this is to update your WordPress installation and the
second best is to manually include React and React DOM in your site header.

== Screenshots ==

1. The block as seen in the WordPress block editor
2. The block on a site using the Twenty Twenty-Three theme
3. The admin view, used for setting and updating the currency values calculated by the block

== Changelog ==

= 0.1.0 =
* Initial release
