import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	SelectControl,
	CheckboxControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const HEADING_LEVELS = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

/**
 * Standard tri-state options for a Block Override boolean field: empty means
 * "follow the Global Setting" - unlike a regular ToggleControl, which can
 * only be on/off and has no way to represent "not overridden yet".
 */
const INHERIT_OPTIONS = [
	{ label: __( 'Ikuti Global', 'gamestuff-core' ), value: '' },
	{ label: __( 'Aktif', 'gamestuff-core' ), value: '1' },
	{ label: __( 'Nonaktif', 'gamestuff-core' ), value: '0' },
];

/**
 * Tri-state SelectControl for a Block Override boolean field.
 *
 * @param {Object}   props
 * @param {string}   props.label
 * @param {string}   props.value
 * @param {Function} props.onChange
 */
function InheritToggleControl( { label, value, onChange } ) {
	return (
		<SelectControl
			label={ label }
			value={ value || '' }
			options={ INHERIT_OPTIONS }
			onChange={ onChange }
		/>
	);
}

export default function Edit( { attributes, setAttributes, name } ) {
	const blockProps = useBlockProps();
	const selectedLevels = Array.isArray( attributes.by_level ) ? attributes.by_level : [];

	const toggleLevel = ( level, checked ) => {
		const next = checked
			? [ ...selectedLevels, level ]
			: selectedLevels.filter( ( item ) => item !== level );

		setAttributes( { by_level: next } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'General', 'gamestuff-core' ) } initialOpen>
					<TextControl
						label={ __( 'Title', 'gamestuff-core' ) }
						value={ attributes.title || '' }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						help={ __( 'Kosongkan untuk mengikuti Global Setting.', 'gamestuff-core' ) }
					/>
					<TextControl
						label={ __( 'Label Show', 'gamestuff-core' ) }
						value={ attributes.label_show || '' }
						onChange={ ( value ) => setAttributes( { label_show: value } ) }
						help={ __( 'Kosongkan untuk mengikuti Global Setting.', 'gamestuff-core' ) }
					/>
					<TextControl
						label={ __( 'Label Hide', 'gamestuff-core' ) }
						value={ attributes.label_hide || '' }
						onChange={ ( value ) => setAttributes( { label_hide: value } ) }
						help={ __( 'Kosongkan untuk mengikuti Global Setting.', 'gamestuff-core' ) }
					/>
					<TextControl
						label={ __( 'Minimal Count of Heading', 'gamestuff-core' ) }
						type="number"
						min={ 1 }
						value={ attributes.minimal_heading_count || '' }
						onChange={ ( value ) => setAttributes( { minimal_heading_count: value } ) }
						placeholder={ __( 'Ikuti Global', 'gamestuff-core' ) }
					/>
					<InheritToggleControl
						label={ __( 'Hierarchical View', 'gamestuff-core' ) }
						value={ attributes.hierarchical_view }
						onChange={ ( value ) => setAttributes( { hierarchical_view: value } ) }
					/>
					<InheritToggleControl
						label={ __( 'Default Hidden', 'gamestuff-core' ) }
						value={ attributes.default_hidden }
						onChange={ ( value ) => setAttributes( { default_hidden: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Appearance', 'gamestuff-core' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Width', 'gamestuff-core' ) }
						value={ attributes.width || '' }
						onChange={ ( value ) => setAttributes( { width: value } ) }
						help={ __( 'Contoh: 300px, 100%, 20rem. Kosongkan untuk mengikuti Global Setting.', 'gamestuff-core' ) }
					/>
					<SelectControl
						label={ __( 'Float', 'gamestuff-core' ) }
						value={ attributes.float || '' }
						options={ [
							{ label: __( 'Ikuti Global', 'gamestuff-core' ), value: '' },
							{ label: __( 'None', 'gamestuff-core' ), value: 'none' },
							{ label: __( 'Left', 'gamestuff-core' ), value: 'left' },
							{ label: __( 'Right', 'gamestuff-core' ), value: 'right' },
						] }
						onChange={ ( value ) => setAttributes( { float: value } ) }
					/>
					<SelectControl
						label={ __( 'Color Scheme', 'gamestuff-core' ) }
						value={ attributes.color_scheme || '' }
						options={ [
							{ label: __( 'Ikuti Global', 'gamestuff-core' ), value: '' },
							{ label: __( 'Auto', 'gamestuff-core' ), value: 'auto' },
							{ label: __( 'Light', 'gamestuff-core' ), value: 'light' },
							{ label: __( 'Dark', 'gamestuff-core' ), value: 'dark' },
						] }
						onChange={ ( value ) => setAttributes( { color_scheme: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Misc', 'gamestuff-core' ) } initialOpen={ false }>
					<p>
						{ __( 'By Level', 'gamestuff-core' ) }
						{ ' ' }
						<em>{ __( '(kosongkan semua untuk mengikuti Global Setting)', 'gamestuff-core' ) }</em>
					</p>
					{ HEADING_LEVELS.map( ( level ) => (
						<CheckboxControl
							key={ level }
							label={ level.toUpperCase() }
							checked={ selectedLevels.includes( level ) }
							onChange={ ( checked ) => toggleLevel( level, checked ) }
						/>
					) ) }
					<SelectControl
						label={ __( 'Hash Format', 'gamestuff-core' ) }
						value={ attributes.hash_format || '' }
						options={ [
							{ label: __( 'Ikuti Global', 'gamestuff-core' ), value: '' },
							{ label: __( 'Slug', 'gamestuff-core' ), value: 'slug' },
							{ label: __( 'Numeric', 'gamestuff-core' ), value: 'numeric' },
						] }
						onChange={ ( value ) => setAttributes( { hash_format: value } ) }
					/>
					<SelectControl
						label={ __( 'Numeration', 'gamestuff-core' ) }
						value={ attributes.numeration || '' }
						options={ [
							{ label: __( 'Ikuti Global', 'gamestuff-core' ), value: '' },
							{ label: __( 'Without Numeration', 'gamestuff-core' ), value: 'none' },
							{ label: __( 'Decimal Number', 'gamestuff-core' ), value: 'decimal' },
							{ label: __( 'Roman Number', 'gamestuff-core' ), value: 'roman' },
						] }
						onChange={ ( value ) => setAttributes( { numeration: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Behavior', 'gamestuff-core' ) } initialOpen={ false }>
					<InheritToggleControl
						label={ __( 'Smooth Scroll', 'gamestuff-core' ) }
						value={ attributes.smooth_scroll }
						onChange={ ( value ) => setAttributes( { smooth_scroll: value } ) }
					/>
					<TextControl
						label={ __( 'Scroll Offset', 'gamestuff-core' ) }
						type="number"
						min={ 0 }
						value={ attributes.scroll_offset || '' }
						onChange={ ( value ) => setAttributes( { scroll_offset: value } ) }
						placeholder={ __( 'Ikuti Global', 'gamestuff-core' ) }
					/>
					<InheritToggleControl
						label={ __( 'Highlight Active Heading', 'gamestuff-core' ) }
						value={ attributes.highlight_active }
						onChange={ ( value ) => setAttributes( { highlight_active: value } ) }
					/>
					<InheritToggleControl
						label={ __( 'Collapse Subheading', 'gamestuff-core' ) }
						value={ attributes.collapse_subheading }
						onChange={ ( value ) => setAttributes( { collapse_subheading: value } ) }
					/>
					<InheritToggleControl
						label={ __( 'Sticky TOC (Desktop)', 'gamestuff-core' ) }
						value={ attributes.sticky_desktop }
						onChange={ ( value ) => setAttributes( { sticky_desktop: value } ) }
					/>
					<InheritToggleControl
						label={ __( 'Sticky TOC (Tablet)', 'gamestuff-core' ) }
						value={ attributes.sticky_tablet }
						onChange={ ( value ) => setAttributes( { sticky_tablet: value } ) }
					/>
					<InheritToggleControl
						label={ __( 'Sticky TOC (Mobile)', 'gamestuff-core' ) }
						value={ attributes.sticky_mobile }
						onChange={ ( value ) => setAttributes( { sticky_mobile: value } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Display Device', 'gamestuff-core' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Desktop', 'gamestuff-core' ) }
						checked={ attributes.display_desktop !== false }
						onChange={ ( value ) => setAttributes( { display_desktop: value } ) }
					/>
					<ToggleControl
						label={ __( 'Tablet', 'gamestuff-core' ) }
						checked={ attributes.display_tablet !== false }
						onChange={ ( value ) => setAttributes( { display_tablet: value } ) }
					/>
					<ToggleControl
						label={ __( 'Mobile', 'gamestuff-core' ) }
						checked={ attributes.display_mobile !== false }
						onChange={ ( value ) => setAttributes( { display_mobile: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender block={ name } attributes={ attributes } />
			</div>
		</>
	);
}