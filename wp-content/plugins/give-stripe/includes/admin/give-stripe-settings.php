<?php
/**
 * Give - Stripe Premium | Admin Settings
 *
 * @since 2.2.0
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Premium_Settings' ) ) {
	/**
	 * Class Give_Stripe_Premium_Settings
	 *
	 * @since 1.0.0
	 */
	class Give_Stripe_Premium_Settings {

		/**
		 * Get only single instance.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var Give_Stripe_Premium_Settings $instance
		 */
		static private $instance;

		/**
		 * Section ID.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var string $section_id
		 */
		private $section_id;

		/**
		 * Section Label.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var string $section_label
		 */
		private $section_label;

		/**
		 * Get single instance of class object.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return Give_Stripe_Premium_Settings
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Setup hooks.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return void
		 */
		public function setup_hooks() {

			$this->section_id    = 'stripe';
			$this->section_label = __( 'Stripe', 'give-stripe' );

			if ( is_admin() ) {
				add_action( 'give_admin_field_stripe_configure_apple_pay', array( $this, 'stripe_configure_apple_pay_field' ), 10, 2 );
				add_filter( 'give_stripe_register_groups', array( $this, 'register_groups' ) );
				add_action( 'give_stripe_add_additional_group_fields', array( $this, 'add_additional_group_fields' ) );
				add_action( 'give_stripe_premium_manual_api_fields', [ $this, 'add_manual_api_key_fields' ], 10, 2 );

			}
		}

		/**
		 * Register Groups.
		 *
		 * @param array $groups List of groups which will create vertical tabs navigation.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return array
		 */
		public function register_groups( $groups ) {

			$groups['plaid']           = __( 'Plaid (ACH)', 'give-stripe' );
			$groups['payment_request'] = __( 'Google/Apple Pay', 'give-stripe' );

			return $groups;
		}

		/**
		 * Add Manual API Keys Fields.
		 *
		 * @param array $accounts All Stripe Accounts.
		 *
		 * @since  2.2.6
		 * @access public
		 *
		 * @return void
		 */
		public function add_manual_api_key_fields( $accounts ) {
			?>
			<tr valign="top" class="stripe-cc-field-format-settings">
				<th scope="row" class="titledesc">
					<label for="stripe_cc_fields_format">
						<?php echo __( 'Connection Type', 'give-stripe' ); ?>
					</label>
				</th>

				<td class="give-forminp give-forminp-radio_inline give-radio-inline">
					<fieldset>
						<ul>
							<li>
								<label>
									<input name="stripe_connection_type" value="connect" checked="checked" type="radio">
									<?php echo __( 'Stripe Connect', 'give-stripe' ); ?>
								</label>
							</li>
							<li>
								<label>
									<input name="stripe_connection_type" value="manual" type="radio">
									<?php echo __( 'API Keys', 'give-stripe' ); ?>
								</label>
							</li>
							<div class="give-field-description">
								<?php echo __( 'Please select the connection type using which you want to connect Stripe account.', 'give-stripe' ); ?>
							</div>
						</ul>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="give-stripe-account-type-manual give-hidden">
				<th scope="row" class="titledesc">
					<label for="stripe_account_type_manual">
						<?php echo __( 'Live Secret Key', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp">
					<fieldset>
						<input type="text" placeholder="sk_live_xxxxxxxx" name="live_secret_key" value="" />
						<div class="give-field-description">
							<?php echo __( 'Enter your live secret key, found in your Stripe Account Settings.', 'give-stripe' ); ?>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="give-stripe-account-type-manual give-hidden">
				<th scope="row" class="titledesc">
					<label for="stripe_live_publishable_key">
						<?php echo __( 'Live Publishable Key', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp">
					<fieldset>
						<input type="text" placeholder="pk_live_xxxxxxxx" name="live_publishable_key" value="" />
						<div class="give-field-description">
							<?php echo __( 'Enter your live publishable key, found in your Stripe Account Settings.', 'give-stripe' ); ?>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="give-stripe-account-type-manual give-hidden">
				<th scope="row" class="titledesc">
					<label for="stripe_test_secret_key">
						<?php echo __( 'Test Secret Key', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp">
					<fieldset>
						<input type="text" placeholder="sk_test_xxxxxxxx" name="test_secret_key" value="" />
						<div class="give-field-description">
							<?php echo __( 'Enter your test secret key, found in your Stripe Account Settings.', 'give-stripe' ); ?>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="give-stripe-account-type-manual give-hidden">
				<th scope="row" class="titledesc">
					<label for="stripe_test_publishable_key">
						<?php echo __( 'Test Publishable Key', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp">
					<fieldset>
						<input type="text" placeholder="pk_test_xxxxxxxx" name="test_publishable_key" value="" />
						<div class="give-field-description">
							<?php echo __( 'Enter your test publishable key, found in your Stripe Account Settings.', 'give-stripe' ); ?>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="give-stripe-account-type-manual give-hidden">
				<th scope="row" class="titledesc">
					<input
						id="give-stripe-add-new-account"
						class="button button-primary"
						type="button"
						name="submit_manual"
						value="<?php echo __( 'Add New Account', 'give-stripe' );?>"
						data-account="<?php echo give_stripe_get_unique_account_slug( $accounts ); ?>"
						data-url="<?php echo give_stripe_get_admin_settings_page_url(); ?>"
						data-error="<?php echo __( 'Please enter the test as well as live secret and publishable keys to add a Stripe account.', 'give-stripe' ); ?>"
					/>
					<span class="give-stripe-spinner spinner"></span>
				</th>
			</tr>
			<?php
		}

		/**
		 * Register additional group fields.
		 *
		 * @param array $settings List of admin setting fields.
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @return array
		 */
		public function add_additional_group_fields( $settings ) {

			// Payment Request.
			$settings['payment_request'][] = array(
				'id'   => 'give_title_stripe_payment_request',
				'type' => 'title',
			);

			$settings['payment_request'][] = array(
				'name'          => __( 'Configure Apple Pay', 'give-stripe' ),
				'desc'          => 'This option will help you configure Apple Pay with Stripe with just a single click.',
				'wrapper_class' => 'give-stripe-configure-apple-pay give-stripe-account-manager-wrap',
				'id'            => 'stripe_configure_apple_pay',
				'type'          => 'stripe_configure_apple_pay',
			);

			$settings['payment_request'][] = array(
				'name'          => __( 'Button Appearance', 'give-stripe' ),
				'desc'          => __( 'Adjust the appearance of the button style for Google and Apple Pay.', 'give-stripe' ),
				'id'            => 'stripe_payment_request_button_style',
				'wrapper_class' => 'stripe-payment-request-button-style-wrap',
				'type'          => 'radio_inline',
				'default'       => 'dark',
				'options'       => array(
					'light'         => __( 'Light', 'give-stripe' ),
					'light-outline' => __( 'Light Outline', 'give-stripe' ),
					'dark'          => __( 'Dark', 'give-stripe' ),
				),
			);

			$settings['payment_request'][] = array(
				'name'  => __( 'Stripe Gateway Documentation', 'give-stripe' ),
				'id'    => 'display_settings_payment_request_docs_link',
				'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
				'title' => __( 'Stripe Gateway Documentation', 'give-stripe' ),
				'type'  => 'give_docs_link',
			);

			$settings['payment_request'][] = array(
				'id'   => 'give_title_stripe_payment_request',
				'type' => 'sectionend',
			);

			// Plaid ( ACH ).
			$settings['plaid'][] = array(
				'id'   => 'give_title_stripe_plaid',
				'type' => 'title',
			);

			$settings['plaid'][] = array(
				'name'    => __( 'API Mode', 'give-stripe' ),
				'desc'    => sprintf(
				/* translators: %s Plaid API Host Documentation URL */
					__( 'Plaid has several API modes for testing and live transactions. "Test" mode allows you to test with a single sample bank account. "Development" mode allows you to accept up to 100 live donations without paying. "Live" mode allows for unlimited donations. Read the <a target="_blank" title="Plaid API Docs" href="%1$s">Plaid API docs</a> for more information.', 'give-stripe' ),
					esc_url( 'https://plaid.com/docs/api/#api-host' )
				),
				'id'      => 'plaid_api_mode',
				'type'    => 'radio_inline',
				'default' => 'sandbox',
				'options' => array(
					'sandbox'     => __( 'Test', 'give-stripe' ),
					'development' => __( 'Development', 'give-stripe' ),
					'production'  => __( 'Live', 'give-stripe' ),
				),
			);

			$settings['plaid'][] = array(
				'name' => __( 'Plaid Client ID', 'give-stripe' ),
				'desc' => __( 'Enter your Plaid Client ID, found in your Plaid account dashboard.', 'give-stripe' ),
				'id'   => 'plaid_client_id',
				'type' => 'text',
			);

			$settings['plaid'][] = array(
				'name' => __( 'Plaid Secret Key', 'give-stripe' ),
				'desc' => __( 'Enter your Plaid secret key, found in your Plaid account dashboard.', 'give-stripe' ),
				'id'   => 'plaid_secret_key',
				'type' => 'api_key',
			);

			$settings['plaid'][] = array(
				'name'  => __( 'Stripe Gateway Documentation', 'give-stripe' ),
				'id'    => 'display_settings_plaid_docs_link',
				'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
				'title' => __( 'Stripe Gateway Documentation', 'give-stripe' ),
				'type'  => 'give_docs_link',
			);


			$settings['plaid'][] = array(
				'id'   => 'give_title_stripe_plaid',
				'type' => 'sectionend',
			);

			return $settings;
		}

		/**
		 * This function return hidden for fields which should get hidden on toggle of modal checkout checkbox.
		 *
		 * @param string $status Status - Enabled or Disabled.
		 *
		 * @since  1.6
		 * @access public
		 *
		 * @return string
		 */
		public function stripe_modal_checkout_status( $status = 'enabled' ) {
			$stripe_checkout = give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled', 'disabled' ) );

			if (
				( $stripe_checkout && 'disabled' === $status ) ||
				( ! $stripe_checkout && 'enabled' === $status )
			) {
				return 'give-hidden';
			}

			return '';
		}

		/**
		 * Configure Apple Pay Field using Stripe.
		 *
		 * @param array  $value        List of values.
		 * @param string $option_value Option value.
		 *
		 * @since 2.0.8
		 */
		public function stripe_configure_apple_pay_field( $value, $option_value ) {
			$accounts = give_stripe_get_all_accounts();
			?>
			<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . esc_attr( $value['wrapper_class'] ) . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label for="configure_apple_pay">
						<?php esc_attr_e( 'Configure Apple Pay', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp give-forminp-api_key">
					<div class="give-stripe-account-manager-container">
						<?php
						if ( $accounts ) {
							?>
							<div class="give-stripe-account-manager-list">
								<?php
								foreach ( $accounts as $name => $details ) {
									$account_name      = $details['account_name'];
									$account_email     = $details['account_email'];
									$is_registered     = isset( $details['register_apple_pay'] ) && $details['register_apple_pay'];
									$stripe_account_id = $details['account_id'];
									?>
									<div class="give-stripe-account-manager-list-item">
										<span class="give-stripe-account-name">
											<?php echo $account_name; ?>
										</span>
										<span class="give-field-description give-stripe-account-email">
											<?php echo $account_email; ?>
										</span>
										<span class="give-stripe-account-badge <?php echo ! $is_registered ? 'give-hidden' : ''; ?>">
											<?php echo esc_html__( 'Registered', 'give-stripe' );?>
										</span>
										<span class="give-stripe-account-register <?php echo $is_registered ? 'give-hidden' : ''; ?>">
											<a
												class="give-stripe-register-domain"
												href="#"
												data-account="<?php echo $name; ?>"
												data-secret-key="<?php echo $details['live_secret_key']; ?>"
												data-account-id="<?php echo $stripe_account_id; ?>"
												data-type="<?php echo $details['type']; ?>"
											>
												<?php echo esc_html__( 'Register domain', 'give-stripe' );?>
											</a>
										</span>
										<span class="give-stripe-account-actions">
											<span class="give-stripe-account-reset">
												<a
													class="give-stripe-reset-domain"
													href="#"
													data-account="<?php echo $name; ?>"
												>
													<?php echo esc_html__( 'Reset', 'give-stripe' ); ?>
												</a>
											</span>
										</span>
									</div>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
					<p class="give-field-description">
						<?php esc_attr_e( 'This option will help you register your domain to support Apple Pay for each of these Stripe accounts.', 'give-stripe' ); ?>
					</p>
				</td>
			</tr>
			<?php
		}
	}
}

Give_Stripe_Premium_Settings::get_instance()->setup_hooks();
