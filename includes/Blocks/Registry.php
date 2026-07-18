<?php
/**
 * Central registry for all GameStuff Core blocks.
 *
 * @package GameStuff\Blocks
 */

namespace GameStuff\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central registry for all GameStuff Core blocks.
 *
 * Block registration is centralized here, and disabled blocks are not
 * registered at all — so they never load their CSS or JavaScript.
 */
final class Registry {

	/**
	 * Single Registry instance.
	 *
	 * @var Registry|null
	 */
	private static $instance = null;

	/**
	 * Blocks added to the Registry.
	 *
	 * Format: [ slug => path_to_block_folder ].
	 *
	 * @var array<string,string>
	 */
	private $blocks = array();

	/**
	 * Retrieves the single Registry instance.
	 *
	 * @return Registry
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevent direct instantiation from outside the class.
	 */
	private function __construct() {}

	/**
	 * Prevent the instance from being cloned.
	 */
	private function __clone() {}

	/**
	 * Adds a block definition to the Registry.
	 *
	 * This method only stores the definition; actual registration with
	 * WordPress happens when register_active_blocks() is called.
	 *
	 * @param string $slug Unique block slug, e.g. 'toc'.
	 * @param string $path Absolute path to the block folder (containing block.json).
	 * @return void
	 */
	public function add( $slug, $path ) {
		$this->blocks[ $slug ] = $path;
	}

	/**
	 * Retrieves every block added to the Registry.
	 *
	 * @return array<string,string>
	 */
	public function get_all() {
		return $this->blocks;
	}

	/**
	 * Registers every ACTIVE block with WordPress.
	 *
	 * Disabled blocks are skipped entirely (continue), so they are
	 * never registered and never load any assets.
	 *
	 * @return void
	 */
	public function register_active_blocks() {
		foreach ( $this->blocks as $slug => $path ) {
			if ( ! Status::is_active( $slug ) ) {
				continue;
			}

			register_block_type( $path );
		}
	}
}
