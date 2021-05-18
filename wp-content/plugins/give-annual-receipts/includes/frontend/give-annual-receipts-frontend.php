<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Give_Annual_Receipts_Frontend
 * @author     GiveWP <https://givewp.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Give_Annual_Receipts_Frontend
 */
class Give_Annual_Receipts_Frontend {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function __construct() {

		// Bailout, if annual receipts is disabled from admin.
		if ( ! give_is_setting_enabled( give_get_option( 'give_annual_receipts_enable_disable' ) ) ) {
			return;
		}

		$get_args = give_clean( $_GET );

		add_action( 'give_donation_history_header_before', array( $this, 'render_annual_receipt_notice' ) );

		if ( ! empty( $get_args['give_action'] ) && 'annual_receipt' === $get_args['give_action'] ) {
			add_filter( 'give_donation_history_shortcode_html', array( $this, 'render_annual_receipt' ), 10, 1 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		if (
			! empty( $get_args['give_action'] )
			&& 'preview_annual_receipts' === $get_args['give_action']
			&& isset( $get_args['donor'] )
		) {
			add_action( 'wp', 'give_annual_receipts_generate_pdf' );
		}

		add_shortcode( 'give_annual_receipts_notice', array( $this, 'give_annual_receipt_notice' ) );

	}

	/**
	 * Register the stylesheets for the front area.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function enqueue_styles() {
		wp_register_style( GIVE_ANNUAL_RECEIPTS_SLUG, GIVE_ANNUAL_RECEIPTS_URL . 'assets/dist/css/give-annual-receipts.css', array(),
			GIVE_ANNUAL_RECEIPTS_VERSION, 'all' );
		wp_enqueue_style( GIVE_ANNUAL_RECEIPTS_SLUG );
	}

	/**
	 * Renders the annual receipt notice on the donation history page.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function render_annual_receipt_notice() {

		// Get donor object.
		$donor = give_annual_receipts_get_donor_object();

		// Bailout, if donor object doesn't exists.
		if ( ! is_object( $donor ) && empty( $donor ) ) {
			return;
		}

		$receipt_link_url = wp_nonce_url( home_url( add_query_arg( 'give_action', 'annual_receipt' ) ), 'annual-receipt-' . $donor->id );

		echo '<link rel="stylesheet" href="' . GIVE_ANNUAL_RECEIPTS_URL . 'assets/dist/css/give-annual-receipts.css" type="text/css" media="all">';
		echo '<div class="give-annual-callout">';
		echo '<i class="give-annual-callout__icon"></i>';
		echo '<span class="give-annual-callout__text">';
		printf(
			__( 'View your <a href="%s">annual receipt and detailed giving history&nbsp;&raquo;</a>', 'give-annual-receipts' ),
			esc_url( $receipt_link_url )
		);
		echo '</span>';
		echo '</div>';
	}

	/**
	 * Renders the annual receipt.
	 *
	 * @since 1.0.0
	 *
	 * @todo Remove usage of donation history content which is calling more than 500 queries.
	 *
	 * @param mixed $content Donation History Content.
	 * @return string returns content of receipt list.
	 */
	public function render_annual_receipt( $content ) {
		$donor = give_annual_receipts_get_donor_object();

		if ( empty( $donor ) || ( isset( $_GET['_wpnonce'] ) && ! wp_verify_nonce( $_GET['_wpnonce'], 'annual-receipt-' . $donor->id ) ) ) {
			return $content;
		}

		$give_options = give_get_settings();
		$end_month    = (int)$give_options['give_annual_receipts_tax_month'];
		$end_day      = (int)$give_options['give_annual_receipts_tax_day'];
		$years        = give_annual_receipts_get_receipt_years( $donor, $end_month, $end_day );

		ob_start(); ?>

		<strong><?php esc_html_e( 'Annual receipts for download or print:', 'give-annual-receipts' ); ?></strong>
		<ul class="give-bullet-nav">
			<?php
			if ( ! empty( $years ) ) :
				foreach ( $years as $nav_year ):
					?>
					<li class="give-bullet-nav__item">
						<?php
						$nav_link_url  = add_query_arg(
							array(
								'give_action'  => 'preview_annual_receipts',
								'donor'        => isset( $donor->id ) ? $donor->id : 0,
								'receipt_year' => $nav_year,
							),
							give_get_history_page_uri()
						);
						$nav_link_text = sprintf(
							__( '%s Annual Receipt (PDF)', 'give-annual-receipts' ),
							give_annual_receipts_get_year_label( $nav_year, $end_month, $end_day )
						);
						printf(
							'<a href="%1$s" target="_blank">%2$s</a>',
							esc_attr( $nav_link_url ),
							esc_html( $nav_link_text )
						);
						?>
					</li>
				<?php endforeach;
			endif; ?>
		</ul>

		<?php
		$this->render_donations_list();

		return ob_get_clean();
	}


	/**
	 * Lists annual donations data based on the year.
	 *
	 * @since 1.0.0
	 */
	public function render_donations_list() {
		global $wp;
		$donor = give_annual_receipts_get_donor_object();

		if ( empty( $donor ) ) {
			return;
		}

		// Determine the valid receipt years for the donor.
		$give_options = give_get_settings();
		$date_format  = give_date_format();
		$end_month    = (int)$give_options['give_annual_receipts_tax_month'];
		$end_day      = (int)$give_options['give_annual_receipts_tax_day'];
		$years        = give_annual_receipts_get_receipt_years( $donor, $end_month, $end_day );

		if ( empty( $years ) ) {
			return;
		}

		// Determine the date range and payments for the current tax year.
		$receipt_year = isset( $_GET['receipt_year'] ) ? absint( $_GET['receipt_year'] ) : $years[0];
		$date_range   = give_annual_receipts_get_date_range( $receipt_year, $end_month, $end_day );
		$payments     = give_annual_receipts_get_donors_payments_by_year(
			$donor->id,
			$date_range['start_date'],
			$date_range['end_date']
		);

		if ( empty( $payments ) ) {
			return;
		}

		// Initialize annual sums.
		$annual_total = 0;
		$annual_count = count( $payments );
		?>

		<strong><?php esc_html_e( 'Donations:', 'give-annual-receipts' ); ?></strong>
		<ol class="give-inline-nav">
			<?php foreach ( $years as $year ) :
				$active_class  = '';
				$nav_link_text = give_annual_receipts_get_year_label( $year, $end_month, $end_day );
				$nav_link_url  = home_url( add_query_arg(
					array(
						'give_action'  => 'annual_receipt',
						'receipt_year' => $year,
					)
				) );

				if ( $year === $receipt_year ) {
					$active_class = ' give-inline-nav__item--active';
				}
				?>
				<li class="give-inline-nav__item<?php echo esc_attr( $active_class ); ?>">
					<?php
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_attr( $nav_link_url ),
						esc_html( $nav_link_text )
					);
					?>
				</li>
			<?php endforeach; ?>
		</ol>

		<ul class="give-annual-donations">
			<?php foreach ( $payments as $payment ) :

				$form_id = give_get_meta( $payment->ID, '_give_payment_form_id', true );
				/**
				 * Filters the featured image size used in annual receipts.
				 *
				 * @since 1.0.0
				 *
				 * @param string $size Any valid image size.
				 */
				$featured_image_size = apply_filters(
					'give_annual_receipts_featured_image_size',
					'thumbnail'
				);
				$featured_image      = get_the_post_thumbnail( $form_id, $featured_image_size );
				$donation_id         = $payment->ID;
				$donation_amount     = give_donation_amount( $payment->ID, true );
				$donation_date       = date_i18n( $date_format, strtotime( $payment->post_date ) );
				$gateway             = give_get_payment_gateway( $payment->ID );
				$gateway_label       = give_get_gateway_checkout_label( $gateway );
				$donation_total      = give_annual_receipts_get_donation_amount( $donation_id );
				$annual_total        += $donation_total;
				$back_url            = home_url( $wp->request );
				?>
				<li class="give-annual-donations__item">
					<div class="give-annual-donations__media">
						<?php
						if ( ! empty( $featured_image ) ) {
							echo $featured_image;
						} else {
							/**
							 * Filters the form's placeholder image used in annual receipts.
							 *
							 * @since 1.0.0
							 *
							 * @param string $placeholder_image URL of the placeholder image.
							 * @param int    $form_id           Form ID of the donation.
							 */
							$placeholder_image = apply_filters( 'give_annual_receipts_placeholder_image',
								GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle-grey.svg',
								$form_id
							);

							printf(
								'<img class="%s" src="%s" alt="">',
								'give-annual-donations__placeholder-image',
								esc_attr( $placeholder_image )
							);
						}
						?>
					</div>
					<div class="give-annual-donations__body">
						<strong class="give-annual-donations__form-title">
							<?php echo give_get_meta( $payment->ID, '_give_payment_form_title', true ); ?>
						</strong>
						<p class="give-annual-donations__summary">
							<?php
							printf(
								/* translators: annual receipt amount, date, and gateway */
								esc_html__( '%1$s donated on %2$s using %3$s', 'give-annual-receipts' ),
								"<strong>{$donation_amount}</strong>",
								"<strong>{$donation_date}</strong>",
								"<strong>{$gateway_label}</strong>"
							);
							?>
						</p>
						<span class="give-annual-donations__meta">
						<?php echo esc_html__( 'Donation ID:', 'give-annual-receipts' ) . ' ' . Give()->seq_donation_number->get_serial_code( $donation_id ); ?>
					</span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="give-annual-donations-summary">
			<?php
			$start_date = date( $date_format, strtotime( $date_range['start_date'] ) );
			$end_date   = date( $date_format, strtotime( $date_range['end_date'] ) );
			printf(
				translate_nooped_plural(
						_n_noop(
							'Between %1$s and %2$s, you donated %3$s in a single donation.',
							'Between %1$s and %2$s, you donated %3$s across %4$s donations.',
							'give-annual-receipts'
						),
					$annual_count
				),
				$start_date,
				$end_date,
				give_currency_filter( give_format_amount( $annual_total ) ),
				$annual_count
			);
			?>
		</p>
		<a href="<?php echo esc_url( $back_url ); ?>">&laquo;&nbsp;<?php esc_html_e( 'Back to All Receipts', 'give-annual-receipts' ); ?></a>
		<?php
	}

