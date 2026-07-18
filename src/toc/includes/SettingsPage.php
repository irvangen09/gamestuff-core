<?php
/**
 * Global Settings UI for GameStuff TOC.
 *
 * @package GameStuff\Blocks\Toc
 */

namespace GameStuff\Blocks\Toc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the GameStuff TOC Global Setting fields under the
 * "Blocks" tab of the plugin's Settings page, using the WordPress
 * Settings API.
 *
 * Field categories: General, Appearance, Auto Insert, Misc, Behavior.
 */
final class SettingsPage {

	/**
	 * Page slug this section/field is registered under (the "Blocks" tab).
	 *
	 * @var string
	 */
	const PAGE = 'gamestuff_core_blocks';

	/**
	 * Registers the admin_init hook.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Registers the option, sections, and fields.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			self::PAGE,
			Settings::GLOBAL_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);

		self::add_section(
			'general',
			__( 'GameStuff TOC — General', 'gamestuff-core' ),
			array(
				array(
					'key'   => 'minimal_heading_count',
					'label' => __( 'Minimal Count of Heading', 'gamestuff-core' ),
					'type'  => 'number',
				),
				array(
					'key'   => 'hierarchical_view',
					'label' => __( 'Hierarchical View', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'title',
					'label' => __( 'Title', 'gamestuff-core' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'label_show',
					'label' => __( 'Label Show', 'gamestuff-core' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'label_hide',
					'label' => __( 'Label Hide', 'gamestuff-core' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'default_hidden',
					'label' => __( 'Default Hidden', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
			)
		);

		self::add_section(
			'appearance',
			__( 'GameStuff TOC — Appearance', 'gamestuff-core' ),
			array(
				array(
					'key'   => 'width',
					'label' => __( 'Width', 'gamestuff-core' ),
					'type'  => 'text',
				),
				array(
					'key'     => 'float',
					'label'   => __( 'Float', 'gamestuff-core' ),
					'type'    => 'select',
					'options' => array(
						'none'  => __( 'None', 'gamestuff-core' ),
						'left'  => __( 'Left', 'gamestuff-core' ),
						'right' => __( 'Right', 'gamestuff-core' ),
					),
				),
				array(
					'key'     => 'color_scheme',
					'label'   => __( 'Color Scheme', 'gamestuff-core' ),
					'type'    => 'select',
					'options' => array(
						'auto'  => __( 'Auto', 'gamestuff-core' ),
						'light' => __( 'Light', 'gamestuff-core' ),
						'dark'  => __( 'Dark', 'gamestuff-core' ),
					),
				),
			)
		);

		self::add_section(
			'auto_insert',
			__( 'GameStuff TOC — Auto Insert', 'gamestuff-core' ),
			array(
				array(
					'key'   => 'auto_insert',
					'label' => __( 'Auto Insert TOC', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
			)
		);

		self::add_section(
			'misc',
			__( 'GameStuff TOC — Misc', 'gamestuff-core' ),
			array(
				array(
					'key'   => 'by_level',
					'label' => __( 'By Level', 'gamestuff-core' ),
					'type'  => 'levels',
				),
				array(
					'key'     => 'hash_format',
					'label'   => __( 'Hash Format', 'gamestuff-core' ),
					'type'    => 'select',
					'options' => array(
						'slug'    => __( 'Slug', 'gamestuff-core' ),
						'numeric' => __( 'Numeric', 'gamestuff-core' ),
					),
				),
				array(
					'key'     => 'numeration',
					'label'   => __( 'Numeration', 'gamestuff-core' ),
					'type'    => 'select',
					'options' => array(
						'none'    => __( 'Without Numeration', 'gamestuff-core' ),
						'decimal' => __( 'Decimal Number', 'gamestuff-core' ),
						'roman'   => __( 'Roman Number', 'gamestuff-core' ),
					),
				),
			)
		);

		self::add_section(
			'behavior',
			__( 'GameStuff TOC — Behavior', 'gamestuff-core' ),
			array(
				array(
					'key'   => 'smooth_scroll',
					'label' => __( 'Smooth Scroll', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'scroll_offset',
					'label' => __( 'Scroll Offset', 'gamestuff-core' ),
					'type'  => 'number',
				),
				array(
					'key'   => 'highlight_active',
					'label' => __( 'Highlight Active Heading', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'collapse_subheading',
					'label' => __( 'Collapse Subheading', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'sticky_desktop',
					'label' => __( 'Sticky TOC (Desktop)', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'sticky_tablet',
					'label' => __( 'Sticky TOC (Tablet)', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'sticky_mobile',
					'label' => __( 'Sticky TOC (Mobile)', 'gamestuff-core' ),
					'type'  => 'checkbox',
				),
				array(
					'key'   => 'ignore_heading',
					'label' => __( 'Ignore Heading', 'gamestuff-core' ),
					'type'  => 'textarea_list',
				),
			)
		);
	}

	/**
	 * Registers one section along with its fields.
	 *
	 * @param string                          $slug   Section slug (without prefix).
	 * @param string                          $title  Section title.
	 * @param array<int,array<string,mixed>>  $fields List of field definitions.
	 * @return void
	 */
	private static function add_section( $slug, $title, array $fields ) {
		$section_id = 'gamestuff_toc_' . $slug;

		add_settings_section( $section_id, $title, '__return_false', self::PAGE );

		foreach ( $fields as $field ) {
			add_settings_field(
				'gamestuff_toc_' . $field['key'],
				$field['label'],
				array( __CLASS__, 'render_field' ),
				self::PAGE,
				$section_id,
				$field
			);
		}
	}

