=== Give - Annual Receipts ===
Contributors: givewp
Donate link: https://givewp.com/
Tags: givewp, donation, donations, donation plugin, wordpress donation plugin, wp donation, donors, display donors, give donors, anonymous donations
Requires at least: 4.9
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 1.1.0
Requires Give: 2.10.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provide your donors with a quick and easy way to download a receipt for all their donations in a given year.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds functionality for your donors to download annual giving receipts from their donation history page.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.4 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.0: March 23rd, 2021 =
* New: Added support for the new Donor Profile introduced in GiveWP 2.10.0

= 1.0.3: November 6th, 2020 =
* Fix: Correct SQL error occurring on Donation History page

= 1.0.2: May 7th, 2019 =
* New: Change the Annual Receipt download filename from `give_annual_receipt_download.pdf` to be more logical and obvious. Now it reflects the donor name and the year being downloaded: `{fullname}-{year}-annual-receipt.pdf`.

= 1.0.1: January 18th, 2019 =
* Fix: Ensure that the donation ID displays properly when Give's sequential ID option is not enabled.

= 1.0.0: December 28th, 2018 =
* Initial plugin release. Yippee!
