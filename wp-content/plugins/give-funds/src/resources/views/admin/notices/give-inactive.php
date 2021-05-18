<?php defined( 'ABSPATH' ) or exit; ?>

<div class="notice notice-error">
	<p>
		<strong><?php _e( 'Activation Error:', 'give-funds' ); ?></strong>
		<?php _e( 'You must have', 'give-funds' ); ?> <a href="https://givewp.com" target="_blank">Give</a>
		<?php printf( __( 'plugin installed and activated for the %s add-on to activate', 'give-funds' ), GIVE_FUNDS_ADDON_NAME ); ?>
	</p>
</div>