	/**
	 * Renders a single field based on its type.
	 *
	 * @param array<string,mixed> $field Field definition ('key', 'type', 'options').
	 * @return void
	 */
	public static function render_field( array $field ) {
		$values  = get_option( Settings::GLOBAL_OPTION_KEY, array() );
		$key     = $field['key'];
		$default = Settings::defaults();
		$value   = array_key_exists( $key, $values ) ? $values[ $key ] : $default[ $key ];
		$name    = Settings::GLOBAL_OPTION_KEY . '[' . $key . ']';

		switch ( $field['type'] ) {
			case 'checkbox':
				printf(
					'<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>',
					esc_attr( $name ),
					checked( (bool) $value, true, false ),
					esc_html__( 'Aktif', 'gamestuff-core' )
				);
				break;

			case 'number':
				printf(
					'<input type="number" name="%1$s" value="%2$s" class="small-text" />',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;

			case 'select':
				echo '<select name="' . esc_attr( $name ) . '">';
				foreach ( $field['options'] as $option_value => $option_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $option_value ),
						selected( $value, $option_value, false ),
						esc_html( $option_label )
					);
				}
				echo '</select>';
				break;

			case 'levels':
				$selected_levels = is_array( $value ) ? $value : array();

				foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $level ) {
					printf(
						'<label style="margin-inline-end:0.75em;"><input type="checkbox" name="%1$s[]" value="%2$s" %3$s /> %4$s</label>',
						esc_attr( $name ),
						esc_attr( $level ),
						checked( in_array( $level, $selected_levels, true ), true, false ),
						esc_html( strtoupper( $level ) )
					);
				}
				break;

			case 'textarea_list':
				$text = is_array( $value ) ? implode( "\n", $value ) : '';
				printf(
					'<textarea name="%1$s" rows="4" class="large-text" placeholder="%2$s">%3$s</textarea>',
					esc_attr( $name ),
					esc_attr__( 'Satu teks heading per baris', 'gamestuff-core' ),
					esc_textarea( $text )
				);
				break;

			default:
				printf(
					'<input type="text" name="%1$s" value="%2$s" class="regular-text" />',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;
		}
	}

	/**
	 * Sanitizes the entire Global Setting before it's saved.
	 *
	 * The final shape always follows Settings::defaults() (single
	 * source of truth), including explicitly turning an unchecked
	 * checkbox into false (the browser doesn't send an empty
	 * checkbox field at all).
	 *
	 * @param mixed $value Raw value from the form submission.
	 * @return array<string,mixed>
	 */
	public static function sanitize( $value ) {
		$value = is_array( $value ) ? $value : array();
		$clean = array();

		foreach ( Settings::defaults() as $key => $default ) {
			if ( ! array_key_exists( $key, $value ) ) {
				$clean[ $key ] = is_bool( $default ) ? false : $default;
				continue;
			}

			$raw = $value[ $key ];

			if ( is_bool( $default ) ) {
				$clean[ $key ] = ! empty( $raw );
			} elseif ( is_int( $default ) ) {
				$clean[ $key ] = (int) $raw;
			} elseif ( is_array( $default ) ) {
				if ( 'ignore_heading' === $key && is_string( $raw ) ) {
					$clean[ $key ] = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
				} else {
					$clean[ $key ] = array_map( 'sanitize_text_field', (array) $raw );
				}
			} else {
				$clean[ $key ] = sanitize_text_field( $raw );
			}
		}

		return $clean;
	}
}
