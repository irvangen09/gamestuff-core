<?php
/**
 * Auto Insert mechanism for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 */

namespace GameStuff\Blocks\Toc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatically inserts GameStuff TOC into the content when the
 * Global Setting "Auto Insert TOC" is enabled.
 *
 * Re-renders through render_block() (instead of duplicating logic)
 * so the result is identical and still goes through the same
 * Settings Resolution Engine used by a manually placed block.
 */
final class AutoInsert {

	/**
	 * Registers the the_content filter.
	 *
	 * Only runs on the frontend; this filter isn't relevant in wp-admin.
	 *
	 * @return void
	 */
	public static function register() {
		if ( is_admin() ) {
			return;
		}

		add_filter( 'the_content', array( __CLASS__, 'maybe_insert' ), 20 );
	}

	/**
	 * Inserts the TOC at the start of the content when eligible.
	 *
	 * Runs at priority 20 (after wpautop and similar filters), so the
	 * TOC markup is only appended, not reprocessed by other content
	 * filters.
	 *
	 * @param string $content Already-filtered post content.
	 * @return string
	 */
	public static function maybe_insert( $content ) {
		/*
		 * This method used to also require in_the_loop() &&
		 * is_main_query(). Both conditions assume the_content is
		 * always called from WordPress's classic Loop, which isn't
		 * always true on pages rendered through a page builder or
		 * custom template. is_singular() alone is enough to prevent
		 * this from running on archive/listing pages.
		 */
		if ( ! is_singular() ) {
			return $content;
		}

		$settings = Settings::resolve();

		if ( empty( $settings['auto_insert'] ) ) {
			return $content;
		}

		if ( has_block( 'gamestuff/toc', $content ) ) {
			return $content;
		}

		$toc = render_block(
			array(
				'blockName'    => 'gamestuff/toc',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		if ( '' === trim( $toc ) ) {
			return $content;
		}

		return $toc . $content;
	}
}
