<?php
/**
 * Give_Stripe_ACH
 *
 * Plaid sandbox testing creds: https://blog.plaid.com/plaid-link/
 * username: plaid_test
 * password: plaid_good
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Give\Helpers\Form\Utils as FormUtils;

/**
 * Class Give_Stripe_ACH
 */
class Give_Stripe_ACH extends Give_Stripe_Gateway {

	/**
	 * Array of API keys.
	 *
	 * @var array
	 */
	private $keys = array();

	/**
	 * Give_Stripe_ACH constructor.
	 */
	public function __construct() {

		$this->id = 'stripe_ach';

		parent::__construct();

		// Remove CC fieldset.
		add_action( 'give_stripe_ach_cc_form', [ $this, 'ach_modal_notice' ] );

		// Load Stripe ACH scripts only when gateway is active.
		if ( give_is_gateway_active( $this->id ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
		}

		add_action( 'give_checkout_error_checks', [ $this, 'validate_fields' ], 10, 1 );

		$this->keys = [
			'client_id'  => trim( give_get_option( 'plaid_client_id' ) ),
			'secret_key' => trim( give_get_option( 'plaid_secret_key' ) ),
		];

	}

	/**
	 * Display a notice explaining completing a donation through the Plaid gateway modal.
	 *
	 * The notice will only appear in non-Legacy form templates
	 */
	public function ach_modal_notice() {

		if ( FormUtils::isLegacyForm() ) {
			return false;
		}

		// If viewing a non-Legacy form, output a note explaining the
		// process of completing an iDEAL gateway donation offsite
		printf(
			'
			<fieldset class="no-fields">
				<div style="display: flex; justify-content: center; margin-top: 20px;">
					<svg width="220" height="84" viewBox="0 0 220 84" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<rect width="220" height="84" fill="url(#pattern0)"/>
						<defs>
							<pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
								<use xlink:href="#image0" transform="scale(0.00454545 0.0119048)"/>
							</pattern>
							<image id="image0" width="220" height="84" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAANwAAABUCAQAAABDsJG/AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAHdElNRQfkAxADAjk5zYqoAAATRElEQVR42u2deXwURdrHf9WTQAaSTHfnQgzKgiDoKqyiggohySThVJDDk0M51o/Hvl6vrLuri3t5vO967cqqr/ciriuKhDtMLgQXXEABj8+CRhAVDcl0TxKSIcl0vX9kkkxVV3cmmQl8dj9df+STruqqPr5dTz3PU0/VAD1NkmeMskwuUQ96htifqI5TyuRfpV0CCU6KWyLdr+IZ4vJSL/KQFs44auQEvrLGRjcjFQBQizLiw0b/N85rP6XgMrNaJ1AvinC2qegIzdGPCLFdTjeFsXWmKrKerKt9Hyed19+r4DKSjbGGl3jpRdZnk0PSxJrvosLWnhrpB8QnFdd+7kCIM7hs94mLcQX1khwkRtHSvxImHv+ew7YZKV3Wq4KP+LDFX+fAiBWcyzNa8lIvuRJJ3Wprf2tefW37gXIFNkWBrT21YhfWGb7AXlAHSg/AqUvoVORA7mFjewyvrgOAMh4bkdyDJo7SLdIWw9fWipOiBJeZ1XKsJ3pmRNrpKqyp7zG2zv63XPu9A8fWGos8aCmIEhvFAfIUptNZJs1wbGijMlmA7ShdgP/D11HeVQJmO2i60eOU1zGvi/OPYXukLaZOou+hb9cC0MgNfBlhA07usj8arVn1NQ6e6MAR5VucYXFeDcrpDro9sIcvkK8hbyEhOmyd+mrTFV0ZGHSW/q6DJypwyoXYJzzlt8a7+j5rbU+5Aa/DZXmFr43cQJW4SD5bKsIkmi+09Z7V7nDwRDfGFVl8+4VSlZ2Srq2ii2BYYsuzwgboR/wvNN9qMfJNdOBECY4WWJxzGd2UYTsm6a+RJUK0X/NCkk/JmYml+LGw6LyMAQ6eKMBlu8l4y7MuD63Ndts1Ezos9Dz+I3DYrlZmVmK5BTaAtOY6eKIA1zjBzk9C8xrWDrYslydIa4W1r1VftJ7MSc5sKcV5NvfmCMtowBmsoKxHiOsABYG30UfURJqXbLJS7+nNytNizTFjQGIlzre9tzwHTxTgSCGDaTW9xaRwTFPfNCv+aV6jGP1srnCH/L/mzPQzWssxgstsxrPM8TnqIAdQF+AyBrBjDS3RX6eLeXT0GplDpxYYxXB3YXHcw6PLzAr5zNjoXNcDaGGscGeU6wpcSyEj0IyWUkB/BT/jdUUyW3mps5eqhXStCVsVfdGE7l75oYjeNrCl0jS2ncQsfW1NPRgDX3KEZVfgOEG5t+E4AGjP0rtNNea3KxxqIX3PhO0IzdeXYoUJ3cPKL9r7dmgrzjX3Nm09AKCcVYkcQFapzeNB3M8y6sWrwbK2f4K7+tWbDPOfJJ0ZXC/Edpjm6oeB4Cb3AIzhyvLdTcEdGQNay0y9rZnO0Yvb/u0nMd5ST9+VJzUHkiglAIA8CoyxS7d2/u9/Qk2lv+b6z2JVpVNMBsAXJFdrcz5T7TalP27iyh+V+4bmYZhJSF6jb2w/SNrRdDLSae3Kw5cOJMse514Ab0TeCf3OSGOgqcLdB7xxPpLXL8khkhcRv0WDxe5huIA9heR2RIZ1apJztA0RVkiLu5AJRmoIvuNAshzjCGvDlfNeEO2XeLwLzfEQyeXC7kLaPPy9i6s3Y7a2jmupjBvliAPJAly2m15hJSg70P2cs7FM2Gq/NWWHtHlYb3Ptk5jFYwMMRj0hWWkjHEgWY1xjDjtaubaKXCDanXISWST0jhx05ZpD89p6lGeOvo54hWVBMsO/xZyt71SaIpUeIw+2AXzKZtOoCRgkQE/Aj8/xiau85pionvwiYaxEktO9QF3lEcztOAhol7EWKJs8irSbyfhQuz6ipV/iFkGlRtpENHxFP5N2+3eaZ18SAKOAkUZHLSIdqb5UPouYZxCOJVhhA3A4OPDqps0wu6+byAx/ibgf0h2RqEmeXV8HkA1BCHzY/JwBhEJqJXmk1meSEgPYeqHE7mAb2K/p1siQKnm63bSv4ZLYe2Q+EaoSYRA/Cf+lUKvpu8bjbLS4xNtwtMTyG5tKcgTZA4wpdo/YlI4zhXdlOcNHWFsuJ8Y1By6aZ2yVX+pilr6bqXEOGwlHFvemWKSZuFX6l/pkpK9YSjuTdfVKFuCUyRA7mQl9QbW8beUslIl6BJLoWjk3GnBIky+M/dHJLfKbNrP03W9vKZdRJJ/dy4NaIr1L/aDzKlKIi+wyRgpHhKthHRRE6HPKdcJaZ6MCQy1quUmxcoWoIGkfO15IcfFYktnKvfF6h+p5uNyk5C3sfYWEXkxKU9LDF2QFJUCWKz8XCMm3xFM6HdbgSvVac28jZfiRTa1kbEy71PxlNb3BhrzTeLmaH0wfGKeWlgg+jMXx7NGWaWjCmrYOJEE3FT4i38n0mxl419TbWkwjyevKNKbWYGxDFyvnkGps9lzE5PRR3sYM7qwJcXolycZP49HM4CQqCmLMVgpPiR1wpfoAAEjanXjT9PU8rXQ8ojyT/N3U2z4zRmA7l9cHq9UOr6bnR6TCtByrETeSQ3y3lEqUCxhsV5vu5hl+UtcmlRtjjDHGGDKJ3oLX+c+LXhWPNxeYafL/xFdBubPtGehEuhBvIGgSmPfLgwEJIW0+fcek862QFwLhqEleUf40MS9QRaZiF5ffl67x5LVhkwTY6DRtFfLBL4FMI760kR3YTK+W/Mb/UDdGMj2wJ7AnsMe/RX9FW2CMRYApHqV4ekdQAgCdnpkVl9H4i7Zn0Cv117Sb6Ah2xgSAm9zf5vJq1W8A78GQyIvKDeos8jcztpa86h8Afx2dhL18k1KxcqVniFSBs7iSE3SqXg74jxr5OMoru0Zp6jD0UVYLsD3s/3UMfWMvfYJtTjoj1teaOoyJhYmUBYktC+IvGfUj2mRSymVen+1us5GatTnYYlI4XqNvmrB90pLXUB1uUm8txH6uvD82SpVCbBXhl/lVKA+8wX6Gq1QpxnTTN7zcvzxGj96HnBcuM9YX6VrMaOHPRdqjdFGveFZPYiEaWHW9YVK7cXuy/0zWvQsgwYTt84SCdmwAUF/b4sWn3DkpyDYJyel6Zedh3ReuXPBuqEHmcFy6XH841mc2uGgY2hizNcX2qhdohDOLDJcn9IY+4v8GKzlJNLbDK/FNU+JVeN+2/oGWHHbNKdBw3FVAD9rWaqCTdU5K1xxEEbpY0kEfih0bQDgNVaqO0YK7mkSOY0e0/awbvbc8KPQtLuPSCHfSDydcU7HTsu7+lvy2gAYOwjEpH1XW2DBF32bO1g7QQtjMbdMH9d/G7mtQHuAmc3XtuxhfIAtmHUDZ+Y9ZHqVX3Ca7OTfzWYwHr6ZemUS3kksENfe1ehtqrDqyOpFWCk3tE3S6btGL9Y/kfFIKxaK3/a6HovF85dG2roGBuAzpXLulaI3l9cmDwTjZSTEQ+Ej5NsIb6yY34U/xB3e8QfEzTyNzDlwtQF4S1vyV3Wo1/1Fym7Dg1naVRIzOEC9f/H3PexsZjmVYhmVYgqk8NgCrYlR1FjEO7zp/JQCKDadCWHKOklQOnDoXfxZWW5l2iZ2KbA7JAwD8wWPn8uor3S3Mn9o74obs1tfE1EACvZk53oRmAOAmiy+0e1MxJNYJUseAU66nqyymPzzGZnm0uMX04a4K4dQNMEjaqmZbOY6UtZgmLBotbUhPifuDnzCWxrajgzyFe8owMHcpmhhxvaRXwMnMR1gbAU65AX+18QqqpEQVxPqnnxsqh7XzdqhRKloule0OrLVajwdgXGj9wH5xfewWeqP+UYw9lhWCraFwbNp3jWAN5OsykuNNTc1mF3/STnDKjbbrSgEgg/rSz+0WNgBkeKsvOYPH1rAW9i7ZCcHibHfcnvswcvW1Mb86dsJ4e52f73vtlmzo2rj3N24CjH4aFozKTXjVhO1jpHPG9IBQqSenc6li2ohQmWnVeB0ItzHN+YlbU/M6HzPb3VAsiEMx2Jlumn/iHcyMeb+vFuzE89rq2PcNM24m7PtJCuuvAEgm5Y2Gl+Jsx/FRKdsSAEC5Dq+YsH3UWkBUVwXXn86Uyj3hvfLSzw2VmrAFyCTqwmZu2dUoV2lqfhs6C2y/IF/SVdw9TFbWaN1ERw+SNeHPJ4CAdKj24zht9SaZAqXGYmzHVfmzxyoXaAfiKCiLTKuFKxMAeT5e5rGRPaGCeg21agGtQAancPjUHP83aSNDZeBHr4BUVLsL8MyQ1nNxzqNdG9ILa+oH9mssJvkmcXqv/wlATaYvcr6+yfJK/fru2F7Sp/6f94ZmoBbSboUmkEW4K17XVsbTVdx72a4fkeT5xIxtd6ggoAGA/zNaAD/X0hCUefIMMzZdKqzdBQCBUlwj2LpmQ3JGcB01Y7vH/wQA+F/G7YLVQa+ekpnlrlI3NUU6b3BSHK7q8oxRXkApVC7/MSCB/MGMzSgIdJh7+j5PoeRjlVE6TCo1G4hSUW2HL17bJM8lqzkn9fjEKposwPZkR62/yH3Jk9wJNyqN2tLeJyMVq/yntsAfdqBnDGid3t0uGrimx+b+M2odANBknClc6btP2wAkYBuuZ6D8kxayW6AF9qiTaUkXO+HpUmHtP5mMYvUGyq9g5W+D0nu0p5haTyl98BiHdsgp6VM/5kcqqX+H5r8Qid1ub3FPwdFhtsWt0lJQIIGUUwZcwowa3eTS2qlMxSb0t2xMMwq13aZaq5U+tiYGpXfrT5uaelx2k+XMaSWnWUwS8IrJXuoXnJbLPOvE1HPqvuiFu7m7Ta4ltJazb5aeB4EHXXtfmYTNFuh0aZIZGwBoq+RE8rJFQCuld+nPCEsquRNPMzhPLs5hMoI0X7Qto1KByIBh4lqEB+J+M49pYZekVPcFu7OPYbEKVNtOZgtVa83w1n5oKUFfw21CRxOl/2WBjVs7RH/Q951ecBKnmNAN4t00+cgdenMPBKxdaqTztQ6dWTJFDltGMdJvuQn0tvR5ymd2V0t4gx4SZO/ULVcEcMuafad3z9iUNMzkst4Qn5n4NjtnRrLkqXG7DQNv0/P1v0Z8ToDBhiyMUYXbXysXoFQYlnb5ifesVd+M5NaNZLigYJzyF3F8Rko6ZSItRYu+TmVKXMDFlGqd62fZdPx7fho6LlM8rdhFHg6N0Obqh9mBF+ogym6DNk3bYMJ2IXycIR6Z1muzwlMcTEpPCW3ElZa1/qT9TKBHX0v/FsnNlV1jO2utjiMRI69Rre2P7n3Io13pXWh3H/rrAHmUi3nuVn9gr1WN9OGUCZMKhcJBG4lpzHKZVj2w27pWZ21Sk3D8+HFxVCkBAOVQ5PBL/6jfxz3kKOKD/WO+p83lw0+z+jevt9/WiTzlN83IyS+RSL/cfm0UnCQaeQFwm1SQ3G5jA2bwW9d0jQ2gd5l3HeJW4JU4iOzAsaPc6FQ1UqSQUhO2Gjxuck7Nity6Jj2lucSEjZoCO0HulR+MPE4biUGcauIka3AtZQwGScqJwOYzqSTHka8ts9u6Jqt/aJ1pIRLFHf4CwdY1v1EirJ0QO7kaTHrfQSROLgBoPuGeG6l6kOrgJltsB2y3rkkNlZhUEoo7tBV2W9e0/et+MHJFNy0//rKDyAYc4B6JyJVqycEVgOciyRIbADT9ox/hxSG5yJ2G+zGOt0LIT7Xnw51oo3so+DWmXnd1cDeAvu4VkUEx5PngBw4iW3D9+kXsIQBkpDzfZ5i01Q4bYLF1zaUYJOhtL3QeibauwRT398E9nhzW8qH/HfzBQWQLLqFaui/CICYGJU/zs0CkGvnaJ2xesCzJQ8bZ2/xkaQS2dnQXcNseEkxxV5GxjIj9Xr/fAWRpSnWY2HvxE9sTq5Hn/1RUoPwZt9lgW+IXjVN9lDXgd2sIkVoauZpmpTbPAWRvDgCg5fbYaL4QG0C1O/CclaGG2/1i9aJZmwnedeRisJ3+6Zx/D3DEBhz9Abm8kGTQ3S50vBr0Fu05y1rN7jn8BA7bauJWB08U4FyVVkE5pJp4/bYzAEqKcNaWENu4i+8a3VOwzbJ4P7+ky0lCcDX1dK/V2GbT2wDIMi0hlwrHzxXqEnt0ZDq1mstzBGU0WiUAuIcKPfmNJCPJnXisuUncgEdx+XCJpeozxX0waIM9RTaaMVk0wUMebqpy8EShVQJqId1iox1+RH2Sr7aSnQOQZZQQ+9UpIXKjn19Piaz+reNsf88q2F/9psnBExW4gf2a/F3+FlwDKsg6bPIfjRIbALTQ2e17LiPBM0ryUi+ZYLtTEbBFm+TAiRIcoGzD+CjrHcAWlON3JtsvRG6lC0wi9yRmGtVSPvJxJaJahUPu8//RgRM9uGV4NKbWQpivrVJTaQkui/G+Loxn7P1/PDhAzUYRLYIXSo+wzdPebBOhko9e3GP4u8lW/0POz0p3C1y7rhn+JfCJ3dieM4SF2spOpYWU4qJu3ksVfMQX8gWcXxroMbh2ZT0tMY96MQXZUWBboDHek+SMxPIufq2qPX2P94mPbta+dnDECVyHrTaETMc0Mt5S5zRhA4DkzMQKjLRptp7uIj7DF9jriMVeAtduLpy83PDiKhMMIba2EZOKdohtojuIDzu0nbHtOuKA62YK/5Z3UXg5eQjzNct1KeqgiK1rQuRj6pN8KdsPB50Xf/pS3zSv8j/KPnVOF5iHKkeV/eqTyrRe2ALDSU76d0v/Dwr9mdCvg94zAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDIwLTAzLTE2VDAzOjAyOjU3KzAwOjAwT4XO5QAAACV0RVh0ZGF0ZTptb2RpZnkAMjAyMC0wMy0xNlQwMzowMjo1NyswMDowMD7YdlkAAAAASUVORK5CYII="/>
						</defs>
					</svg>
				</div>
				<p style="text-align: center;"><b>%1$s</b></p>
				<p style="text-align: center;">
					<b>%2$s</b> %3$s
				</p>
			</fieldset>
			',
			__( 'Make your donation quickly and securely directly through your bank account', 'give-stripe' ),
			__( 'How it works:', 'give-stripe' ),
			__( 'A window will open after you click the Donate Now button where you can securely make your donation through your bank account. You will then be brought back to this page to view your receipt.',
				'give-stripe' )
		);

		return true;
	}

	/**
	 * ACH Scripts.
	 *
	 * Loads scripts from Plaid.
	 */
	public function load_scripts() {

		Give_Scripts::register_script( 'give-plaid-checkout-js', give_stripe_ach_get_plaid_checkout_url(), array( 'jquery' ), null );
		wp_enqueue_script( 'give-plaid-checkout-js' );

		Give_Scripts::register_script( 'give-stripe-ach-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-ach.js', array( 'jquery' ), GIVE_STRIPE_VERSION );
		wp_enqueue_script( 'give-stripe-ach-js' );

		wp_localize_script(
			'give-stripe-ach-js',
			'give_stripe_ach_vars',
			array(
				'sitename'          => get_bloginfo( 'name' ),
				'plaid_endpoint'    => give_stripe_ach_get_api_endpoint(),
				'plaid_api_version' => give_stripe_ach_get_current_api_version(),
			)
		);
	}

	/**
	 * Process ACH Payments
	 *
	 * @since 2.2.14 Increase Stripe Plaid API request timeout to 15 seconds
	 *
	 * @param array $donation_data List of donation data.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function process_payment( $donation_data ) {

		$posted = $donation_data['post_data'];

		// Bailout, if not Stripe ACH as the gateway processed.
		if ( $this->id !== $posted['give-gateway'] ) {
			return false;
		}

		// Sanity check: must have Plaid token.
		if ( ! isset( $posted['give_stripe_ach_token'] ) || empty( $posted['give_stripe_ach_token'] ) ) {

			give_record_gateway_error( esc_html__( 'Missing Stripe Token', 'give-stripe' ),
				esc_html__( 'The Stripe ACH gateway failed to generate the Plaid token.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		} elseif ( ! isset( $posted['give_stripe_ach_account_id'] ) || empty( $posted['give_stripe_ach_account_id'] ) ) {

			give_record_gateway_error( esc_html__( 'Missing Stripe Token', 'give-stripe' ),
				esc_html__( 'The Stripe ACH gateway failed to generate the Plaid account ID.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		$request = wp_remote_post(
			give_stripe_ach_get_endpoint_url( 'exchange' ),
			array(
				'timeout' => 15,
				'body'    => wp_json_encode(
					array(
						'client_id'    => $this->keys['client_id'],
						'secret'       => $this->keys['secret_key'],
						'public_token' => $posted['give_stripe_ach_token'],
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json;charset=UTF-8',
				),
			)
		);

		// Error check.
		if ( is_wp_error( $request ) ) {

			give_record_gateway_error(
				esc_html__( 'Missing Stripe Token', 'give-stripe' ),
				sprintf(
				/* translators: %s Error Message */
					__( 'The Stripe ACH gateway failed to make the call to the Plaid server to get the Stripe bank account token along with the Plaid access token that can be used for other Plaid API requests. Details: %s',
						'give-stripe' ),
					$request->get_error_message()
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was a problem communicating with the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		// Decode response.
		$exchange_response = json_decode( wp_remote_retrieve_body( $request ) );

		// Is there an error returned from the API?
		if ( isset( $exchange_response->error_code ) ) {

			give_record_gateway_error(
				esc_html__( 'Plaid API Error', 'give-stripe' ),
				sprintf(
				/* translators: %s Error Message */
					__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
					"{$exchange_response->error_code} (error code) - {$exchange_response->error_type} (error type) - {$exchange_response->error_message}"
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		$request = wp_remote_post( give_stripe_ach_get_endpoint_url( 'bank_account' ), array(
			'timeout' => 15,
			'body'    => wp_json_encode( array(
				'client_id'    => $this->keys['client_id'],
				'secret'       => $this->keys['secret_key'],
				'access_token' => $exchange_response->access_token,
				'account_id'   => $posted['give_stripe_ach_account_id'],
			) ),
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
			),
		) );

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( empty( $response ) ) {
			give_record_gateway_error(
				esc_html__( 'Plaid API Response Error', 'give-stripe' ),
				sprintf(
				/* translators: %s Error Message */
					__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
					print_r([
						'bank_request' => $request,
						'exchange_response' => $exchange_response,
						'posted' => $posted
					], true)
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		// Is there an error returned from the API?
		if ( isset( $response->error_code ) ) {

			give_record_gateway_error(
				esc_html__( 'Plaid API Error', 'give-stripe' ),
				sprintf(
				/* translators: %s Error Message */
					__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
					"{$response->error_code} (error code) - {$response->error_type} (error type) - {$response->error_message}"
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		// Get Donor Email.
		$donor_email = ! empty( $posted['give_email'] ) ? $posted['give_email'] : 0;

		// Get the Stripe customer.
		$give_stripe_customer = new Give_Stripe_Customer( $donor_email );
		$customer             = $give_stripe_customer->customer_data;

		// Check if the bank ID is present for customer in sources.
		$match = $this->check_repeat_donor( $response, $donation_data, $customer );

		// If match, donor is charged.
		if ( $match ) {
			return true;
		}

		// Source doesn't exist for customer, create it, then charge it.
		try {

			// Update Stripe customer with this payment source and charge it.
			$bank_obj = $customer->sources->create( array(
				'source' => $response->stripe_bank_account_token,
			) );

			// Set bank object to array.
			$bank_obj = $bank_obj->__toArray( true );

			$bank_id = isset( $bank_obj['id'] ) ? $bank_obj['id'] : false;

			// Charge the customer.
			$this->charge_ach( $donation_data, $bank_id, $customer->id );

		} catch ( \Stripe\Error\Base $e ) {

			Give_Stripe_Logger::log_error( $e, $this->id );

		} catch ( Exception $e ) {

			give_record_gateway_error(
				esc_html__( 'Stripe Error', 'give-stripe' ),
				sprintf(
				/* translators: %s Exception Message Body */
					esc_html__( 'The Stripe Gateway returned an error while checking if a Stripe source exists. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		return false;

	}

	/**
	 * Get the Bank ID from Stripe.
	 *
	 * @param $response
	 * @param $donation_data
	 * @param $customer
	 *
	 * @return bool
	 * @since  1.4
	 * @access public
	 *
	 */
	public function check_repeat_donor( $response, $donation_data, $customer ) {

		$bank_id     = false;
		$fingerprint = false;

		try {

			$token_args = array(
				'expand' => 'id',
			);

			$token_response = $this->get_token_details( $response->stripe_bank_account_token, $token_args );
			$token_response = $token_response->__toArray( true ); // @see http://stackoverflow.com/a/27364648/684352

			$bank_id     = isset( $token_response['bank_account']['id'] ) ? $token_response['bank_account']['id'] : false;
			$fingerprint = isset( $token_response['bank_account']['fingerprint'] ) ? $token_response['bank_account']['fingerprint'] : false;

			// Need a bank ID to continue.
			if ( ! $bank_id ) {

				give_set_error( 'request_error',
					esc_html__( 'There was a problem identifying your bank account with the payment gateway. Please try you donation again.', 'give-stripe' ) );
				give_send_back_to_checkout( '?payment-mode=stripe_ach' );
				give_record_gateway_error( esc_html__( 'Stripe Error', 'give-stripe' ),
					esc_html__( 'The Stripe Gateway returned an error while checking if a Stripe source exists.', 'give-stripe' ) );

				return false;
			}
		} catch ( \Stripe\Error\Base $e ) {

			Give_Stripe_Logger::log_error( $e, $this->id );

		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error( esc_html__( 'Stripe Error', 'give-stripe' ),
				sprintf( esc_html__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ), $e->getMessage() ) );
			give_set_error( 'stripe_error', esc_html__( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		$source_args = array(
			'limit'  => 100,
			'object' => 'bank_account',
		);

		$customer_bank_sources = $customer->sources->all( $source_args )->__toArray( true );
		$match                 = false;

		// Loop through sources and check for match with the new bank ID.
		foreach ( $customer_bank_sources['data'] as $array_key => $bank ) {

			// Bank ID & fingerprint are both viable matching properties.
			if ( $bank['id'] === $bank_id ) {
				$match = true;
			}

			if ( $bank['fingerprint'] === $fingerprint ) {
				$match   = true;
				$bank_id = $bank['id'];
				break;
			}
		}

		// If this bank has already been added to the Stripe customer, charge it now.
		if ( $match ) {

			$this->charge_ach( $donation_data, $bank_id, $customer->id );

			return true; // bounce, the charge has taken place.

		} else {

			// No match found.
			return false;

		}

	}


	/**
	 * Charge ACH.
	 *
	 * @see: http://stackoverflow.com/a/34416413/684352 Useful information on creating a charge using a Stripe bank
	 *       token.
	 *
	 * @param array  $donation_data Donation Data.
	 * @param string $bank_id       Bank  Account ID.
	 * @param string $customer_id   Customer ID.
	 */
	public function charge_ach( $donation_data, $bank_id, $customer_id ) {

		$form_id     = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
		$price_id    = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
		$description = give_payment_gateway_donation_summary( $donation_data, false );

		// Setup the payment details.
		$payment_data = array(
			'price'           => $donation_data['price'],
			'give_form_title' => $donation_data['post_data']['give-form-title'],
			'give_form_id'    => $form_id,
			'give_price_id'   => $price_id,
			'date'            => $donation_data['date'],
			'user_email'      => $donation_data['user_email'],
			'purchase_key'    => $donation_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $donation_data['user_info'],
			'status'          => 'pending',
			'gateway'         => 'stripe_ach',
		);

		// Record the pending payment in Give.
		$donation_id = give_insert_payment( $payment_data );

		// Prepare Charge Arguments.
		$charge_args = array(
			'amount'      => parent::format_amount( $donation_data['price'] ),
			'currency'    => give_get_currency(),
			'customer'    => $customer_id,
			'source'      => $bank_id,
			'description' => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
			'metadata'    => $this->prepare_metadata( $donation_id ),
		);

		$charge = $this->create_charge( $donation_id, $charge_args );

		// Verify Stripe ACH Payment.
		parent::verify_payment( $donation_id, $customer_id, $charge );

	}


	/**
	 * Ensure the form.
	 *
	 * @access      public
	 *
	 * @param $data
	 *
	 * @return      void
	 * @since       1.4
	 *
	 */
	public function validate_fields( $data ) {

		// Important that we ensure we're only validating this gateway
		if ( isset( $data['gateway'] ) && $data['gateway'] !== 'stripe_ach' ) {
			return;
		}

		// Verify Client ID is there.
		if ( empty( $this->keys['client_id'] ) ) {
			give_set_error( 'give_recurring_stripe_ach_client_id_missing', esc_html__( 'The Plaid client ID must be entered in settings.', 'give-stripe' ) );
			give_record_gateway_error( 'Stripe ACH Error', esc_html__( 'The Plaid client ID must be entered in settings.', 'give-stripe' ) );
		}

		// Verify Secret Key is there.
		if ( empty( $this->keys['secret_key'] ) ) {
			give_set_error( 'give_recurring_stripe_ach_public_missing', esc_html__( 'The Plaid secret key must be entered in settings.', 'give-stripe' ) );
			give_record_gateway_error( 'Stripe ACH Error', esc_html__( 'The Plaid secret key must be entered in settings.', 'give-stripe' ) );
		}

	}


}

new Give_Stripe_ACH();

