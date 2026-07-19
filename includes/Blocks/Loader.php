<?php
/**
 * Block loader for GameStuff Core.
 *
 * @package GameStuff\Blocks
 */

namespace GameStuff\Blocks;

use GameStuff\Blocks\Accordion\FrontendAssets as AccordionFrontendAssets;
use GameStuff\Blocks\Accordion\ProgressiveEnhancement as AccordionProgressiveEnhancement;
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
		Categories::register();

		$registry = Registry::get_instance();

		$registry->add( 'toc', GAMESTUFF_CORE_PATH . 'build/toc' );
		$registry->add( 'accordion', GAMESTUFF_CORE_PATH . 'build/accordion' );
		$registry->add( 'accordion-item', GAMESTUFF_CORE_PATH . 'build/accordion-item' );

		if ( is_admin() ) {
			TocSettingsPage::register();
		}

		TocAutoInsert::register();
		TocAnchorInjector::register();

		AccordionProgressiveEnhancement::register();
		AccordionFrontendAssets::register();
	}
}
