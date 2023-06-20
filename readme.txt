=== Alda Currency ===
Contributors:      aldavigdis
Author URI:        https://aldavigdis.is/
Tags:              block, currency, money, tourism, travel
Tested up to:      6.2.2
Requires at least: 6.2
Requires PHP:      7.4
Stable tag:        0.1.0
License:           GPL-3.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

Automatically convert between currencies from an interactive block

== Description ==

This plugin provides a simple interactive currency conversion block that can be inserted into your posts, pages and your WordPress FSE layout.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/alda-currency` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

Currency values are entered automatically by the site administrator using the Alda Currency admin panel. Your base currency will equal 1 and then the other currencies should have values.

Choose your base currency under Settings and enter the values relative to that currency under Currency Rates.

If you use USD as your base currency, then the EUR value should be 0.91 for instance.

== Frequently Asked Questions ==

= Who is this plugin for? =

It is first and foremost intended for companies in the travel and tourism sector, as well as e-commerce stores for posting a non-binding reference value for a series of currencies and to make it easy for customers to convert between the local currency and others. (If you are a bank or a financial institution, then you are goin to need something more complex.)

= The block does not render on my site — what gives? =

This block depends on being able to run the React JavaScript framework on top of your WordPress site. This framework, which the block editor is based on, is only provided to your website frontend with version 6.2 or newer of WordPress.

The best weay to fix this is to update your WordPress installation and the second best is to manually include React and React DOM in your site header.

= What if my currency is missing or I'd like to add one? =

Feel free to contact the author and she will be happy to add more currencies to the plugin. Otherwise, you are welcome to contribute to its code on Github.

= Should I include a disclaimer? =

Yes, probably. Consumer currency rates change daily and depend on each bank, card company, national currency restrictions etc. Financial institutions post at least two values for each currency while this plugin only accounts for a single value for each currency and does not use "mills" for posting fractional currency rates.

== Screenshots ==

1. The block as seen in the WordPress block editor
2. The block on a site using the Twenty Twenty-Three theme
3. The admin view, used for setting and updating the currency values calculated by the block

== Changelog ==

= 0.1.0 =
* Initial release
