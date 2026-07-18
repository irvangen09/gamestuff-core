<?php
/**
 * Tab list for the GameStuff Core Settings page.
 *
 * @package GameStuff\Settings
 */

namespace GameStuff\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds the list of Settings tabs as a single source of truth.
 *
 * This class only provides tab data. The implementation details of
 * each tab (fields, options, storage) are handled in their own
 * dedicated stage.
 */
final class Tabs {

	/**
	 * Retrieves every available tab.
	 *
	 * @return array<string,string> Tab slug => tab label.
	 */
	public static function get_tabs() {
		return array(
			'dashboard'   => __( 'Dashboard', 'gamestuff-core' ),
			'blocks'      => __( 'Blocks', 'gamestuff-core' ),
			'appearance'  => __( 'Appearance', 'gamestuff-core' ),
			'performance' => __( 'Performance', 'gamestuff-core' ),
			'tools'       => __( 'Tools', 'gamestuff-core' ),
			'about'       => __( 'About', 'gamestuff-core' ),
		);
	}

	/**
	 * Retrieves the default tab slug.
	 *
	 * @return string
	 */
	public static function get_default_tab() {
		return 'dashboard';
	}
}
