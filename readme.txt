=== Content Progress ===
Contributors: joedolson
Donate link: https://www.joedolson.com/donate/
Tags: page, post, admin, developer, tools, progress, content, editorial, review, overview, workflow, editing
Requires at least: 4.1
Tested up to: 4.7
Text Domain: content-progress
Stable tag: trunk

Manage your work flow: mark WordPress posts and Pages as complete or incomplete, or as needing review. Add notes for editorial needs.

== Description ==

Content Progress helps you manage your content editing work flow. It adds icons to your content listings (Posts, Pages, and custom post types) to indicate the current needs for that post. 

The plug-in auto-detects empty posts or documents with very small amounts of content; but also allows you to flag documents specifically.

You can flag any document as incomplete or needing review, or create custom content flags suitable to your needs, such as "Needs Research", "Add Media" or "Needs Scheduling".

To help communication between groups (or remembering where you are for large sites!), you can also add notes to each post.

This simple work flow plug-in provides a quick way to scan over which documents in your web site are finished, since it is frequently the case in my own development that I add all new pages (for the purpose of building menus and navigation) before actually filling those pages with content.

There are also four shortcodes available for generating front-facing lists of pages: [empty], [partial], [incomplete], and [needs_review] which will produce appropriate unordered lists of documents. Each shortcode accepts an argument for the post type: e.g. [empty type='post']

With the addition of custom statuses available in version 1.3.0, a fifth shortcode [list status=''] is available to produce a list of items with a custom status.

Translating my plug-ins is always appreciated. Visit <a href="http://translate.joedolson.com">my translations site</a> to start getting your language into shape!

Languages available (in order of completeness):
German, Dutch, Spanish, French, Polish, Irish

<a href="http://www.joedolson.com/translator-credits/">Translator Credits</a>

== Changelog ==

= Future = 

* Feature: add filters to All Posts views
* Feature: ability to show notes & status only to specific roles or capabilities
* Feature: send notifications to authors after comments or changes

= 1.3.13 =

* New meta box output filter: 'cp_meta_box'
* New action: 'cp_save_post'
* Misc. code reformatting
* Update styles for headings
* Make settings layout responsive
* More aggressive donation request.
* Test for 4.7

= 1.3.12 =

* Update to headings hierarchy
* Remove deprecated get_currentuserinfo()
* Test for 4.6

= 1.3.11 =

* Bug fix: Typo in URL.
* Bug fix: text domain path info
* Update URLs

= 1.3.10 =

* Style change: Wider column for i18n.
* Bug fix: Selected status did not always display in quick edit.
* Feature: Set default status for new posts.

= 1.3.9 =

* Bug fix: lang textdomain declaration used invalid nesting
* Bug fix: Posts automatically marked as Empty not automatically cleared when updated.
* Bug fix: post lists generated with headings labeled 'Pages' rather than actual post types.

= 1.3.8 =

* Bug fix: Default values did not display after initial refresh. 
* Add language: Polish.

= 1.3.7 =

* Bug fix: post type variable incorrectly called/documented in shortcodes
* Change: posts automatically ID'd as empty or partial have post meta auto updated when displayed in a list, so that they will show in shortcode lists.
* Cleared one PHP notice.
* Added filter to customize the post length auto-flagged as Incomplete: <code>cp_incomplete_length</code>

= 1.3.6 =

* Added language: German

= 1.3.5 =

* Added language: Dutch

= 1.3.4 =

* Added language: Spanish

= 1.3.3 =

* Added language: French

= 1.3.2 =

* Bug fix: Added fallback function for exif_imagetype when exif not installed. (Related to: custom status icons)

= 1.3.1 =

* Fixed broken labels when updating flags in Quick Edit.
* Fixed PHP notice errors from undefined variables in Quick Edit.

= 1.3.0 =

* Added ability to create custom status labels.
* Added icon library
* Bug fix: posts without set values did not display with default settings.
* Revised description.
* Added Irish translation.

= 1.2.3 =

* Missed one occurrence of the settings array bug.
* Revised plug-in description to improve clarity.
* Revised quick edit script so that Notes are exposed in quickedit on all post types.

= 1.2.2 =

* Oh, wow. I forgot to add the scripts directory to the subversion repository.

= 1.2.1 = 

* Settings bug

= 1.2.0 =

* Added 'notes' column to posts tables.
* Added selection options for progress label and notes fields to Quick Edit mode.
* Added option to limit use of plug-in to specific content types.
* Bug fix: default settings were not rendered in posts tables.

= 1.1.1 =

* Added new option: mark "Needs Review"
* Replaced all graphics with more intuitive icons.
* Fixed theme data bug 

= 1.1.0 =

* Added option to mark a document as complete, as well as mark as incomplete.
* Changed flags: Blue flag now means complete, Yellow means short content. 
* Updated some textdomain labels (had wrong plug-in textdomain on a few strings.

= 1.0.0 =

* Initial release

== Installation ==

1. Upload the `content-progress` folder to your `/wp-content/plugins/` directory
2. Activate the plugin using the `Plugins` menu in WordPress
3. Visit any listing of content: Posts, Pages, etc. to see notifications.

== Frequently Asked Questions ==

= This is a weird idea. Why'd you write it? =

While working on a web site with several hundred pages, it got difficult to keep track of which pages had their content loaded in. This was a quick way to see what needed to be done.

== Screenshots ==

1. Posts page indicator

== Upgrade Notice ==

* 1.3.6: German translation