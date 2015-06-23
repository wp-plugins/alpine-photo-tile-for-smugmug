=== Alpine PhotoTile for SmugMug ===
Contributors: theAlpinePress
Donate link: thealpinepress.com
Tags: photos, smugmug, smug mug, photostream, pictures, images, widget, sidebar, gallery, lightbox, fancybox, prettybox, colorbox
Requires at least: 2.8
Tested up to: 3.5
Stable tag: 1.2.3.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Stylish and compact plugin for displaying SmugMug images in a sidebar, post, or page. 

== Description == 
The Alpine PhotoTile for SmugMug is capable of retrieving photos from a particular SmugMug user or gallery. The photos can be linked to the your SmugMug page, a specific URL, or to a Lightbox slideshow. Also, the Shortcode Generator makes it easy to insert the widget into posts without learning any of the code. This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek presentation that I hope you will like. A full description and demonstration is available at [the Alpine Press](http://thealpinepress.com/alpine-phototile-for-smugmug/ "Plugin Demo").

**Features:**

* Display SmugMug images in a sidebar, post, or page
* Multiple styles to allow for customization
* Lighbox feature for interactive slideshow (Fancybox, prettyBox, or ColorBox)
* Simple instructions
* Widget & shortcode options
* Feed caching/storage for improved page loading

**Quick Start Guide:**

1. After installing the plugin on your WordPress site, make sure it is activated by logging into your admin area and going to Plugins in the left menu.
2. To add the plugin to a sidebar, go to Appearance->Widgets in the left menu.
3. Find the rectangle labeled Alpine PhotoTile for SmugMug. Click and drag the rectangle to one of the sidebar containers on the right.
4. Once you drop the rectangle in a sidebar area, it should open to reveal a menu of options. The only required information for the plugin to work is SmugMug Username. Enter this username and click save in the right bottom corner of the menu.
5. Open another page/window in your web browser and navigate to your WordPress site to see how the sidebar looks with the Alpine PhotoTile for SmugMug included.
6. Play around with the various styles and options to find what works best for your site.

== Installation ==

1. Upload `alpine-photo-tile-for-smugmug` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the widget like any other widget.
4. Customize based on your preference.

== Frequently Asked Questions ==

**How do I find the Album ID and Album Key?**
The Album ID and Album Key are contained in the url of the gallery. For example, there is a gallery with the url “cmac.smugmug.com/gallery/2504559_f3ta9?. The last portion of the url contains the information we want:

*2504559 is the albumID*

*f3ta9 is the albumKey* (the key is case sensitive and you will encounter upper case characters in it)

**I’m getting the message “SmugMug feed was successfully retrieved, but no photos found”. What does that mean?**

This message simply means that while no distinguishable errors occurred, the plugin found your feed to be empty.

**Can I insert the plugin in posts or pages? Is there a shortcode function?**

Yes, you can display photos in posts or pages using what is called a shortcode. Rather than explaining how to setup the shortcode, I have created a method of generating the shortcode. Check out the Shortcode Generator on the plugin’s settings page (Settings->AlpineTile: SmugMug->Shortcode Generator).

**Why doesn’t the widget show my most recent photos?**

By default, the plugin caches or stores the SmugMug feed for three hours (see Caching above). If the new photos have still not appeared after this time, it is possible that SmugMug is responsible for the delay.

**How many photos can I display?**

The plugin can retrieve and display up to 200 photos.

If you have any more questions, please leave a message at [the Alpine Press](http://thealpinepress.com/alpine-phototile-for-smugmug/ "Plugin Demo").
I am a one-man development team and I distribute these plugins for free, so please be patient with me.

== Changelog ==

= 1.2.0 =
* Rebuilt Alpine Photo series to work with SmugMug
* Rebuilt plugin structure into OBJECT
* Combined all Alpine Photo Tiles scripts and styles into identical files
* Improved IE 7 compatibility
* Added custom image link options
* Added Fancybox jQuery option
* Fixed galleryHeight bug
* Implemented fetch with wp_remote_get()

= 1.2.1 =
* Rebuilt admin div structure
* Fixed admin css issues

= 1.2.2 =
* Added aspect ratio options for gallery style
* Added key generator function
* Added get_image_url() functions
* Object oriented id, options, results, and output storage
* Object oriented display generation

= 1.2.3 =
* Added FancyboxForAlpine (Fancybox Safemode)
* Added choice between Fancybox, prettyBox, and ColorBox
* Added hidden options, including custom rel for lightbox

= 1.2.3.2 =
* Added option to disable right-clicking on images

= 1.2.3.3 =
* Pretty Photo Update