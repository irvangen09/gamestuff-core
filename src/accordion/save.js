import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function Save() {
	const blockProps = useBlockProps.save( {
		className: 'gamestuff-accordion',
	} );

	return (
		<div { ...blockProps }>
			<InnerBlocks.Content />
		</div>
	);
}