	/**
	 * Annual Receipt notice Shortcode
	 *
	 * @since  1.0.1
	 *
	 * @return string|bool
	 */
	function give_annual_receipts_notice() {
		// Get donor object.
		$donor = give_annual_receipts_get_donor_object();
		if ( ! is_object( $donor ) && empty( $donor ) ) {
			$receipt_link_url = esc_url(
				add_query_arg(
					'give_action',
					'annual_receipt',
					give_get_history_page_uri()
				)
			);
		} else {
			$receipt_link_url = wp_nonce_url( esc_url(
				add_query_arg(
					'give_action',
					'annual_receipt',
					give_get_history_page_uri()
				)
			), 'annual-receipt-' . $donor->id );
		}
		ob_start(); ?>
		<link rel="stylesheet" href="<?php echo GIVE_ANNUAL_RECEIPTS_URL; ?>assets/dist/css/give-annual-receipts.css" type="text/css" media="all">
		<div class="give-annual-callout">
			<i class="give-annual-callout__icon"></i>
			<span class="give-annual-callout__text">
		<?php printf(
			__( 'View your <a href="%s">annual receipt and detailed giving history&nbsp;&raquo;</a>', 'give-annual-receipts' ),
			esc_url( $receipt_link_url )
		); ?>
		</span>
		</div>
		<?php return ob_get_clean();
	}

}
