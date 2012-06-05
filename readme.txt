=== Plugin Name ===
Contributors: jkrill
Donate link: http://www.krillwebdesign.com
Plugin URI: http://krillwebdesign.com/2012/03/wp-jump-menu/
Author URI: http://krillwebdesign.com
Tags: posts, pages, admin, jump, menu, quick, links, custom post types
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 2.4

Creates a drop-down menu in the admin area which makes it easy to jump to a page, post, custom post type or media file for editing. 

== Description ==

Creates a drop-down menu in the admin area which makes it easy to jump to a page, post, custom post type or media file for editing. One click and you're editing!  New options page for easy customization.

THIS PLUGIN IS A MUST HAVE FOR WORDPRESS DEVELOPERS!

UPDATE: Now you can place the Jump Menu in the WordPress Admin Bar! 

Sick of having to go to your Posts list or Pages list to edit an entry?  Me too.

The WP Jump Menu plugin is a useful plugin that adds a drop-down "jump" menu to the bottom or top of the WordPress admin area which allows you to select from a list of pages, posts or custom post types and jump right into editing that page.

Why is this needed?

By adding this ability, you can save TONS OF TIME when editing a bunch of posts or pages.  No more do you have to click on Pages, and then search for the right page in the list, and then click on the title or on edit.   Simply find the page or post in the drop-down (which is neatly organized and indented) and it will jump to edit that page or post.

Customizable

The plugin comes with an options page where you can edit the position (top or bottom) of the jump bar, the background color, font color, link color, border color and the icon and message that optionally get displayed on the left hand side.

Great for theme developers to help clients navigate in the admin area of WordPress.  We use this plugin on all of our projects and decided it was time to release it to the world!

Enjoy!

== Installation ==


1. Upload the `wp-jump-menu` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll find the options under Settings->WP Jump Menu
4. In the settings, you can customize the jump menu's location, colors, sort order, and enter in custom post types (comma seperated) to list in the jump menu.
5. Make sure you click save to save your settings.

== Frequently Asked Questions ==

= The jump menu isn't working. =

If the jump menu isn't working, please submit a forum post and I will respond asap. <a href="http://wordpress.org/tags/wp-jump-menu?forum_id=10#postform">Click here to start a forum thread</a>.

Make sure you have the latest version of WordPress and the latest version of WP-Jump-Menu.  

= How do I put my logo in? =

You need to upload your logo icon, through WordPress maybe, and get the full URL to the image.  This you will put into the Logo Icon field in the options.

== Screenshots ==

1. The Jump Menu in the WordPress Admin Bar

2. The Options Page - Here you can edit the drop-down list of custom post types (as well as built in post types) and their order, change the colors of the WP Jump Menu, and specify a few other options!

3. The Jump Menu - Just a drop down, but one POWERFUL drop down!

== Changelog ==

= 2.4 =
* Added new option to show "Add New" link under each post type in the jump menu.
* Fixed a bug that was preventing heirarchical pagers from showing correctly in the menu.

= 2.3.4 =
* Fixed a bug that was causing a javascript error in WP versions 3.2.1 and lower.

= 2.3.3 =
* Added the ability to choose what color each post status will appear as in the jump menu.
* Added the date to show on future/scheduled posts/pages.

= 2.3.2 =
* Fixed another small bug.

= 2.3.1 =
* Fixed a bug pertaining to arrays.

= 2.3 =
* Added post status options for each post type.  Now you can choose which posts/pages to display per post type based on their post status (i.e. Published, Drafts, Pending, Private, etc.).  See the new options in the WP Jump Menu Options page.

= 2.2.9 =
* Fixed a bug when showing drafts.

= 2.2.8 =
* Added the ability to show Drafts.

= 2.2.7 =
* Updated the code for insertion into the WP Admin Bar. WP compliant now.

= 2.2.6 =
* BETA testing Jump Menu in WP Admin Bar.  Please report any bugs.

= 2.2.5 =
* Tested on WP 3.3
* Fixed positioning bar when positioned on bottom (if WP admin bar was showing)

= 2.2.4 =
* Replaced the listing of heirarchical pages/post types with WP native Walker functionality. Should improve listing of parent / child pages in drop-down.
* Cleaned up the code, added comments
* Used get_edit_post_link() instead of hard coding link to edit post page (yay!)
* Added the option to show or hide the ID next to the post/page name in the drop-down

= 2.2.3 =
* Bug fixed: Order By was not working because the orderby value names had not been updated to exclude "post-".  Thanks Tim for the bug report.

= 2.2.2 =
* Minor fix to jqueryfunctions.js 

= 2.2.1 = 
* Forgot to add default value for "number of posts" to show when updating or installing for the first time.  Post types had to be saved the first time to get the list to show up correctly.

= 2.2 =
* Completed/fixed the sorting of the post types on the options page. Now you can sort the post types in the order you want them to appear in the Jump Menu, and when you save that order, the table-list will stay in the order you saved it in.
* Started using wp_cache to store the list of posts, pages, etc. that Jump Menu displays in it's drop-down, so it is not querying the database on every page load.  Hopefully I did this right and it works, if anyone wants to jump in the code and check for me... great!

= 2.1.4 =
* Fixed error (variable not array)

= 2.1.3 =
* Added the ability to sort the Post Types by clicking and dragging on their rows and re-ordering them.  This will effect the order they display in the Jump Menu.
* Updated text on options page.

= 2.1.2 =
* Small bug fixed: default post and page post type were not set right.

= 2.1.1 =
* Fixed Update issue: When updated, WP Jump Menu would stop working because it uses new option values.  New default values should be set when updated.

= 2.1 =
* Fixed bug: Can be displayed at the top of the screen WITH the WP Admin Bar, whereas before it was being hidden when both were activated and on the top.
* Totally redid the Post Types and the way you select them.  You can now select (or deselect) any post type.  Not just custom post types.  So you can totally turn off pages, or posts - or now you can add attachments!
* Redid the options page using wp_settings.  Much better!
* Added the title "WP Jump Menu" next to the drop-down

= 2.0 =
* Totally redid the form layout to match native WP admin styles
* Updated the color styling of the jump menu bar so you can live preview the changes as you change the colors to see what it will look like
* Got rid of Logo Width (automatically determines the width of the logo based on the logo URL)
* Changed the custom post type selector from manual (you had to type in a comma separated list) to showing checkboxes for each custom post type that exists, allowing you to simply select each one you want to show in the drop-down.

= 1.4 = 
* Cleaned up a little bit, and updated the readme.txt

= 1.3 =
* Changed how the optional logo is displayed (from background css to <img> tag)

= 1.2 =
* Added color picker to hex inputs
* Added sort order options for pages and posts
* Added number of posts to display option

= 1.1 =
* Fixed minor js bug
* Included a screenshot

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.3 =
* Updated logo option

= 1.2 =
* Updates to options in admin area

= 1.1 =
Stable working version.

= 1.0 = 
* Initial Release
