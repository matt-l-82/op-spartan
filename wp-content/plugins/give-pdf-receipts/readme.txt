=== Give PDF Receipts ===
Plugin URI: https://givewp.com
Contributors: givewp
Requires at least: 4.8
Tested up to: 5.5
Requires Give: 2.6.0
Stable Tag: 2.3.11

Dynamically generate PDF Receipts for each completed donation.

== Description ==

The add-on comes with beautifully crafted receipt templates that are easily customizable.

Features of the plugin include:

* Dynamically generate PDF Receipts from each donation
* Integrates with the Payment History `[donation_history]` shortcode to give users a link to download the receipt
* A template tag can easily show a link to a downloadable receipt in the donation receipt

More information at [givewp.com](http://givewp.com/).

== Installation ==

1. Activate the plugin
2. Go to Donations > Settings > PDF Receipts to configure the options
3. Donors can now download receipts for any donation on the Donation History page

== Changelog ==

= 2.3.11: September 16th, 2020 =
* Fix: Added a fallback for PDF generation when a user activates the plugin but doesn't configure any settings.

= 2.3.10: June 30th, 2020 =
* Fix: Resolved a bug with GiveWP 2.7 which could cause a PHP notice when previewing PDFs that would cause the PDF to not display properly.

= 2.3.9: June 16th, 2020 =
* Fix: Resolved an issue with download PDFs on mobile devices on the donation receipt.

= 2.3.8: June 9th, 2020 =
* Fix: If using a custom field template tag in a PDF Receipt and the donor did not complete the field the raw template tag would output incorrectly rather than blank.

= 2.3.7: May 27th, 2020 =
* Fix: Resolve styling issues with Custom PDF Templates in the TinyMCE editor not formatting properly on preview.
* Fix: Add additional checks to help prevent the TCPDF error "Some data has already been output".
* Fix: Resolve an issue with letter size not working correctly for Set PDF Templates.
* Fix: Allow saving of Custom PDF Templates on single donation forms.

= 2.3.6: November 26th, 2019 =
* Fix: Corrected an issue with "Set PDF Templates" customized on a per-donation form basis not properly being displayed and rather displaying the global settings.

= 2.3.5: October 28th, 2019 =
* Fix: Resolved an issue with a conditional checking whether a PDF receipt uses a DIV layout returning false positives.
* Fix: Ensured that DOMPDF template layouts don't return an occasional fatal error when rendered in preview.

= 2.3.4: July 11th, 2019 =
* Fix: Ensure that the correct donation receipt downloads from the WP-Admin area for renewal donations.
* Fix: Added additional security checks when downloading PDF Receipts.

= 2.3.3: February 20th, 2019 =
* Fix: Allow customers to download PDFs directly from their email link.

= 2.3.2: December 13th, 2018 =
* Fix: Hardened the security permissions for downloading PDF receipts to further ensure only admins and the specific donors can download it.
* Fix: Ensure if the company name field is not entered that the {company_name} email tag doesn't output any text.

= 2.3.1: November 13th, 2018 =
* Tweak: Ensure that the "{pdf_receipt}" email tag only displays on the applicable emails in wp-admin.
* Fix: Resolved conflict with WP DSGVO Tools and WP Courseware plugins by loading TCPDF via Composer autoload.
* Fix: Ensure that the Hindi displays properly for the currency and content.
* Fix: Allow Greek characters to display with Open Sans font.
* Fix: Resolved issue in  the admin interface where PDF content would be incorrectly visible on the donation form options screen when using the "Global Options".

= 2.3.0: May 2nd, 2018 =
* New: Added support for Sequential Ordering within Give Core 2.1. This update requires Give 2.1+ so be sure to update Give Core to the latest version!

= 2.2.5: March 7th, 2018 =
* New: Support for Recurring Donations' template tags has been added to PDF receipts. You can now display subscription information within your downloadable PDF receipts. {renewal_link}, {completion_date}, {subscription_frequency},{subscriptions_completed} and {cancellation_date} are all supported.
* New: Improved support for special characters by using the "Deja Vu Sans" font for the entire receipt if the option is enabled. Previously this font would only be used for the currency symbol. This font includes support for many languages that's why it's being used for the entire receipt when the option is enabled to support special characters.
* Tweak: The transaction ID field now returns the gateway ID first then fallsback to Give's internal payment ID if none is found from the gateway.
* Fix: PHP notices when activating the add-on for the first time on certain WordPress site environments.
* Fix: If you're using Fee Recovery the fee amount and the total are now separated within the receipts for an improved breakdown of the donation amount.

= 2.2.4: January 17th, 2018 =
* New: Give core 2.0 is now minimum version requirement for this add-on.
* Fix: Removed an extra arrow icon displaying on the download PDF link.

= 2.2.3: January 9th, 2018 =
* Tweak: Changed the default template from "Blank" to "Fresh Blue" to make customizing easier for new installs.

= 2.2.2: December 21st, 2017 =
* Fix: Prevent PHP fatal error if Give core is deactivated while this add-on is active.

= 2.2.1: December 14th, 2017 =
* Fix: Conflict with Stripe plugin not properly displaying donation amounts within the PDF receipts.
* Fix: Formatting issue with the billing address template tag output.

= 2.2: December 12th, 2017 =
* New: The plugin now uses table based layouts for the "Custom PDF Builder" which allow you to much more dependably edit and customize the text and layout.
* New: The "Custom PDF Builder" now allows you to adjust the page size of the PDFs.
* New: Greatly improved the layouts for the "Set PDF Templates". They now look much more modern and you can customize the main color.
* Tweak: Removed unnecessary arrow icon from "Resend Receipt" option within donation listing screen in wp-admin.

= 2.1: October 25th, 2017 =
* New: Added the ability to create a customize PDF receipts per donation form.
* New: Added the ability to easily rename and delete custom PDF receipts.
* New: Added a documentation link to the settings pages to easily access how to information on the plugin.
* New: Set PDFs now have an option to preview the PDFs as well.
* Tweak: Improved support for special characters within Set PDF Templates.
* Tweak: Improved visual layout for the "Default" set template.
* Fix: {transaction_id} pulls the same info as the {payment_id} tag incorrectly.
* Fix: Template tag style was not correctly displaying since version 1.8 - that's been improved and looks better now.
* Fix: When you preview an email receipt it now supports the {pdf_receipt} link tag.

= 2.0.8 =
* New: Added compatiblity with the Fee Recovery add-on so the donation amount field outputs the fee vs donation breakdown.
* New: New option for Special Character display for the PDF builder which uses the DejaVu Sans font to override helvetica/times/etc to better display special characters.
* New: Added filter "give_pdf_header_styles" which allows you to easily inject CSS into the custom pdf generator.
* New: Added filter "give_pdf_receipts_receipt_not_allowed_td" to easily filter content displayed when a PDF receipt is not allowed.
* New: Added a new option to adjust the PDF receipt page size.
* Tweak: Touched up the styling of the "Default" Set PDF template.
* Tweak: Updated various settings description for better understanding and clarity.
* Tweak: Removed the {price} tag for {amount} to match core. {price} will still work but is no longer displayed within the setting's description.
* Fix: Conflict with the {all_customer_fields} tag not being able to pull appropriately when the transaction ID in use was the payment gateways' rather than the Give payment ID.

= 2.0.7 =
* Fix: Added compatibility with Give 1.8.9 handling of email template tags.
* Fix: Removed use of give_get_payment_fee() function deprecated from Give core in 1.8.9.

= 2.0.6 =
* New: If you're using Form Field Manager you can now use the {all_custom_fields} tag within your PDF receipts to display the donor's custom field content.
* Tweak: Updated Dompdf library to the latest stable version.
* Fix: Added settings page compatibility with the latest version of Give core 1.8+.
* Fix: PDF previews span multiple pages with large gaps.

= 2.0.5 =
* Fix: If the Give core plugin is deactivated the PDF Receipts add-on would not properly deactivate itself.

= 2.0.4 =
* New: {today} tag which will output the date the receipt was generated - https://github.com/impress-org/give-pdf-receipts/issues/62
* Tweak: Updated DOMPDF library to the latest version and updated functions for compatiblity - https://github.com/impress-org/give-pdf-receipts/issues/70
* Tweak: Removed usage of deprecated hooks as of Give core version 1.7
* Tweak: Minimum version of Give updated to 1.7
* Fix: Allow cancelled transactions to still have a receipt generated - https://github.com/impress-org/give-pdf-receipts/pull/58
* Fix: The ability to preview PDFs within the admin builder breaks when WooCommerce is active - https://github.com/impress-org/give-pdf-receipts/issues/55

= 2.0.3 =
* New: Plugin activation check PHP version minimum PHP version requirement - https://github.com/impress-org/give-pdf-receipts/issues/45
* New: Support for Mandarin, Japanese, and many other languages now supported via the Droid Sans Full fallback font for "Set PDF Templates" - https://github.com/impress-org/give-pdf-receipts/issues/47

= 2.0.2 =
* Fix: Updated license version number to prevent endless update issue
* Fix: Cache folder returned to TCPDF to fix issues with some servers not allowing system to create it

= 2.0.1 =
* Fix: Compatibility issue with Autoptimize plugin - https://github.com/impress-org/give-pdf-receipts/issues/35
* Fix: Compatibility issue with Hyper Cache plugin - https://github.com/impress-org/give-pdf-receipts/issues/36
* Fix: PDF Receipt Previews Viewable to Non-Admins - https://github.com/impress-org/give-pdf-receipts/issues/37

= 2.0 =
* New PDF Template builder functionality

= 1.0 =
* Plugin Release
