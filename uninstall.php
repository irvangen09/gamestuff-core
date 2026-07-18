<?php
/**
 * Uninstall handler for GameStuff Core.
 *
 * This file is run by WordPress when the plugin is deleted (not just
 * deactivated). Data cleanup logic (e.g. options, transients) will be
 * added in a later stage, once the plugin's data structure (Settings
 * Architecture) is locked in.
 *
 * @package GameStuff\Blocks
 */

// WordPress calls this file with the WP_UNINSTALL_PLUGIN constant defined.
// If it isn't defined, stop execution to prevent direct access.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Plugin Foundation stage: no plugin data needs cleanup yet.
