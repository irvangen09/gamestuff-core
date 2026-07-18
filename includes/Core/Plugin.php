<?php
/**
 * Main orchestrator for GameStuff Core.
 *
 * @package GameStuff\Core
 */

namespace GameStuff\Core;

use GameStuff\Blocks\Loader;
use GameStuff\Blocks\Registry;
use GameStuff\Settings\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main orchestrator for GameStuff Core.
 *
 * Runs the plugin initialization sequence per the Bootstrap Flow and Lifecycle
 *
 * This class contains NO business logic; it only orchestrates other
 * components (Forbidden Dependencies:
 * "Bootstrap contains business logic").
 */
final class Plugin {

	/**
	 * Single Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Retrieves the single Plugin instance.
	 *
	 * @return Plugin
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
	 * Runs the plugin initialization sequence.
	 *
	 * Environment Check -> Register Services -> Register Settings
	 * -> Register Blocks -> Plugin Ready.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! Environment::is_compatible() ) {
			add_action( 'admin_notices', array( $this, 'render_environment_notice' ) );
			return;
		}

		$this->register_services();
		$this->register_settings();
		$this->register_blocks();

		/**
		 * Fires after GameStuff Core has finished initializing.
		 *
		 * @since 0.1.0
		 */
		do_action( 'gamestuff_core_ready' );
	}

	/**
	 * Placeholder for Shared Services registration.
	 *
	 * A Service Container implementation is not yet in scope. See
	 * includes/Services/.
	 *
	 * @return void
	 */
	private function register_services() {
		// No Services registered yet.
	}

	/**
	 * Registers the Settings page (skeleton).
	 *
	 * The menu and tab navigation are registered, but there are no
	 * functional settings fields yet (skeleton-only scope).
	 *
	 * @return void
	 */
	private function register_settings() {
		( new Menu() )->register();
	}

	/**
	 * Connects the bootstrap to the Block Registry.
	 *
	 * Actual block registration happens on the 'init' hook (not directly here),
	 * per the Lifecycle (Block Registration happens after Service Initialization).
	 *
	 * @return void
	 */
	private function register_blocks() {
		Loader::load();

		add_action( 'init', array( Registry::get_instance(), 'register_active_blocks' ) );
	}

	/**
	 * Renders an admin notice when the environment doesn't meet the
	 * minimum requirements.
	 *
	 * @return void
	 */
	public function render_environment_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'GameStuff Core requires PHP 8.0+ and WordPress 6.9+ to run.', 'gamestuff-core' )
		);
	}
}
