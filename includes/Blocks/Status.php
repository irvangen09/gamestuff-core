<?php
/**
 * Active/inactive status for each block.
 *
 * @package GameStuff\Blocks
 */

namespace GameStuff\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines whether a block is active or inactive.
 *
 * Every block can be enabled or disabled. This class only reads the
 * status — the UI to change it lives on the Settings page, so this
 * class has no dependency on any UI.
 *
 * Default: a block is considered ACTIVE when it has no stored entry
 * yet (active by default until explicitly disabled).
 */
class Status {

	/**
	 * Option name under which every block's status is stored.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'gamestuff_core_active_blocks';

	/**
	 * Checks whether a block is active.
	 *
	 * @param string $slug Unique block slug, e.g. 'toc'.
	 * @return bool
	 */
	public static function is_active( $slug ) {
		$states = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $states ) || ! array_key_exists( $slug, $states ) ) {
			return true;
		}

		return (bool) $states[ $slug ];
	}
}
