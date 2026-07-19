import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

import {
	InnerBlocks,
	InspectorControls,
	RichText,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';

import { PanelBody, SelectControl, TextControl } from '@wordpress/components';

const HEADING_LEVELS = [
	{ label: 'H2', value: 'h2' },
	{ label: 'H3', value: 'h3' },
	{ label: 'H4', value: 'h4' },
	{ label: 'H5', value: 'h5' },
	{ label: 'H6', value: 'h6' },
];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { title, icon, iconColor, headingLevel, triggerId, panelId } =
		attributes;

	/**
	 * Detect a colliding id inherited from another Accordion Item in
	 * the SAME post — this happens when a block is duplicated or
	 * copy-pasted, since Gutenberg clones attributes verbatim
	 * (including triggerId/panelId) but assigns a new clientId.
	 * Without this check, the clone would keep the exact same ids
	 * as its source, producing duplicate `id` attributes on the
	 * rendered page.
	 *
	 * Deliberately scoped to the WHOLE post (getClientIdsWithDescendants,
	 * not just this item's siblings within one Accordion), because a
	 * collision can happen across two entirely different Accordion
	 * blocks on the same page — e.g. copy-pasting an Accordion Item
	 * from one Accordion into another. Narrowing the scope to one
	 * parent would miss that case.
	 *
	 * --- Performance analysis (kept, not changed) ---
	 *
	 * This was flagged in an earlier audit as an O(items × total
	 * blocks) cost, worth checking before assuming it needs
	 * optimizing. There's no live WordPress editor available in the
	 * environment this revision was made in to profile directly, so
	 * this is a source-level analysis instead of a measurement — see
	 * the trade-off below for exactly what that means for confidence
	 * in this conclusion.
	 *
	 * `getClientIdsWithDescendants()` itself is NOT the expensive
	 * part in practice: it's wrapped in Gutenberg core's own
	 * `createSelector()` memoization (packages/block-editor/src/store/
	 * selectors.js), keyed on block order/structure — not on block
	 * attributes. That means typing inside a RichText field (the most
	 * frequent editing action) does NOT re-walk the block tree; the
	 * memoized id list is reused as-is. The tree walk only re-runs
	 * when blocks are actually added, removed, or reordered — a much
	 * rarer event than a keystroke. WordPress core has also
	 * repeatedly optimized this exact selector across versions
	 * (e.g. "Optimize getClientIdsOfDescendants and
	 * getClientIdsWithDescendants selectors"), so this plugin
	 * benefits from that work automatically on any reasonably current
	 * WordPress version rather than needing its own workaround.
	 *
	 * What DOES still run on every relevant keystroke is the `.some()`
	 * loop below — but each iteration is a cheap, direct property
	 * lookup (getBlockName / getBlockAttributes), not a recomputation,
	 * and it already fails fast in the cheapest-check-first order:
	 * identity check, then name check, then attribute check, with
	 * `.some()` stopping at the first real collision. The early
	 * `if ( ! triggerId && ! panelId )` return above also skips the
	 * entire check for any item that doesn't have an id yet.
	 *
	 * Given that, no further change was made here: the one available
	 * alternative considered (debouncing this check) would trade a
	 * small, currently-unmeasured cost for a real one — a delayed
	 * collision indicator, which is an actual behavior change the
	 * scope of this revision doesn't call for introducing without
	 * clear evidence it's needed. If a future WordPress version, a
	 * much larger real page, or a different editor profiling setup
	 * shows this is actually costly in practice, that would be the
	 * moment to revisit this — not before.
	 */
	const hasIdCollision = useSelect(
		( select ) => {
			if ( ! triggerId && ! panelId ) {
				return false;
			}

			const {
				getClientIdsWithDescendants,
				getBlockName,
				getBlockAttributes,
			} = select( blockEditorStore );

			return getClientIdsWithDescendants().some( ( otherId ) => {
				if ( otherId === clientId ) {
					return false;
				}

				if ( getBlockName( otherId ) !== 'gamestuff/accordion-item' ) {
					return false;
				}

				const otherAttributes = getBlockAttributes( otherId );

				return (
					( !! triggerId &&
						otherAttributes.triggerId === triggerId ) ||
					( !! panelId && otherAttributes.panelId === panelId )
				);
			} );
		},
		[ clientId, triggerId, panelId ]
	);

	/**
	 * Bootstrap a stable, persisted id the first time this block is
	 * created (nothing saved yet to extract triggerId/panelId from),
	 * OR regenerate it if a collision with a sibling was detected
	 * (duplicate/copy-paste case above).
	 *
	 * `clientId` is only used here as a convenient, already-unique
	 * seed for that generation — it is never referenced again after
	 * this. From the next save onward, triggerId and panelId are
	 * read back from the stored HTML itself (see block.json
	 * `source: "attribute"`), so they stay identical every time the
	 * post is reopened, unlike clientId.
	 */
	useEffect( () => {
		if ( ! triggerId || ! panelId || hasIdCollision ) {
			const seed = `${ clientId.slice( 0, 8 ) }-${ Math.random()
				.toString( 36 )
				.slice( 2, 6 ) }`;

			setAttributes( {
				triggerId: `gamestuff-accordion-trigger-${ seed }`,
				panelId: `gamestuff-accordion-panel-${ seed }`,
			} );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ hasIdCollision ] );

	const blockProps = useBlockProps( {
		className: 'gamestuff-accordion-item',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title="Accordion Item">
					<SelectControl
						label="Heading Level"
						value={ headingLevel }
						options={ HEADING_LEVELS }
						onChange={ ( value ) =>
							setAttributes( {
								headingLevel: value,
							} )
						}
					/>

					<TextControl
						label="Icon"
						value={ icon }
						onChange={ ( value ) =>
							setAttributes( {
								icon: value,
							} )
						}
						placeholder="dashicons-media-document"
						help="Masukkan nama class icon, contoh: dashicons-instagram. Bisa juga isi beberapa class sekaligus (mis. dari Font Awesome) jika theme-mu sudah memuat library-nya."
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="gamestuff-accordion-item__header">
					<span
						className={ `gamestuff-accordion-item__icon dashicons ${ icon }` }
						style={ { color: iconColor } }
						aria-hidden="true"
					/>

					<RichText
						tagName={ headingLevel }
						className="gamestuff-accordion-item__title"
						value={ title }
						onChange={ ( value ) =>
							setAttributes( {
								title: value,
							} )
						}
						placeholder="Section title..."
						allowedFormats={ [] }
					/>

					<button
						type="button"
						className="gamestuff-accordion-item__toggle"
						tabIndex={ -1 }
						aria-hidden="true"
					>
						<span className="gamestuff-accordion-item__chevron">▾</span>
					</button>
				</div>

				<div className="gamestuff-accordion-item__content">
					<InnerBlocks templateLock={ false } />
				</div>
			</div>
		</>
	);
}
