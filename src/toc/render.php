<?php
/**
 * Dynamic render for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 *
 * @var array<string,mixed> $attributes Block attributes (Block Override).
 * @var string              $content    Block content (empty, dynamic block).
 * @var WP_Block             $block      WP_Block instance.
 */

use GameStuff\Blocks\Toc\HeadingParser;
use GameStuff\Blocks\Toc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gamestuff_toc_render_list' ) ) {
	/**
	 * Recursively renders the heading <ol> for a hierarchical structure.
	 *
	 * <ol> is used (not <ul>) because the TOC represents a
	 * semantically meaningful section order, consistent with the
	 * numbering that helps Accessibility & SEO.
	 *
	 * Wrapped in function_exists() because render.php can be included
	 * more than once in a single request (e.g. the block is used more
	 * than once), so the function declaration must not collide.
	 *
	 * @param array<int,array<string,mixed>> $headings         Heading list (flat/nested).
	 * @param string                          $numeration_class Extra class for numbering style ('', ' gamestuff-toc__list--numeration-none', etc).
	 * @return string
	 */
	function gamestuff_toc_render_list( array $headings, $numeration_class = '' ) {
		if ( empty( $headings ) ) {
			return '';
		}

		$items = '';

		foreach ( $headings as $heading ) {
			$children = empty( $heading['children'] ) ? '' : gamestuff_toc_render_list( $heading['children'], $numeration_class );

			$items .= sprintf(
				'<li class="gamestuff-toc__item gamestuff-toc__item--level-%1$d"><a class="gamestuff-toc__link" href="#%2$s">%3$s</a>%4$s</li>',
				(int) $heading['level'],
				esc_attr( $heading['id'] ),
				esc_html( $heading['text'] ),
				$children
			);
		}

		return sprintf( '<ol class="gamestuff-toc__list%s">%s</ol>', esc_attr( $numeration_class ), $items );
	}
}

if ( ! function_exists( 'gamestuff_toc_render' ) ) {
	/**
	 * Renders the GameStuff TOC markup for a single block instance.
	 *
	 * All logic is wrapped in a function (instead of sitting directly
	 * at the top level of the file) so its variables stay in local
	 * scope, not global scope -- render.php is included directly by
	 * WordPress with no namespace/class wrapper, so top-level
	 * variables are technically treated as global variables by
	 * WordPress Coding Standards (WordPress.NamingConventions.PrefixAllGlobals).
	 *
	 * @param array<string,mixed> $attributes Block attributes (Block Override).
	 * @return void
	 */
	function gamestuff_toc_render( array $attributes ) {
		$toc_post = get_post();

		if ( ! $toc_post instanceof WP_Post ) {
			return;
		}

		$settings = Settings::resolve( $attributes );
		$headings = HeadingParser::parse( $toc_post->post_content, $settings );

		if ( empty( $headings ) ) {
			return;
		}

		$wrapper_classes = array( 'gamestuff-toc' );

		if ( 'none' !== $settings['float'] ) {
			$wrapper_classes[] = 'gamestuff-toc--float-' . sanitize_html_class( $settings['float'] );
		}

		if ( empty( $settings['display_desktop'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--hide-desktop';
		}

		if ( empty( $settings['display_tablet'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--hide-tablet';
		}

		if ( empty( $settings['display_mobile'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--hide-mobile';
		}

		if ( ! empty( $settings['sticky_desktop'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--sticky-desktop';
		}

		if ( ! empty( $settings['sticky_tablet'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--sticky-tablet';
		}

		if ( ! empty( $settings['sticky_mobile'] ) ) {
			$wrapper_classes[] = 'gamestuff-toc--sticky-mobile';
		}

		$extra_attributes = array(
			'class'                    => implode( ' ', $wrapper_classes ),
			'data-color-scheme'        => $settings['color_scheme'],
			'data-smooth-scroll'       => $settings['smooth_scroll'] ? 'true' : 'false',
			'data-scroll-offset'       => (string) (int) $settings['scroll_offset'],
			'data-highlight-active'    => $settings['highlight_active'] ? 'true' : 'false',
			'data-collapse-subheading' => $settings['collapse_subheading'] ? 'true' : 'false',
		);

		$style_parts = array();
		$width       = $settings['width'];

		/*
		 * Width MUST be a number + a recognized CSS unit, not a free
		 * string. Without this validation, esc_attr() (used by
		 * get_block_wrapper_attributes()) only escapes HTML
		 * characters -- not characters that are meaningful in CSS
		 * like a semicolon, so a value like "10px; background:url(...)"
		 * could be injected through the Width field (Global Setting
		 * or Block Override) and pass through as CSS injection.
		 */
		if ( '' !== $width && ! preg_match( '/^\d+(\.\d+)?(px|em|rem|%|vw|vh|ch)$/', $width ) ) {
			$width = '';
		}

		if ( '' !== $width ) {
			$style_parts[] = sprintf( '--gamestuff-toc-width:%s;', $width );
		}

		if ( ! empty( $settings['sticky_desktop'] ) || ! empty( $settings['sticky_tablet'] ) || ! empty( $settings['sticky_mobile'] ) ) {
			// Reuse Scroll Offset as the distance from the top of the
			// viewport while sticky -- conceptually the same "distance
			// from the top", no need for a separate setting.
			$style_parts[] = sprintf( '--gamestuff-toc-sticky-offset:%dpx;', (int) $settings['scroll_offset'] );
		}

		if ( ! empty( $style_parts ) ) {
			$extra_attributes['style'] = implode( ' ', $style_parts );
		}

		$wrapper_attributes = get_block_wrapper_attributes( $extra_attributes );

		$is_hidden = ! empty( $settings['default_hidden'] );
		$list_id   = wp_unique_id( 'gamestuff-toc-list-' );

		$numeration_class = '';

		if ( 'none' === $settings['numeration'] ) {
			$numeration_class = ' gamestuff-toc__list--numeration-none';
		} elseif ( 'roman' === $settings['numeration'] ) {
			$numeration_class = ' gamestuff-toc__list--numeration-roman';
		}

		printf(
			'<nav %1$s aria-label="%2$s"><div class="gamestuff-toc__header"><span class="gamestuff-toc__title"><svg class="gamestuff-toc__icon" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M2 3.5H14M5 8H14M5 12.5H14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="2" cy="8" r="0.9" fill="currentColor"/><circle cx="2" cy="12.5" r="0.9" fill="currentColor"/></svg><span class="gamestuff-toc__title-text">%3$s</span></span><button type="button" class="gamestuff-toc__toggle" aria-expanded="%4$s" aria-controls="%5$s"><span class="gamestuff-toc__toggle-label--show">%6$s</span><span class="gamestuff-toc__toggle-label--hide">%7$s</span></button></div><div id="%5$s" class="gamestuff-toc__content"%8$s>%9$s</div></nav>',
			$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped by get_block_wrapper_attributes().
			esc_attr( $settings['title'] ),
			esc_html( $settings['title'] ),
			$is_hidden ? 'false' : 'true',
			esc_attr( $list_id ),
			esc_html( $settings['label_show'] ),
			esc_html( $settings['label_hide'] ),
			$is_hidden ? ' hidden' : '',
			gamestuff_toc_render_list( $headings, $numeration_class ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped inside gamestuff_toc_render_list().
		);
	}
}

gamestuff_toc_render( $attributes );
