<?php
/**
 * GameStuff Core admin Settings page (skeleton).
 *
 * @package GameStuff\Settings
 */

namespace GameStuff\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the GameStuff Core admin menu along with its tab navigation.
 *
 * This is the Settings page SKELETON. There are no functional
 * settings fields yet — each tab only shows a placeholder unless a
 * settings section has already been registered for it. Per-tab field
 * implementation is handled in its own dedicated stage.
 *
 * Uses WordPress's built-in admin classes (nav-tab-wrapper) with no
 * extra CSS/JS, to stay native to the WordPress admin.
 */
final class Menu {

	/**
	 * Menu page slug.
	 *
	 * @var string
	 */
	const SLUG = 'gamestuff-core';

	/**
	 * Registers the hook that displays the menu in wp-admin.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	/**
	 * Adds the main GameStuff Core menu page.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_menu_page(
			__( 'GameStuff Core', 'gamestuff-core' ),
			__( 'GameStuff Core', 'gamestuff-core' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_page' ),
			'dashicons-editor-table',
			80
		);
	}

	/**
	 * Renders the Settings page along with its tab navigation.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs        = Tabs::get_tabs();
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : Tabs::get_default_tab(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! array_key_exists( $current_tab, $tabs ) ) {
			$current_tab = Tabs::get_default_tab();
		}

		echo '<div class="wrap gamestuff-settings">';
		echo '<h1>' . esc_html__( 'GameStuff Core', 'gamestuff-core' ) . '</h1>';

		$this->render_tab_navigation( $tabs, $current_tab );

		echo '<div class="gamestuff-settings__content">';
		$this->render_tab_content( $current_tab );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Renders the tab navigation using WordPress's built-in admin classes.
	 *
	 * @param array<string,string> $tabs        List of tabs.
	 * @param string               $current_tab Slug of the currently active tab.
	 * @return void
	 */
	private function render_tab_navigation( $tabs, $current_tab ) {
		echo '<nav class="nav-tab-wrapper">';

		foreach ( $tabs as $slug => $label ) {
			$is_active = ( $slug === $current_tab );
			$url       = add_query_arg(
				array(
					'page' => self::SLUG,
					'tab'  => $slug,
				),
				admin_url( 'admin.php' )
			);

			printf(
				'<a href="%1$s" class="nav-tab%2$s">%3$s</a>',
				esc_url( $url ),
				$is_active ? ' nav-tab-active' : '',
				esc_html( $label )
			);
		}

		echo '</nav>';
	}

	/**
	 * Renders the content of the currently active tab.
	 *
	 * Generic: when a tab has a registered settings section (via the
	 * WordPress Settings API, page slug 'gamestuff_core_{tab}'), the
	 * form and its fields render automatically. A tab with no
	 * registered section still shows a placeholder (not implemented
	 * yet). This keeps Menu.php unaware of any specific block.
	 *
	 * @param string $tab Slug of the currently active tab.
	 * @return void
	 */
	private function render_tab_content( $tab ) {
		global $wp_settings_sections;

		$page = 'gamestuff_core_' . $tab;

		if ( empty( $wp_settings_sections[ $page ] ) ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'Konten pengaturan untuk tab ini akan tersedia pada tahap implementasi berikutnya.', 'gamestuff-core' )
			);
			return;
		}

		echo '<form method="post" action="options.php">';
		settings_fields( $page );
		do_settings_sections( $page );
		submit_button();
		echo '</form>';
	}
}
