<?php
/**
 * Heading Parser Engine for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 */

namespace GameStuff\Blocks\Toc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts headings from post content into a TOC structure.
 */
final class HeadingParser {

	/**
	 * CSS class that marks a heading to be ignored.
	 *
	 * @var string
	 */
	const IGNORE_CLASS = 'gamestuff-toc-ignore';

	/**
	 * Parses content into a heading list (flat or hierarchical).
	 *
	 * @param string               $content  Post content HTML.
	 * @param array<string,mixed>  $settings Settings from Settings::resolve().
	 * @return array<int,array<string,mixed>>
	 */
	public static function parse( $content, array $settings ) {
		if ( '' === trim( (string) $content ) ) {
			return array();
		}

		$allowed_levels = self::normalize_levels( $settings['by_level'] );

		if ( empty( $allowed_levels ) ) {
			/*
			 * "By Level" is invalid or every checkbox is unchecked
			 * (e.g. a misconfigured Global Setting). Rather than the
			 * TOC disappearing without a trace (hard for the user to
			 * diagnose), fall back to the plugin's default levels
			 * (H2-H6) so the TOC still appears using headings that
			 * actually exist in the content.
			 */
			$allowed_levels = self::normalize_levels( array( 'h2', 'h3', 'h4', 'h5', 'h6' ) );
		}

		$nodes = self::query_heading_nodes( $content, $allowed_levels );
		$flat  = self::build_flat_list( $nodes, $settings['hash_format'], $settings['ignore_heading'] );

		if ( count( $flat ) < (int) $settings['minimal_heading_count'] ) {
			return array();
		}

		if ( ! empty( $settings['hierarchical_view'] ) ) {
			return self::build_tree( $flat );
		}

		return $flat;
	}

	/**
	 * Validates the allowed heading levels (h1-h6 only).
	 *
	 * @param array<int,string> $levels Levels from settings (e.g. ['h2','h3']).
	 * @return array<int,string>
	 */
	private static function normalize_levels( array $levels ) {
		$valid = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );

