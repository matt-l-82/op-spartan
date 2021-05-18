<?php

namespace GiveStripe\Framework;

/**
 * Class Log
 *
 * @package GiveMailChimp\Infrastructure
 * @since 2.3.0
 */
class Log extends \Give\Log\Log {
	/**
	 * @inheritDoc
	 * @since 2.3.0
	 *
	 * @param  string  $type
	 * @param  array  $args
	 */
	public static function __callStatic( $type, $args ) {
		$args[1]['source'] = 'Give Stripe';

		parent::__callStatic( $type, $args );
	}

}
