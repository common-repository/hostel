=== Hostel ===
Contributors: prasunsen
Tags: hostel, hotel, booking, bnb, reservations
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.1.5
License: GPL2

Create your hostel, small hotel or BnB site with WordPress. Manage rooms, booking, unavailable dates, and more. 

== Description ==

Create your hostel, small hotel, or BnB site with WordPress.
Hostel is online booking system with easy back-end management. 
You can publish the booking forms, room calendars, and room lists with shortcodes so the plugin fits any WordPress theme. 

###Features###

- Manage your booking mode: accept Paypal, manual payments, or don't allow online booking
- Manage email notifications
- Manage rooms, beds, and prices
- Set unavailable dates when you are on vacations or just don't want to accept guests in some rooms
- Manage bookings, process payments, contact customers
- List your rooms by using shortcodes
- Supports iCal / .ics to synchronize bookings with online systems. You can export and import bookings to/from all popular booking sites like Booking.com, AirBnB.com, Hotels.com etc.
- Localization / translation - ready
- Mobile / touch - friendly

There are more and better features + premium support in the PRO version. Check it on our new site: [wp-hostel.com](http://wp-hostel.com "Hostel PRO") 

###Getting Started###

1. Go to Hostel link in your admin menu to manage your rooms and rates.
2. Use the shortcodes to install a list of your rooms or to add the booking code to a post or page where you have described your rooms.
3. Set up unavailable dates if you have such.

###Shortcodes###

- [wphostel-list] will display a table with your available rooms. A date selector on the top lets the user choose dates of their visit and then the rooms list is updated. If you have enabled booking in your Hostel settings page, the table will also show "Book" button when appropriate. The button will automaically load the booking form. You can pass the attribute "max_days" to specify the maximum day interval that can be selected to show the table.

- [wphostel-booking] displays a generic booking form with a drop-down selector for choosing room, and a date selector. If you use the [wphostel-list] shortcode you most probably do not need this one because the booking form is automatically generated.

For translating the plugin check the Help page under the Hostel menu in your administration.

###Community Translations###

The following translations are currently available. Please note they are maintained by volunteer translators and we can't guarantee their accuracy.

Spanish: [wphostel-es_ES.mo](http://backpackercompare.com/wp-content/uploads/2014/06/wphostel-es_ES.mo "wphostel-es_ES.mo") | [wphostel-es_ES.po](http://backpackercompare.com/wp-content/uploads/2014/06/wphostel-es_ES.po "wphostel-es_ES.po")

== Installation ==

1. Unzip the contents and upload the entire `hostel` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Hostel" in your menu and manage the plugin

== Frequently Asked Questions ==

= Can I import bookings from sites like Booking.com etc? =

Yes. Each room supports multiple iCal URLs. You can get the iCal URL from your control panel at the sites that provide it. Most popular booking sites do provide you with iCal URL

= I have problems with receiving emails =

Hostel just sends the emails to your WP installaiton so if emails are not delivered this is most likely a problem with your installation. We suggest installing a free plugin like [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) or [Postman SMTP](https://wordpress.org/plugins/postman-smtp/) to test and improve your emails delivery. Hostel will automatically send its emails through any SMTP plugin you have installed.

== Screenshots ==

1. The options page let you set up currency, booking mode, and email settings

2. You can add any number of private and dorm rooms, specify price, bathroom etc

3. Add/Edit booking. As admin you can review and edit bookings made from users and manually add bookings made by phone or email

4. If there are any dates when your property or some rooms are not available, add them here

5. The Help page shows the available shortcodes.

== Changelog ==

= Version 1.1.5 =
- Tables in the administration pages are now made responsive so you can work with them in all mobile devices
- Added class "wphostel-book-button" to the "Make Reservation" button to allow styling
- Replaced CURL with WP HTTP API
- Prevent XSS and removed unwanted slashes in Manage Bookings
- Added DB indexes for better performance


= Version 1.1 =
- Improved the Unavailable dates management. Now you can select a date range to make it unavailable at once. Any partially overlapping periods will also be shown there for each room so you can easily switch to them and cleanup the unavailability.
- Added iCal / .ics support for easier synchronization with other services
- Added possibility to use your own versions of the views / templates and modify the plugin pages without losing your changes on upgrade. See the plugin's internal Help page for more details.
- Two new arguments added to the [wphostel-list] shortcode: form_horizontal and show_table. Check the internal Help page for details.
- Added option to limit the period in the future available for booking
- Now you can choose whether guests can book from today or tomorrow 
- Changed the Paypal postback endpoints accordingly to latest Paypal security changes
- Security fixes - sanitization of vars, etc.
- You can now sync bookings with external services through iCal URLs - see the Edit Room page for details
- Added sorting on the Bookings page

= Version 1.0 =
- Booking ID added in the Bookings table to help finding a booking for cancellation, referrence etc
- Added email log for all notification emails sent out from the plugin. Emails are logged from version 0.9.3 ahead.
- Added "show_titles" argument to the [wphostel-list] shortcode. It allows you to show the room titles in the rooms listing table. 
- Fixed the datepicker CSS bug
- Added debug mode to see SQL errors in case you have any problems with the plugin
- Disallow selecting dates in the past when booking
- Added payment error log to see why Paypal payment was not marked successfully
- Added option to use Paypal PDT instead of IPN
- Added option to allow other user roles than admin to work with the plugin
- Fixed bug, the attribute "show_titles" of the wphostel-list shortcode did not initially take effect when page is loaded

= Version 0.9 =
- Changed the booking form design to avoid styling issues
- Added setting to auto-cleanup unconfirmed/unpaid bookings after given interval of time
- Added setting for required minimum stay (X days)
- Added {{room-name}} variable for the email contents
- Added "max_days" attribute for the [wphostel-list] shortcode
- Your custom date format will now be used accross the date selector fields
- The datepicker can now be localized and styled using the configuration fields on the options page
- Optionally send notification emails when marking booking as paid from admin 

= Version 0.8 = 
- Reworked all forms to work only with Ajax. This will let you use multiple 
- Removed the requirement and setting for booking form URL. This is no longer needed
- Improved the booking form validations
- "Per room" price is now available. When this is selected number of beds become irrelevant because your guests are booking the entire room.
- Fixed bug: tables were not properly created on installation
- Setting a custom currently is available
- Added ajax loading of the beds in the booking form to prevent confusing numbers on the private rooms.
- Fixed bug in [wphostel-book] shortcode

= Version 0.7 =

- Added "wphostel-book" shortcode which allows you to place a booking button on any page (usually on a page where you have described your room manually, with pictures etc)
- Added a validaion on the [wphostel-list] so no more than 5 days interval can be selected (to avoid creating long ugly tables with rooms). Setting soon to be made configurable.
- Added zebra tables in manage bookings and manage rooms pages
- Changed the date drop-downs on the front end to use the date picker
- Major improvements of the availability logics, differentiating between dorms and private rooms
- Fixed bug with resetting the room type on editing
- Fixed HTML content-type of the auto-mails
- Fixed bug with pending status when manually marking booking as paid
- Fixed JS validation error on the [wphostel-list] shortcode
- Fixed problem with overlapping the "to" day when booking and showing availability
- Fixed issues with unavailable dates: when date is unavailable, all beds should be considered unavailable

= Version 0.5.9 =

First public release
