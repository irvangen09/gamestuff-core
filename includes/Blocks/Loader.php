<?php
/**
 * Block loader for GameStuff Core.
 *
 * @package GameStuff\Blocks
 */

namespace GameStuff\Blocks;

use GameStuff\Blocks\Toc\AnchorInjector as TocAnchorInjector;
use GameStuff\Blocks\Toc\AutoInsert as TocAutoInsert;
use GameStuff\Blocks\Toc\SettingsPage as TocSettingsPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every available block with the Registry, and bootstraps
 * each block's own integrations (e.g. Global Settings UI, Auto
 * Insert) when it provides them.
 */
final class Loader {

	/**
	 * Adds block definitions to the Registry and runs each block's
	 * own bootstrap.
	 *
	 * @return void
	 */
	public static function load() {
		$registry = Registry::get_instance();

		$registry->add( 'toc', GAMESTUFF_CORE_PATH . 'build/toc' );

		if ( is_admin() ) {
			TocSettingsPage::register();
		}

		TocAutoInsert::register();
		TocAnchorInjector::register();
	}
}
