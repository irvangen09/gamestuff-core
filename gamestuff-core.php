<?php
/**
 * Plugin Name:       GameStuff Core
 * Description:       Foundation for GameStuff's Gutenberg blocks — fast, lightweight, and maintainable.
 * Version:           0.1.0
 * Requires at least: 6.9
 * Requires PHP:      8.0
 * Author:            GameStuff
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gamestuff-core
 * Domain Path:       /languages
 *
 * @package GameStuff\Core
 */

// Stop execution if this file is accessed directly, outside of WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GAMESTUFF_CORE_VERSION', '0.1.0' );
define( 'GAMESTUFF_CORE_FILE', __FILE__ );
define( 'GAMESTUFF_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'GAMESTUFF_CORE_URL', plugin_dir_url( __FILE__ ) );

// Load the Composer autoloader (PSR-4: GameStuff\ -> includes/).
$gamestuff_core_autoload = GAMESTUFF_CORE_PATH . 'vendor/autoload.php';

if ( file_exists( $gamestuff_core_autoload ) ) {
	require_once $gamestuff_core_autoload;
}

/**
 * Boots the plugin.
 *
 * Runs on the 'plugins_loaded' hook so every WordPress dependency is
 * already available before Core initialization, per the Bootstrap and Lifecycle
 *
 * This file contains NO business logic — it only loads the dependency and
 * calls the orchestrator in GameStuff\Core\Plugin.
 *
 * @return void
 */
function gamestuff_core_boot() {
	if ( ! class_exists( \GameStuff\Core\Plugin::class ) ) {
		add_action( 'admin_notices', 'gamestuff_core_render_autoload_notice' );
		return;
	}

	\GameStuff\Core\Plugin::get_instance()->boot();
}
add_action( 'plugins_loaded', 'gamestuff_core_boot' );

/**
 * Renders an admin notice when the autoloader failed to load.
 *
 * A silent failure (no indication to the user beyond the plugin
 * "doing nothing") is hard to diagnose. This notice surfaces the
 * failure immediately, along with the fix.
 *
 * @return void
 */
function gamestuff_core_render_autoload_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'GameStuff Core tidak dapat dimuat: folder vendor/ tidak ditemukan atau tidak lengkap. Jalankan "composer install" di direktori plugin, lalu aktifkan ulang.', 'gamestuff-core' )
	);
}
