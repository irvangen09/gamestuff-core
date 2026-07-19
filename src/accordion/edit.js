import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'gamestuff/accordion-item' ];

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'gamestuff-accordion',
	} );

	return (
		<div { ...blockProps }>
			<InnerBlocks
				allowedBlocks={ ALLOWED_BLOCKS }
				templateLock={ false }
			/>
		</div>
	);
}
