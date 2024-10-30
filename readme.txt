=== Location Report ===
Contributors: christian_feldbauer
Tags: geo, current location, latest position, travel route, kml, map, shortcode
Requires at least: 3.0.1
Tested up to: 5.4.2
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides a shortcode to update your location and generates kml files for your latest location and your travel route.


== Description ==

Ideal for travel blogs, this plugin provides the [locationreport ...] shortcode that lets you update your current location. It generates two kml files. The first has a placemark of your current location and the second one shows the route along all your location reports. Those kml files can be displayed by one of the many available map plugins (e.g., OpenStreetMap, Leaflet Map, or Flexible Map). Note, this plugin itself does NOT provide a map. However, it provides a simple, universal (no GUI interaction necessary, so you can report your position even when posting by email), portable (no db, just kml files) and modular way of position reporting and together with a map plugin you can show your latest position and/or your travel route in a map widget, post or page.


== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the [locationreport ...] shortcode in your posts whenever you want to update your location.
4. Use a map plugin to display the generated kml files with your latest position and/or your (travel) route.


== Frequently Asked Questions ==


= How to use the [locationreport] shortcode? =

Here are some examples to report a latitude of 25° 51' N and a longitude of 135° 7.3' W as your current location: 

  [locationreport lat="25.85" lon="-135.1217"] ... Here the coordinates are given in decimal degrees. Positive values for N and E, negative for S and W. Note, as this format does not have any blanks, the quotes can be omitted:
   
  [locationreport lat=25.85 lon=-135.1217] ... This is fine as well.
  
  [locationreport lat="25° 51' N" lon="135° 7.3' W"] ... If you really love typing (and searching for the degree character)... Coordinates are given as a triplet of degrees (positive integer), minutes (decimal) and cardinal direction.
  
  [locationreport lat=25°51'N lon=135°7.3'W] ... Without blanks the quotes are not needed. 
  
  [locationreport lat="25 51 N" lon="135 7.3 W"] ... The degree and minute characters are not needed. 
  
  [locationreport lat="25 51N" lon="135 7.3W"] ... Is okay as well.
  
  [locationreport lat="25N 51" lon="135W 7.3"] ... And so is this.
  
  [locationreport lat="N25 51" lon="W135 7.3"] ... And this.
  
  [locationreport lat=25N51 lon=135W7.3] ... And also this.
  
Choose your favorite coordinate format and put this shortcode with your actual location in a blog entry. That's it. Giving the position as degrees, minutes and seconds is not supported yet.


= What does this shortcode do? =

When the blog entry is saved (published or private), a placemark is added/modified in the kml files for your route and your current location. Date and time is taken from the post's publication time. The post title and the text content are shown in the popup window of the created placemark.
If the 'show' attribute is given, e.g. [locationreport show=true lat=25N51 lon=135W7.3], the shortcode is replaced by a short paragraph: 'Position reported on DATE at TIME UTC: LAT, LON.' Otherwise, no text is shown in the post content.


= Where can I find these kml files? =

The kml files are in your blog's upload directory in the subfolder 'locationreport', normally at '/wp-content/uploads/locationreport/'. The files are named 'latest.kml' and 'route.kml'.


= How do I display those kml files in a map on my blog? =

There are many map plugins available for WordPress. You need one that is capable of displaying kml files. I successfully tested displaying the locationreport kml files using the 'OSM' plugin, the 'Flexible Map' plugin, 'Leaflet Map', and 'Geo Mashup'. Leaflet Map does not (yet) support marker icons from kml styles out-of-the-box, however installation of the 'Leaflet FullKML' plugin adds this support. All those tested plugins provide shortcodes to put a map on a post, a page or a text widget. Refer to the documentation of your favorite map plugin for more information on how to display kml files.


= I updated my position using [locationreport ...], but the map shows still the old position. What's wrong? =

Some servers as well as some web browsers cache kml files. The problem should be fixed now.  


= I made a mistake when I posted the [locationreport ...]. Can I correct the coordinates in the kml files? =

