=== Plugin Name ===
Contributors: esserq
Tags: bjj,jiujitsu,jiu,jitsu,brazilian,openmat,training,calendar,schedule,karate,taekwondoe,wrestling,yoga,krav,maga,martial arts,bjj
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Openmat plugin allows you to display your weekly training calendar anywhere in your blog.

== Description ==

This plugin sources your training schedule from https://openmat.training and displays the current week in a widget.

== Installation ==

1. Upload the unzipped `openmat` folder to your `wp-content/plugins` directory.

1. Activate the plugin through the `Plugins` menu in WordPress

== Configuration ==

1. Go to the [Openmat Developer page](https://openmat.training/settings/developer/ "Openmat developers") and generate an `API key` and `shared secret`.

1. In Wordpress, go to `Settings` > `Openmat API` and paste in your key information and save.

1. Go to `Appearance` > `Widgets` and drag the `Openmat Calendar` widget into a sidebar, enter an optional title and save.

== Appearance ==

Styling of the widget is controlled through a single stylesheet (openmat.css), contained under the wp-content/plugins/openmat/css/ directory.

== Changelog ==

= 1.0 =
* First version released

= 1.1 =
* Fixed date compare bug