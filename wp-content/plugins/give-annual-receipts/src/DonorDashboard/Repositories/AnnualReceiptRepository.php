<?php

namespace GiveAnnualReceipts\DonorDashboard\Repositories;

use Give\ValueObjects\Money;
use Give\Framework\Database\DB;

class AnnualReceiptRepository {
    public function getByDonorId ( int $id ) {

        global $wpdb;

        $donor = give()->donors->get_donor_by( 'id', $id );

        $give_options = give_get_settings();
		$end_month    = (int)$give_options['give_annual_receipts_tax_month'];
		$end_day      = (int)$give_options['give_annual_receipts_tax_day'];
		$years        = give_annual_receipts_get_receipt_years( $donor, $end_month, $end_day );

        $data = [];

        if ( ! empty( $years ) ) {

			foreach ( $years as $receipt_year ) {

				$date_range   = give_annual_receipts_get_date_range( $receipt_year, $end_month, $end_day );
				$start_date   = $date_range['start_date'];
				$end_date     = $date_range['end_date'];

				$annual_data[ $receipt_year ]['count']  = $this->getDonationCount($start_date, $end_date, $donor->id);
				$annual_data[ $receipt_year ]['amount'] = $this->getRevenue($start_date, $end_date, $donor->id);

                if ( isset( $annual_data ) && ! empty( $annual_data ) ) {
                    foreach ( $annual_data as $year => $annual_value ) {
                        $data[$year] = [
                            'year' => [
                                'label' => give_annual_receipts_get_year_label( $year, $end_month, $end_day ),
                                'value' => $year,
                            ],
                            'amount' => [
                                'formatted' => isset($annual_value['amount']) ? give_currency_filter( give_format_amount( $annual_value['amount'] ), [ 'decode_currency' => true ] ) : '-',
                                'raw' => $annual_value['amount'],
                            ],
                            'count' => isset( $annual_value['count'] ) ? $annual_value['count'] : '-',
                            'statementUrl' => add_query_arg(
                                array(
                                    'give_action'  => 'preview_annual_receipts',
                                    'donor'        => isset( $donor->id ) ? $donor->id : 0,
                                    'receipt_year' => $year,
                                ),
                                give_get_history_page_uri()
                            ),
                        ];
                    }
                }

			}
		}

        return $data;

    }

	/**
	 * Get donor revenue
	 *
	 * @param int $donorId
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function getRevenue( $startDate, $endDate, $donorId ) {
		$aggregate = $this->getDonationAggregate( 'sum(revenue.amount)', $startDate, $endDate, $donorId );
		error_log( serialize( $aggregate ) );
		return Money::ofMinor( $aggregate->result, give_get_option( 'currency' ) )->getAmount();
	}

	/**
	 * Get donations count for donor
	 *
	 * @param string $startDate,
	 * @param string $endDate,
	 * @param int $donorId
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function getDonationCount( $startDate, $endDate, $donorId ) {
		$aggregate = $this->getDonationAggregate( 'count(revenue.id)', $startDate, $endDate, $donorId );
		return $aggregate->result;
	}

	private function getDonationAggregate( $rawAggregate, $startDate, $endDate, $donorId ) {
		global $wpdb;
		return DB::get_row(
			DB::prepare(
				"
				SELECT {$rawAggregate} as result
				FROM {$wpdb->give_revenue} as revenue
					INNER JOIN {$wpdb->posts} as posts ON revenue.donation_id = posts.ID
					INNER JOIN {$wpdb->prefix}give_donationmeta as donationmeta ON revenue.donation_id = donationmeta.donation_id
				WHERE donationmeta.meta_key = '_give_payment_donor_id'
					AND donationmeta.meta_value = {$donorId}
					AND posts.post_status IN ( 'publish', 'give_subscription' )
					AND posts.post_date >= '{$startDate}' and posts.post_date <= '{$endDate}'
			"
			)
		);
	}

    /**
	 * @param $payment_ids payment ids array
	 *
	 * @since 1.0.0
	 *
	 * @return int|void returns the total amount deducted for payment_id
	 */

	public function give_annual_receipts_get_donation_amount_by_year( $payment_ids ) {

		if ( empty( $payment_ids ) ) {
			return;
		}
		$total_donation = 0;
		foreach ( $payment_ids as $payment_id ) {
			$payment_total  = Give()->payment_meta->get_meta( $payment_id, '_give_payment_total', true );
			$total_donation = $total_donation + $payment_total;
		}

		return $total_donation;
	}

}