Yes, that is possible. Since version 1.0.3 you can easily do that by editing the post that contains the problematic [locationreport ...] shortcode. Log in to your blog and search for the post and click 'Edit.' Modify the [locationreport ...] shortcode with the corrected coordinates, and the corresponding placemark in the kml file(s) will be updated. You can also modify the post title and the remaining post content (which will be shown in the placemark's popup window). Even the post's publication date can be modified (this can be used to change the route along the placemarks in a modified order).


= Can I insert a new placemark anywhere in my route? =

Yes. Create a new post with a [locationreport ...] shortcode and change the publication date/time to a past date/time that is in-between the dates/times of those two placemarks where you want to insert the new one.


= Can I delete a placemark from my route? =

Yes. Edit the post that contains the corresponding '[locationreport lat=.. lon=..]' shortcode and remove both the 'lat' and 'lon' attributes, i.e., it can be empty like '[locationreport]'. When updating the post, the shortcode in the post and the placemark in the kml file(s) is removed. If the placemark belongs to an archived route, it can be deleted from that archive in the same way by removing the 'lat' and 'lon' attributes, however the 'file' attribute MUST NOT be removed, e.g., '[locationreport file="archive.kml"]'.


= How do I start a new route for my new trip around the world? =

On your WordPress admin page you can find a menu entry 'LocationReport' under 'Settings' that opens the LocationReport Manager. Use it to archive your old route and to start a new one.


= I'd like to have a different icon for my latest location. How can I change the  placemark icon? =

Kml files can include styles to define placemark icons (as well as line styles), and both locationreport kml files are prepared with empty style definitions for easy customization. To change how the placemark for your latest location is displayed, you can add your IconStyle definition in the file 'style.kml' inside the Style element called 'locationreportLatestStyle' by manually editing the kml file: click on 'Editor' under the 'Plugin' section of your dashboard. Select 'Location Report' as the plugin to be edited. Then select the file 'style.kml.txt' under symlinks (the .txt extensions are just a workaround to get the kml files included in the file list). Save the file by clicking on 'Update File'.

This style definition will effect markers in both kml files, latest.kml and route.kml (most recent marker only). You also can change the placemark icon for the older location reports as well as the line style for your route. Simply add your IconStyle and LineStyle definitions in the file 'style.kml.txt' inside the Style element called 'locationreportRouteStyle'.

Don't change the styles inside latest.kml or route.kml (or their symbolic links latest.kml.txt or route.kml.txt) directly as these files will be overwritten when you use the [locationreport] shortcode the next time.

Here are some example style definitions:

    <Style id="locationreportLatestStyle"> 
      <IconStyle>
        <Icon>
          <href>http://yourblog.com/icon.jpg</href>
        </Icon>
      </IconStyle> 
    </Style>
    
    <Style id="locationreportRouteStyle">
      <IconStyle>
        <color>ff0088ff</color>
      </IconStyle>
      <LineStyle>
        <color>ff0000ff</color>
        <width>6</width>
      </LineStyle>
    </Style>

For a complete documentation on how to define styles refer to the [KML reference](https://developers.google.com/kml/documentation/kmlreference).

Since version 1.0.3 this plugin comes with a template customized for a sailboat, which can be (usually) found at /wp-content/plugins/locationreport/templates/lilly. If you like to use this for your blog, copy all files within lilly/ to your blog's upload folder, usually under /wp-content/uploads/locationreport/.


== Changelog ==

### 1.1.3, 2020-08-05

* Fix for special characters in post title, proper encoding of xml entities

### 1.1.2, 2020-07-30

* 'show' attribute is needed now to show the position-report text in the blog content.

### 1.1.1, 2020-07-28

* more work on caching workaround: forced file-size change

### 1.1, 2020-02-21

* fixed bugs
* use post ID as placemark id
* routes can be archived through Settings->LocationReport Manager
* enabled inserting, reordering, and removing of placemarks
* shortcode is no longer removed from content

### 1.0.4, 2020-02-15

* html links to kml files to prevent caching in some browsers

### 1.0.3, 2020-01-01

* added possibility to correct entries

### 1.0.2, 2017-04-11

* documentation cleanup

### 1.0.1, 2017-04-10

* added: documentation in readme.txt

