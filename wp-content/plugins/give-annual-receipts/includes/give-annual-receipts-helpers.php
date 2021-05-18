<?php
/**
 * Annual Receipts Helper Functions
 */

/**
 * Gets valid receipt years for a given donor and tax year end date.
 *
 * If the tax year end date is December 31, then this function uses a simple
 * query to return receipt years in which the donor made donations. If the tax
 * year end date is any other date, then each donation date is checked to
 * determine the appropriate receipt years.
 *
 * @param Give_Donor|stdClass $donor The donor object.
 * @param int $end_month
 * @param int $end_day
 *
 * @return array Array of valid receipt years for the given donor.
 */
function give_annual_receipts_get_receipt_years( $donor, $end_month, $end_day ) {
	$receipt_years = array();

	// Determine receipts years differently based on tax year end date.
	if ( 12 === $end_month && 31 === $end_day ) {
		// The tax year is the calendar year, so we only need the years.
		$receipt_years = give_annual_receipts_get_donation_years( $donor );
	} else {
		// The tax year is NOT the calendar year, so we need to check full dates.
		$donation_dates = give_annual_receipts_get_donation_dates( $donor );

		// Process each donation date to determine the appropriate receipt year.
		foreach( $donation_dates as $date ) {
			$donation_year  = (int)$date[0];
			$donation_month = (int)$date[1];
			$donation_day   = (int)$date[2];

			if (
				$donation_month > $end_month
				|| ( $donation_month === $end_month && $donation_day > $end_day )
			) {
				// Donation year and receipt year are the same.
				$receipt_year = $donation_year;
			} else {
				// Donation belongs to the previous receipt year.
				$receipt_year = $donation_year - 1;
			}

			// Only add receipt year if it has not already been added.
			if ( ! in_array( $receipt_year, $receipt_years ) ) {
				$receipt_years[] = $receipt_year;
			}
		}
	}

	/**
	 * Filters the receipt years for a given donor.
	 *
	 * @param array  Array of valid receipt years for the given donor.
	 * @param Object The donor object.
	 */
	return apply_filters( 'give_annual_receipts_receipt_years', $receipt_years, $donor );
}

/**
 * Gets the calendar years in which a donor donated.
 *
 * This function does not account for tax years spanning multiple years. It
 * simply returns an array of years in which the donor made a donation.
 *
 * @param Object $donor The donor object.
 * @return array Array of years.
 */
function give_annual_receipts_get_donation_years( $donor ) {
	global $wpdb;
	$donation_years = array();
	$years          = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT YEAR(post_date) FROM {$wpdb->posts}
			WHERE post_type = %s
			AND (post_status = 'publish' OR post_status = 'give_subscription')
			AND ID IN ({$donor->payment_ids})
			GROUP BY YEAR(post_date)",
			'give_payment'
		), ARRAY_N
	);

	if ( empty( $years ) ) {
		return array();
	}

	// Parse years into a more usable format.
	foreach( $years as $year ) {
		$donation_years[] = (int)$year[0];
	}

	return $donation_years;
}

/**
 * Gets the full dates in which a single donor donated.
 *
 * @param Object $donor The donor object.
 * @return array Nested array of dates, each containing a year, month, and day.
 */
function give_annual_receipts_get_donation_dates( $donor ) {
	global $wpdb;
	$donation_dates = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT YEAR(post_date) as year, MONTH(post_date) as month, DAY(post_date) as day
			FROM {$wpdb->posts}
			WHERE post_type = %s
			AND (post_status = 'publish' OR post_status = 'give_subscription')
			AND ID IN ($donor->payment_ids)
			ORDER BY post_date DESC",
			'give_payment'
		),
		ARRAY_N
	);

	return $donation_dates;
}

/**
 * Retrieves the donors payments by start year and end year.
 *
 * @param int    $donor_id   The donor ID.
 * @param string $start_date The beginning of the date range.
 * @param string $end_date   The end of the date range.
 *
 * @return array|bool|null|object
 */
function give_annual_receipts_get_donors_payments_by_year( $donor_id, $start_date, $end_date ) {
	global $wpdb;
	$payments = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT {$wpdb->posts}.* FROM {$wpdb->posts} INNER JOIN {$wpdb->prefix}give_donationmeta ON ( {$wpdb->posts}.ID = {$wpdb->prefix}give_donationmeta.donation_id ) WHERE 1=1
			AND ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'give_subscription')
			AND {$wpdb->posts}.post_date >= '$start_date' AND {$wpdb->posts}.post_date <= '$end_date'
			AND {$wpdb->prefix}give_donationmeta.meta_key = '_give_payment_donor_id' AND {$wpdb->prefix}give_donationmeta.meta_value = '%s'
			AND {$wpdb->posts}.post_type = 'give_payment'
			ORDER BY post_date ASC",
			$donor_id
		)
	);

	if ( empty( $payments ) ) {
		return false;
	}

	return $payments;
}

