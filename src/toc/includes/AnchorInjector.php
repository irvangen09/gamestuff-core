<?php
/**
 * Heading Anchor Injector for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 */

namespace GameStuff\Blocks\Toc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Injects the id attribute into the actual heading tags on the frontend.
 *
 * GameStuff TOC produces links with href="#heading-slug", but the
 * actual heading (the <h2>-<h6> tag in the article body) doesn't
 * automatically have that id. Without this class, clicking a TOC link
 * won't jump to the intended section because the browser can't find a
 * matching element.
 *
 * This class re-reads the settings (Global Setting + the Block
 * Override of the actual TOC block on the post, if any) so the
 * injected id always matches the id used by HeadingParser when it
 * built the TOC list (see HeadingParser::inject_ids()).
 */
final class AnchorInjector {

	/**
	 * Registers the the_content filter.
	 *
	 * Priority 30 is used so this runs after do_blocks() (priority 9,
	 * where the TOC block is rendered) and after AutoInsert
	 * (priority 20), so the content being processed is already in its
	 * final form (both the actual headings and the TOC markup are
	 * present in $content).
	 *
	 * Only relevant on the frontend.
	 *
	 * @return void
	 */
	public static function register() {
		if ( is_admin() ) {
			return;
		}

		add_filter( 'the_content', array( __CLASS__, 'maybe_inject' ), 30 );
	}

	/**
	 * Injects ids into the actual headings when the page contains GameStuff TOC.
	 *
	 * @param string $content Already-filtered post content.
	 * @return string
	 */
	public static function maybe_inject( $content ) {
		/*
		 * This method used to also require in_the_loop() &&
		 * is_main_query(). Both conditions assume the_content is
		 * always called from WordPress's classic main Loop
		 * (have_posts()/the_post()/the_content()). On pages where the
		 * post content is rendered through a page builder or theme
		 * builder (e.g. Elementor Theme Builder, a custom single
		 * template calling apply_filters('the_content', ...)
		 * manually), both conditions are often false even though the
		 * page is still is_singular() — so the id was NEVER injected
		 * and clicking a TOC link failed to jump to its heading.
		 *
		 * is_singular() combined with the strpos() check for
		 * 'gamestuff-toc' below is enough to prevent this from
		 * running on archive/listing pages or on an excerpt that
		 * contains no GameStuff TOC markup at all.
		 */
		if ( ! is_singular() ) {
			return $content;
		}

		// Skip early when there's no GameStuff TOC markup on the page
		// (avoid unnecessary DOM parsing on every post).
		if ( false === strpos( $content, 'gamestuff-toc' ) ) {
			return $content;
		}

		$post = get_post();

		$attrs    = $post instanceof \WP_Post ? self::find_toc_block_attrs( $post->post_content ) : null;
		$settings = Settings::resolve( is_array( $attrs ) ? $attrs : array() );

		return HeadingParser::inject_ids( $content, $settings );
	}

	/**
	 * Finds the attributes of the first gamestuff/toc block in the post content.
	 *
	 * Used so the injected id follows the actual Block Override in
	 * use (e.g. a by_level or ignore_heading specific to that block),
	 * not just the Global Setting.
	 *
	 * @param string $content Raw post_content.
	 * @return array<string,mixed>|null Null when the block isn't found.
	 */
	private static function find_toc_block_attrs( $content ) {
		if ( ! function_exists( 'parse_blocks' ) ) {
			return null;
		}

		return self::search_blocks( parse_blocks( $content ) );
	}

	/**
	 * Traverses the block list (including innerBlocks) looking for gamestuff/toc.
	 *
	 * @param array<int,array<string,mixed>> $blocks Block list from parse_blocks().
	 * @return array<string,mixed>|null
	 */
	private static function search_blocks( array $blocks ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && 'gamestuff/toc' === $block['blockName'] ) {
				return isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$found = self::search_blocks( $block['innerBlocks'] );

				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}
}
