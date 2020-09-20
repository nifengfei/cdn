=== BBCode ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: bbcode
Requires at least: 2.5
Stable tag: trunk

Implements BBCode in posts.

== Description ==

Implements [BBCode](http://en.wikipedia.org/wiki/BBCode) in posts.

Examples:

`Bold: [b]bold[/b]

Italics: [i]italics[/i]

Underline: [u]underline[/u]

URL: [url]http://wordpress.org/[/url] [url="http://wordpress.org/"]WordPress[/url]

Image: [img]http://s.wordpress.org/style/images/codeispoetry.png[/img]

Quote:

[quote]Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.[/quote]

Bold Italics: [b][i]Test text[/i][/b]`

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - bbcode
            | readme.txt
            | bbcode.php`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

== Frequently Asked Questions ==

= I love your plugin! Can I donate to you? =

Sure! I do this in my free time and I appreciate all donations that I get. It makes me want to continue to update this plugin. You can find more details on [my donate page](http://www.viper007bond.com/donate/).

== ChangeLog ==

**Version 1.0.1**

* Don't double-encode URLs. Props [DaMsT](http://wordpress.org/support/topic/182354).

**Version 1.0.0**

* Initial release.