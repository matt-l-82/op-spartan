<?php

namespace GiveAnnualReceipts\Infrastructure;

/**
 * Helper class responsible for showing add-on notices.
 *
 * @package     GiveAnnualReceipts\Infrastructure
 * @copyright   Copyright (c) 2021, GiveWP
 */
class Notices {

	/**
	 * Add notice
	 *
	 * @param string $type
	 * @param string $description
	 * @param bool $show
	 */
	public static function add( $type, $description, $show = true ) {
		give()->notices->register_notice(
			[
				'id'          => sprintf( 'give-recurring-notice-%s', $type ),
				'type'        => $type,
				'description' => $description,
				'show'        => $show,
			]
		);
	}
	/**
	 * GiveWP min required version notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function giveVersionError() {
		self::add( 'error', View::load( 'admin/notices/give-version-error' ) );
	}

	/**
	 * GiveWP inactive notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function giveInactive() {
		echo View::load( 'admin/notices/give-inactive' );
	}
}
