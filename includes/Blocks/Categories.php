<?php
/**
 * Custom block category shared by every GameStuff Core block.
 *
 * @package GameStuff\Blocks
 */

namespace GameStuff\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the "gamestuff" block category.
 *
 * Any block that declares "category": "gamestuff" in its block.json
 * needs this category to exist on the PHP side, or the Block Inserter
 * won't recognize the slug and the block loses its intended grouping.
 * Registered once here so every current and future block can rely on
 * it without registering the category itself.
 */
final class Categories {

	/**
	 * Registers the block_categories_all filter.
	 *
	 * @return void
	 */
	public static function register() {
		add_filter( 'block_categories_all', array( __CLASS__, 'add_category' ) );
	}

	/**
	 * Prepends the "gamestuff" category to the existing category list.
	 *
	 * @param array<int,array<string,string>> $categories Existing block categories.
	 * @return array<int,array<string,string>>
	 */
	public static function add_category( array $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'gamestuff',
					'title' => __( 'GameStuff', 'gamestuff-core' ),
					'icon'  => 'games',
				),
			),
			$categories
		);
	}
}