		return array_values( array_intersect( $valid, $levels ) );
	}

	/**
	 * Queries heading nodes from the content HTML for the allowed levels.
	 *
	 * A union XPath ('|') is returned in document order by libxml, so
	 * the heading order still matches the original content order.
	 *
	 * @param string             $content HTML content of the post.
	 * @param array<int,string>  $levels  Allowed heading levels.
	 * @return \DOMNodeList
	 */
	private static function query_heading_nodes( $content, array $levels ) {
		$dom = new \DOMDocument();

		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?><div>' . $content . '</div>' );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );
		$query = implode(
			' | ',
			array_map(
				function ( $level ) {
					return '//' . $level;
				},
				$levels
			)
		);

		return $xpath->query( $query );
	}

	/**
	 * Builds a flat heading list from a DOMNodeList.
	 *
	 * @param \DOMNodeList        $nodes        Heading nodes from the query.
	 * @param string              $hash_format  Hash format ('slug' or 'numeric').
	 * @param array<int,string>   $ignore_list  Heading text explicitly ignored.
	 * @return array<int,array<string,mixed>>
	 */
	private static function build_flat_list( $nodes, $hash_format, array $ignore_list ) {
		$headings = array();
		$used_ids = array();

		foreach ( $nodes as $node ) {
			if ( self::is_ignored( $node, $ignore_list ) ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $textContent is PHP's native DOMNode property, not our own variable.
			$text = trim( $node->textContent );

			if ( '' === $text ) {
				continue;
			}

			$id         = self::generate_id( $node, $text, $hash_format, $used_ids );
			$used_ids[] = $id;

			$headings[] = array(
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $nodeName is PHP's native DOMNode property, not our own variable.
				'level'    => (int) substr( $node->nodeName, 1 ),
				'text'     => $text,
				'id'       => $id,
				'children' => array(),
			);
		}

		return $headings;
	}

	/**
	 * Checks whether a heading should be ignored.
	 *
	 * A heading is ignored when it has the marker CSS class (added by
	 * the author through the block editor's built-in "Additional CSS
	 * Class" field, no extra meta box needed), or when its text
	 * matches the Ignore Heading list in settings.
	 *
	 * @param \DOMElement       $node        Heading node.
	 * @param array<int,string> $ignore_list List of ignored heading text.
	 * @return bool
	 */
	private static function is_ignored( $node, array $ignore_list ) {
		$class = $node->getAttribute( 'class' );

		if ( $class && false !== strpos( $class, self::IGNORE_CLASS ) ) {
			return true;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $textContent is PHP's native DOMNode property, not our own variable.
		$text = trim( $node->textContent );

		return in_array( $text, $ignore_list, true );
	}

	/**
	 * Generates the anchor id for a heading.
	 *
	 * A heading's own anchor (block editor's HTML Anchor) is always
	 * prioritized so it stays consistent with links the user may have
	 * already shared.
	 *
	 * @param \DOMElement       $node        Heading node.
	 * @param string            $text        Heading text.
	 * @param string            $hash_format Hash format ('slug' or 'numeric').
	 * @param array<int,string> $used_ids    Ids already in use (avoid duplicates).
	 * @return string
	 */
	private static function generate_id( $node, $text, $hash_format, array $used_ids ) {
		$existing = $node->getAttribute( 'id' );

		if ( $existing ) {
			return $existing;
		}

		if ( 'numeric' === $hash_format ) {
			$base      = 'toc-heading-' . ( count( $used_ids ) + 1 );
			$separator = '-';
		} else {
			$base      = self::titleize( $text );
			$separator = '_';
		}

		$id     = $base;
		$suffix = 2;

		while ( in_array( $id, $used_ids, true ) ) {
			$id = $base . $separator . $suffix;
			++$suffix;
		}

		return $id;
	}

	/**
	 * Converts heading text into a "Title_Case_Underscore" id.
	 *
	 * Unlike WordPress's built-in sanitize_title() (which produces a
	 * lowercase kebab-case slug, e.g. "mineral-town"), this format
	 * keeps each word's leading capital and joins words with an
	 * underscore, e.g. "Mineral Town" -> "Mineral_Town".
	 *
	 * @param string $text Original heading text.
	 * @return string
	 */
	private static function titleize( $text ) {
		$text = remove_accents( $text );
		$text = wp_strip_all_tags( $text );

		// Apostrophes are treated as part of a word (e.g. "O'Brien"),
		// any other non-alphanumeric character is treated as a word separator.
		$text = str_replace( "'", '', $text );
		$text = preg_replace( '/[^A-Za-z0-9]+/', ' ', $text );

		$words = array_filter( explode( ' ', trim( $text ) ), 'strlen' );
		$words = array_map(
			function ( $word ) {
				return ctype_upper( $word ) ? $word : ucfirst( strtolower( $word ) );
			},
			$words
		);

		$id = implode( '_', $words );

		return '' !== $id ? $id : 'section';
	}

	/**
	 * Injects the id attribute into the actual heading tags in the content.
	 *
	 * Note: parse() only generates an id to use as the TOC link's href
	 * ("#the-slug"), but that id is NEVER written to the actual
	 * <h2>-<h6> tags shown on the page. As a result, TOC links point
	 * to an element that doesn't exist, so clicking a link doesn't
	 * jump to the intended section.
	 *
	 * This method traverses and numbers ids with an algorithm
	 * identical to build_flat_list()/generate_id(), so the id
	 * injected into the actual heading always stays in sync with the
	 * id used in the TOC link's href.
	 *
	 * @param string               $content  Post content HTML (the version that will be displayed).
	 * @param array<string,mixed>  $settings Settings from Settings::resolve().
	 * @return string Content HTML with ids injected into the matching headings.
	 */
	public static function inject_ids( $content, array $settings ) {
		if ( '' === trim( (string) $content ) ) {
			return $content;
		}

		$allowed_levels = self::normalize_levels( $settings['by_level'] );

		if ( empty( $allowed_levels ) ) {
			$allowed_levels = self::normalize_levels( array( 'h2', 'h3', 'h4', 'h5', 'h6' ) );
		}

		$dom = new \DOMDocument();

		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?><div>' . $content . '</div>' );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );
		$query = implode(
			' | ',
			array_map(
				function ( $level ) {
					return '//' . $level;
				},
				$allowed_levels
			)
		);

		$nodes    = $xpath->query( $query );
		$used_ids = array();
		$changed  = false;

		foreach ( $nodes as $node ) {
			if ( self::is_ignored( $node, $settings['ignore_heading'] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $textContent is PHP's native DOMNode property, not our own variable.
			$text = trim( $node->textContent );

			if ( '' === $text ) {
				continue;
			}

			$existing = $node->getAttribute( 'id' );

			if ( $existing ) {
				$used_ids[] = $existing;
				continue;
			}

			$id = self::generate_id( $node, $text, $settings['hash_format'], $used_ids );
			$used_ids[] = $id;

			$node->setAttribute( 'id', $id );
			$changed = true;
		}

		if ( ! $changed ) {
			return $content;
		}

		$root_query = $xpath->query( '/html/body/div' );
		$root       = $root_query->length ? $root_query->item( 0 ) : null;

		if ( ! $root ) {
			return $content;
		}

		$html = '';

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $childNodes is PHP's native DOMNode property, not our own variable.
		foreach ( $root->childNodes as $child_node ) {
			$html .= $dom->saveHTML( $child_node );
		}

		return $html;
	}

	/**
	 * Builds a hierarchical structure from a flat heading list.
	 *
	 * Uses a stack holding direct references into $tree (not end(),
	 * since end() returns a copy of the value so its reference
	 * wouldn't carry through).
	 *
	 * @param array<int,array<string,mixed>> $flat Flat heading list.
	 * @return array<int,array<string,mixed>>
	 */
	private static function build_tree( array $flat ) {
		$tree  = array();
		$stack = array();

		foreach ( $flat as $heading ) {
			$stack_size = count( $stack );

			while ( $stack_size > 0 && $stack[ $stack_size - 1 ]['level'] >= $heading['level'] ) {
				array_pop( $stack );
				--$stack_size;
			}

			if ( 0 === $stack_size ) {
				$tree[]  = $heading;
				$stack[] = array(
					'level' => $heading['level'],
					'node'  => &$tree[ count( $tree ) - 1 ],
				);
				continue;
			}

			$parent                = &$stack[ $stack_size - 1 ]['node'];
			$parent['children'][]  = $heading;
			$stack[]                = array(
				'level' => $heading['level'],
				'node'  => &$parent['children'][ count( $parent['children'] ) - 1 ],
			);
		}

		return $tree;
	}
}
