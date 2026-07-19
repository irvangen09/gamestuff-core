import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	const { title, icon, iconColor, headingLevel, triggerId, panelId } =
		attributes;

	const blockProps = useBlockProps.save( {
		className: 'gamestuff-accordion-item',
	} );

	const HeadingTag = headingLevel || 'h2';

	return (
		<div { ...blockProps }>
			<HeadingTag className="gamestuff-accordion-item__heading">
				<button
					id={ triggerId }
					type="button"
					className="gamestuff-accordion-item__trigger"
					aria-expanded="false"
					aria-controls={ panelId }
				>
					<span
						className={ `gamestuff-accordion-item__icon dashicons ${ icon }` }
						style={ { color: iconColor } }
						aria-hidden="true"
					/>

					<span className="gamestuff-accordion-item__title">
						<RichText.Content value={ title } />
					</span>

					<span
						className="gamestuff-accordion-item__chevron"
						aria-hidden="true"
					>
						▾
					</span>
				</button>
			</HeadingTag>

			<div
				id={ panelId }
				className="gamestuff-accordion-item__content"
				role="region"
				aria-labelledby={ triggerId }
			>
				<InnerBlocks.Content />
			</div>
		</div>
	);
}