/**
 * Function is used to get the Give_Donor object.
 *
 * @since 1.0.0
 *
 * @return bool|mixed|object Give_Donor
 */
function give_annual_receipts_get_donor_object() {

	$donor = false;

	if ( is_user_logged_in() || current_user_can( 'view_give_sensitive_data' ) ) {

		$donor = Give()->donors->get_donor_by( 'user_id', get_current_user_id() );

	} elseif ( ! is_user_logged_in() ) {

		// Check whether it is receipt access session?
		if ( give_is_setting_enabled( give_get_option( 'email_access' ) ) ) {

			if ( isset( $_COOKIE['give_nl'] ) ) {
				$email_access_token = give_clean( $_COOKIE['give_nl'] );
			} elseif ( isset( $_GET['give_nl'] ) ) {
				$email_access_token = give_clean( $_GET['give_nl'] );
			}
			$donor = ! empty( $email_access_token )
				? Give()->donors->get_donor_by_token( $email_access_token )
				: false;

		} else {
			$email = Give()->session->get( 'give_email' );
			$donor = Give()->donors->get_donor_by( 'email', $email );
		}
	}

	return $donor;
}

/**
 * Gets the start and end date of the annual receipt.
 *
 * If a tax year end date of December 31 is provided, it is handled with special
 * logic to ensure the start and end year are correct.
 *
 * @param int $receipt_year The year of the annual receipt.
 * @param int $end_month    The month in which the tax year ends.
 * @param int $end_day      The day on which the tax year ends.
 *
 * @return array Array of start and end dates.
 */
function give_annual_receipts_get_date_range( $receipt_year, $end_month, $end_day ) {
	if ( 12 === $end_month && 31 === $end_day ) {
		$start_date = date( 'Y-m-d H:i:s', strtotime( $receipt_year . '-01-01' ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $receipt_year . '-12-31' ) );
	} else {
		$next_year  = $receipt_year + 1;
		$start_date = date( 'Y-m-d H:i:s', strtotime( "{$receipt_year}-{$end_month}-{$end_day} +1 day" ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( "{$next_year}-{$end_month}-{$end_day}" ) );
	}

	return array(
		'start_date' => $start_date,
		'end_date'   => $end_date,
	);
}

/**
 * Gets the donation amount compatible with Fee Recovery and Currency Switcher add-ons.
 *
 * @since 1.0.0
 *
 * @param int $donation_id Donation ID.
 * @return float|int The donation amount.
 */
function give_annual_receipts_get_donation_amount( $donation_id ) {

	$donation_amount              = Give()->payment_meta->get_meta( $donation_id, '_give_payment_total', true );
	$is_currency_switcher_enabled = Give()->payment_meta->get_meta( $donation_id, '_give_cs_enabled', true );

	// Update donation amount when currency switcher is used to process the donation.
	if ( give_is_setting_enabled( $is_currency_switcher_enabled ) ) {
		$donation_amount = $donation_amount / Give()->payment_meta->get_meta( $donation_id, '_give_cs_exchange_rate', true );
	}

	return $donation_amount;
}

/**
 * Gets the appropriate receipt year label based on tax year end date.
 *
 * @since 1.0.0
 *
 * @param int $year
 * @return string The label containing either a single year or year range.
 */
function give_annual_receipts_get_year_label( $year, $end_month, $end_day ){
	if( 12 === $end_month && 31 === $end_day ) {
		return $year;
	}

	$next_year = $year + 1;

	return "{$year}&ndash;{$next_year}";
}

/**
 * Function is used to generate pdf file name.
 *
 * @since   1.0.1
 *
 */
function give_annual_receipts_generate_file_name() {
	$donor_id       = isset( $_GET['donor'] ) ? give_clean( $_GET['donor'] ) : '';
	$receipt_year   = isset( $_GET['receipt_year'] ) ? $_GET['receipt_year'] : '';
	$donor_name     = give_get_donor_name_by( $donor_id, 'donor' );
	$end_month      = (int) give_get_option( 'give_annual_receipts_tax_month' );
	$end_day        = (int) give_get_option( 'give_annual_receipts_tax_day' );
	$date_range     = give_annual_receipts_get_year_label( $receipt_year, $end_month, $end_day );
	$financial_year = str_replace( '&ndash;', '-', $date_range );
	$name           = preg_replace( '/[^a-zA-Z0-9_-]/', '', $donor_name );

	return $filename = $name . '-' . $financial_year . '-annual-receipt';
}