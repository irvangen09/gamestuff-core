<?php
/**
 * Environment compatibility check.
 *
 * @package GameStuff\Core
 */

namespace GameStuff\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether the environment (PHP & WordPress) meets the minimum
 * requirements set in PHP 8+, WP 6.9+.
 *
 * This class is a pure environment check; it carries no other
 * business logic, per the Separation of Responsibilities principle.
 */
class Environment {

	/**
	 * Minimum supported PHP version.
	 *
	 * @var string
	 */
	const MINIMUM_PHP_VERSION = '8.0';

	/**
	 * Minimum supported WordPress version.
	 *
	 * @var string
	 */
	const MINIMUM_WP_VERSION = '6.9';

	/**
	 * Checks whether the current environment meets the minimum requirements.
	 *
	 * @return bool True when the environment is compatible.
	 */
	public static function is_compatible() {
		return self::is_php_compatible() && self::is_wp_compatible();
	}

	/**
	 * Checks the running PHP version.
	 *
	 * @return bool
	 */
	public static function is_php_compatible() {
		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Checks the running WordPress version.
	 *
	 * @return bool
	 */
	public static function is_wp_compatible() {
		global $wp_version;

		if ( ! isset( $wp_version ) ) {
			return false;
		}

		return version_compare( $wp_version, self::MINIMUM_WP_VERSION, '>=' );
	}
}
