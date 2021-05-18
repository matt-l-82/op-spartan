<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Give_Annual_Receipts_Admin
 * @subpackage Give_Annual_Receipts_Admin/admin
 * @author     GiveWP <https://givewp.com>
 */

defined( 'ABSPATH' ) || exit;

class Give_Annual_Receipts_Admin {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		$give_options = give_get_settings();

		// Register new type for multi dropdown.
		add_action( 'give_admin_field_multi_dropdown', array( $this, 'give_annual_receipt_custom_field' ), 10, 1 );

		// Enqueue Script and Style for Admin.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'tax_year_field_save' ) );
		add_action( 'give_admin_field_annual_receipts_preview_button', array(
			$this,
			'annual_receipts_preview_button'
		), 10, 2 );

		// Only display annual receipts if enabled.
		if ( ! empty( $give_options['give_annual_receipts_enable_disable'] ) && 'enabled' === $give_options['give_annual_receipts_enable_disable'] ) {
			add_action( 'admin_init', array( $this, 'give_annual_receipts_template_preview' ) );
			add_action( 'give_donor_after_tables', array(
				$this,
				'give_annual_receipts_admin_annual_receipts'
			), 10, 1 );
			add_action( 'admin_init', array( $this, 'give_annual_receipts_admin_action' ) );
		}
	}

	/**
	 * Function is used to create custom field in settings.
	 * settings page.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $value Key-value pairs representing a setting.
	 *
	 * @return void
	 */
	public function give_annual_receipt_custom_field( $setting ) {
		// Return if value type is not set.
		if ( ! isset( $setting['type'] ) ) {
			return;
		}
		?>

		<tr valign="top" <?php echo ! empty( $setting['wrapper_class'] ) ? 'class="' . $setting['wrapper_class'] . '"' : ''; ?>>
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $setting['id'] ); ?>"><?php echo esc_html( $setting['name'] ); ?></label>
			</th>
			<td colspan="2">
				<fieldset class="multi_dropdown">
					<table class="form-table give-annual-receipts-tab-body give-setting-tab-body give-setting-tab-body-multi-dropdown">
						<tbody>
						<tr>
							<td><select name="give_annual_receipts_tax_month"
										id="give_annual_receipts_tax_month"></select></td>
							<td><select name="give_annual_receipts_tax_day" id="give_annual_receipts_tax_day"></select>
							</td>
						</tr>
						</tbody>
					</table>
					<div class="give-field-description">
						<?php echo esc_html( $setting['description'] ); ?>
					</div>
				</fieldset>
			</td>
		</tr>

		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles() {
		if ( ( isset( $_GET['page'] ) && 'give-settings' === $_GET['page'] ) && isset( $_GET['tab'] ) && 'annual_receipts' === $_GET['tab'] ) {
			wp_register_style(
				GIVE_ANNUAL_RECEIPTS_SLUG, GIVE_ANNUAL_RECEIPTS_URL . 'assets/dist/css/give-annual-receipts-admin.css', array(),
				GIVE_ANNUAL_RECEIPTS_VERSION, 'all'
			);
			wp_enqueue_style( GIVE_ANNUAL_RECEIPTS_SLUG );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts() {
		if ( ( isset( $_GET['page'] ) && 'give-settings' === $_GET['page'] ) && isset( $_GET['tab'] ) && 'annual_receipts' === $_GET['tab'] ) {
			$give_options = give_get_settings();

			// Register and enqueue JS.
			wp_register_script(
				GIVE_ANNUAL_RECEIPTS_SLUG, GIVE_ANNUAL_RECEIPTS_URL . 'assets/dist/js/give-annual-receipts-admin.js', array( 'give-admin-scripts' ),
				GIVE_ANNUAL_RECEIPTS_VERSION, false
			);
			wp_enqueue_script( GIVE_ANNUAL_RECEIPTS_SLUG );

			// Make data available to JS.
			wp_localize_script(
				'give-annual-receipts', 'giveAnnualReceipts', array(
					'taxYearEndMonth' => (int)$give_options['give_annual_receipts_tax_month'],
					'taxYearEndDay'   => (int)$give_options['give_annual_receipts_tax_day'],
				)
			);
		}

	}

	/**
	 * Save Custom fields settings.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @return bool
	 */
	public function tax_year_field_save() {

		// Get current section.
		$current_section = give_get_current_setting_tab();
		if ( 'annual_receipts' !== $current_section ) {
			return false;
		}

		if ( empty( $_REQUEST['_give-save-settings'] ) || ! wp_verify_nonce( $_REQUEST['_give-save-settings'], 'give-save-settings' ) ) {
			return false;
		}

		$post_data = give_clean( $_POST ); // WPCS: input var ok, CSRF ok.

		// Return if submitted data is empty.
		if ( empty( $post_data ) ) {
			return false;
		}

		foreach ( $post_data as $option_name => $option_value ) {
			if ( 'give_annual_receipts_tax_month' === $option_name || 'give_annual_receipts_tax_day' === $option_name ) {
				// Update option.
				give_update_option( $option_name, $option_value );
			} elseif ( 'give_annual_receipts_footer' === $option_name ) {
				give_update_option( $option_name, wp_kses_post( $option_value ) );
			} elseif ( 'give_annual_receipts_content_after_receipt' === $option_name ) {
				give_update_option( $option_name, wp_kses_post( $option_value ) );
			} elseif ( 'give_annual_receipts_content_before_receipt' === $option_name ) {
				give_update_option( $option_name, wp_kses_post( $option_value ) );
			}
		}

		return true;
	}

	/**
	 * Set PDF Receipt Preview button.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $value        field array.
	 * @param string $option_value field value.
	 */
	public function annual_receipts_preview_button( $value, $option_value ) {
		ob_start();
		?>
		<tr valign="top give-annual-receipts-preview" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : ''; ?>>
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_attr( $value['name'] ); ?></label>
			</th>
			<td class="give-annual-receipts-preview-button-td" colspan="2">
				<a href="<?php echo wp_nonce_url( add_query_arg( array( 'give_annual_receipts_action' => 'preview_annual_receipts' ), admin_url() ), 'annual_receipt_preview_nonce' ); ?>"
				   class="button-secondary" target="_blank"
				   title="<?php _e( 'Preview PDF', 'give-annual-receipts' ); ?> "><?php _e( 'Preview PDF', 'give-annual-receipts' ); ?></a>
				<p class="give-field-description"><?php echo give_get_field_description( $value ); ?></p>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Generate a PDF preview.
	 *
	 * When the admin clicks the "preview" button to view the PDF rendered.
	 */
	public function give_annual_receipts_template_preview() {

		// Admin's only.
		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_GET['give_annual_receipts_action'] )
		     && isset( $_GET['_wpnonce'] )
		     && wp_verify_nonce( $_GET['_wpnonce'], 'annual_receipt_preview_nonce' )
		) {
			give_annual_receipts_preview();
		}

	}

	/**
	 *  Function is used to display annual donation stats.
	 *
	 * @param $donor Give_Donor object
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function give_annual_receipts_admin_annual_receipts( $donor ) {
		?>
		<h3><?php _e( 'Annual Receipts', 'give-annual-receipts' ); ?></h3>
		<?php
		global $wpdb;
		$give_options = give_get_settings();
		$end_month    = (int)$give_options['give_annual_receipts_tax_month'];
		$end_day      = (int)$give_options['give_annual_receipts_tax_day'];
		$years        = give_annual_receipts_get_receipt_years( $donor, $end_month, $end_day );

		if ( ! empty( $years ) ) {
			foreach ( $years as $receipt_year ) {
				$date_range   = give_annual_receipts_get_date_range( $receipt_year, $end_month, $end_day );
				$start_date   = $date_range['start_date'];
				$end_date     = $date_range['end_date'];
				$payments     = give_annual_receipts_get_donors_payments_by_year(
					$donor->id,
					$start_date,
					$end_date
				);

				if ( empty( $payments ) ) {
					return false;
				}

				foreach ( $payments as $payment ) {
					$payment_ids[] = (int) $payment->ID;
				}

				$count_donations = $wpdb->get_results(
					$wpdb->prepare( "SELECT count(ID) as cnt FROM {$wpdb->posts} WHERE post_type = %s AND (post_status = 'publish' OR post_status = 'give_subscription') AND ID IN ($donor->payment_ids) AND post_date >= '$start_date' and post_date <= '$end_date'", 'give_payment' ),
					ARRAY_N
				);

				if ( ! empty( $count_donations ) && ! empty( $payment_ids ) ) {
					$annual_data[ $receipt_year ]['count']  = $count_donations[0][0];
					$annual_data[ $receipt_year ]['amount'] = $this->give_annual_receipts_get_donation_amount_by_year( $payment_ids );
				}
				$payment_ids = array();
			}
		}
		?>
		<table class="wp-list-table widefat striped donations">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Year', 'give-annual-receipts' ); ?></th>
				<th scope="col"><?php _e( 'Amount', 'give-annual-receipts' ); ?></th>
				<th scope="col"><?php _e( 'Donations', 'give-annual-receipts' ); ?></th>
				<th scope="col"><?php _e( 'Actions', 'give-annual-receipts' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			if ( isset( $annual_data ) && ! empty( $annual_data ) ) {
				foreach ( $annual_data as $year => $annual_value ) :
					$year_label = give_annual_receipts_get_year_label( $year, $end_month, $end_day );
					?>
					<tr>
						<td><?php echo esc_html( $year_label ) ?></td>
						<td><?php echo ! empty( $annual_value['amount'] ) ? give_currency_filter( give_format_amount( $annual_value['amount'] ) ) : '-'; ?></td>
						<td><?php echo isset( $annual_value['count'] ) ? $annual_value['count'] : '-'; ?></td>
						<td>
							<?php
							printf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								add_query_arg(
									array(
										'give_annual_receipts_admin_action' => 'preview_annual_receipts',
										'donor'                             => isset( $_GET['id'] ) ? $_GET['id'] : 0,
										'receipt_year'                      => $year,
									),
									admin_url()
								),
								__( 'View Statement', 'give-annual-receipts' )
							);
							?>
						</td>
					</tr>
					<?php
				endforeach;
			}
			?>
			</tbody>
		</table>
		<?php
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

	/**
	 * Generate a PDF preview.
	 *
	 * When the admin clicks the "preview" button to view the PDF rendered.
	 */
	public function give_annual_receipts_admin_action() {
		if ( isset( $_GET['give_annual_receipts_admin_action'] ) && 'preview_annual_receipts' === $_GET['give_annual_receipts_admin_action'] ) {
			give_annual_receipts_admin_download();
		}
	}
}
