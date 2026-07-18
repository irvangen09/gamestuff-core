<?php
/**
 * Settings Resolution Engine for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 */

namespace GameStuff\Blocks\Toc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the final settings for GameStuff TOC.
 *
 * Priority: Plugin Default -> GameStuff Core Global Setting -> Block Override.
 */
final class Settings {

	/**
	 * Option name for the GameStuff TOC Global Setting.
	 *
	 * @var string
	 */
	const GLOBAL_OPTION_KEY = 'gamestuff_core_toc_settings';

	/**
	 * Keys that are boolean in PHP.
	 *
	 * In block.json (JS), these keys are stored as strings ('', '1',
	 * '0') so an empty string can represent "not overridden" — a
	 * native boolean can't distinguish an explicit false from
	 * "not set".
	 *
	 * @var array<int,string>
	 */
	const BOOLEAN_KEYS = array(
		'hierarchical_view',
		'default_hidden',
		'smooth_scroll',
		'highlight_active',
		'collapse_subheading',
		'sticky_desktop',
		'sticky_tablet',
		'sticky_mobile',
		'display_desktop',
		'display_tablet',
		'display_mobile',
	);

	/**
	 * Keys that are numeric in PHP (stored as strings in JS for the
	 * same reason as BOOLEAN_KEYS).
	 *
	 * @var array<int,string>
	 */
	const NUMERIC_KEYS = array(
		'minimal_heading_count',
		'scroll_offset',
	);

	/**
	 * Plugin default values.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			'minimal_heading_count' => 2,
			'hierarchical_view'     => true,
			'title'                 => __( 'Table of Contents', 'gamestuff-core' ),
			'label_show'            => __( 'Show', 'gamestuff-core' ),
			'label_hide'            => __( 'Hide', 'gamestuff-core' ),
			'default_hidden'        => false,
			'width'                 => '',
			'float'                 => 'none',
			'color_scheme'          => 'auto',
			'auto_insert'           => false,
			'by_level'              => array( 'h2', 'h3', 'h4', 'h5', 'h6' ),
			'hash_format'           => 'slug',
			'numeration'            => 'decimal',
			'smooth_scroll'         => true,
			'scroll_offset'         => 0,
			'highlight_active'      => true,
			'collapse_subheading'   => false,
			'sticky_desktop'        => false,
			'sticky_tablet'         => false,
			'sticky_mobile'         => false,
			'ignore_heading'        => array(),
			'display_desktop'       => true,
			'display_tablet'        => true,
			'display_mobile'        => true,
		);
	}

	/**
	 * Retrieves the GameStuff Core Global Setting (stored in wp_options).
	 *
	 * @return array<string,mixed>
	 */
	private static function global_settings() {
		$stored = get_option( self::GLOBAL_OPTION_KEY, array() );

		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Converts camelCase keys (block attributes from JS) to snake_case.
	 *
	 * @param array<string,mixed> $attributes Raw block attributes from JS.
	 * @return array<string,mixed>
	 */
	private static function normalize_keys( array $attributes ) {
		$normalized = array();

		foreach ( $attributes as $key => $value ) {
			$snake_key                = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $key ) );
			$normalized[ $snake_key ] = $value;
		}

		return $normalized;
	}

	/**
	 * Filters Block Override so only valid keys are accepted.
	 *
	 * Empty values ('', null, empty array) are treated as "not
	 * overridden" and skipped, so they still fall back to the
	 * Global/Default Setting.
	 *
	 * @param array<string,mixed> $overrides Overrides from block attributes.
	 * @return array<string,mixed>
	 */
	private static function filter_overrides( array $overrides ) {
		$allowed  = array_keys( self::defaults() );
		$filtered = array_intersect_key( $overrides, array_flip( $allowed ) );

		foreach ( $filtered as $key => $value ) {
			if ( '' === $value || null === $value || array() === $value ) {
				unset( $filtered[ $key ] );
				continue;
			}

			if ( in_array( $key, self::BOOLEAN_KEYS, true ) ) {
				$filtered[ $key ] = ( '1' === $value || true === $value );
			} elseif ( in_array( $key, self::NUMERIC_KEYS, true ) ) {
				$filtered[ $key ] = (int) $value;
			}
		}

		return $filtered;
	}

	/**
	 * Resolves the final settings.
	 *
	 * @param array<string,mixed> $block_overrides Overrides from block attributes (per post).
	 * @return array<string,mixed>
	 */
	public static function resolve( array $block_overrides = array() ) {
		$settings = self::defaults();
		$settings = array_merge( $settings, self::global_settings() );
		$settings = array_merge( $settings, self::filter_overrides( self::normalize_keys( $block_overrides ) ) );

		return $settings;
	}
}
