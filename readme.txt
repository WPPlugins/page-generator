=== Page Generator ===
Contributors: n7studios,wpzinc
Donate link: https://www.wpzinc.com/plugins/page-generator-pro
Tags: page,generator,content,bulk,pages,seo,spintax,automated,automation,500px,wikipedia,youtube,yelp
Requires at least: 3.6
Tested up to: 4.7.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate multiple Pages using dynamic content.

== Description ==

Page Generator allows you to generate multiple content Pages, each with their own variation of a base content template.  

Variations can be produced by using keywords, which contain multiple words or phrases that are then cycled through for each Page that is generated.

Generate multiple Pages in bulk by defining:

* Page Title
* Page Slug / Permalink
* Content
* Publish status (Draft or Publish)
* Number of Pages to generate
* Author

> #### Page Generator Pro
> <a href="https://www.wpzinc.com/plugins/page-generator-pro/" rel="friend" title="Page Generator Pro">Page Generator Pro</a> provides additional functionality:<br />
>
> - Generate Unlimited, Unique Posts, Pages and Custom Post Types<br />
> - Automatically Generate Nearby Cities Keywords<br />
> - Full Spintax Support<br />
> - Full Page Builder Support: Avada, Beaver Builder, BeTheme, Divi, Fusion Builder, Muffin Page Builder, SiteOrigin Page Builder and Visual Composer<br />
> - Custom Fields<br />
> - Generate SEO Metadata: Define custom field key/value pairs found in our Documentation, to ensure you generated Pages are SEO ready.  Supports AIOSEO and Yoast.<br />
> - Advanced Scheduling Functionality: Generate Pages to be published in the future. Each generated page can be scheduled relative to the previous page, to drip feed content.<br />
> - Powerful Generation Methods: Pro provides All, Sequential and Random generation methods when cycling through Keywords, as well as a Resume Index to generate Pages in smaller batches.<br />
> - Page Attribute Support<br />
> - Full Taxonomy Support: Choose taxonomy terms, or generate new ones<br />
> - Embed Rich Content: 500px, Google Maps, Yelp! Business Listings, Wikipedia and YouTube content<br />
> - WP-CLI Support: Generate Pages faster using WP-CLI, if installed on your web host.<br />
>
> [Upgrade to Page Generator Pro](https://www.wpzinc.com/plugins/page-generator-pro/)

[youtube http://www.youtube.com/watch?v=KTBDy3-6Z1E]

= Support =

We will do our best to provide support through the WordPress forums. However, please understand that this is a free plugin, 
so support will be limited. Please read this article on <a href="http://www.wpbeginner.com/beginners-guide/how-to-properly-ask-for-wordpress-support-and-get-it/">how to properly ask for WordPress support and get it</a>.

If you require one to one email support, please consider <a href="https://www.wpzinc.com/plugins/page-generator-pro" rel="friend">upgrading to the Pro version</a>.

= WP Zinc =
We produce free and premium WordPress Plugins that supercharge your site, by increasing user engagement, boost site visitor numbers
and keep your WordPress web sites secure.

Find out more about us at <a href="https://www.wpzinc.com" rel="friend" title="Premium WordPress Plugins">wpzinc.com</a>

== Installation ==

1. Upload the `page-generator` folder to the `/wp-content/plugins/` directory
2. Active the Page Generator plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `Page Generator` menu that appears in your admin menu

== Frequently Asked Questions ==



== Screenshots ==

1. Keywords table
2. Editing a keyword
3. Generating Pages screen

== Changelog ==

= 1.4.2 =
* Fix: Generate: Blank screen for some users

= 1.4.1 =
* Fix: Undefined variable errors

= 1.4.0 =
* Fix: Only display Review Helper for Super Admin and Admin

= 1.3.9 =
* Added: Review Helper to check if the user needs help
* Updated: Dashboard Submodule

= 1.3.8 =
* Added: Version bump to match Pro version, using same core codebase and UI for basic features. Fixes several oustanding bugs
* Added: Post Type: Use variable for Post Type Name for better abstraction
* Fix: Generate: Don't attempt to test for permitted meta boxes if none exist
* Fix: Generate: Check Custom Fields are set before running checks on them
* Fix: Use Plugin Name variable for better abstraction
* Fix: Improved Installation and Upgrade routines

= 1.0.6 =
* Fix: Changed branding from WP Cube to WP Zinc

= 1.0.5 =
* Fix: Display keywords in keyword table

= 1.0.4 =
* Tested with WordPress 4.3
* Fix: plugin_dir_path() and plugin_dir_url() used for Multisite / symlink support

= 1.0.3 =
* Fix: Dashboard errors
* Fix: Changed Menu Icon
* Fix: WordPress 4.0 compatibility

= 1.0.2 =
* Added: Support for HTML elements in keyword data

= 1.0.1 =
* Added translation support and .pot file

= 1.0 =
* First release.

== Upgrade Notice ==
