( function( blocks, blockEditor, components, element, i18n ) {
	const el = element.createElement;
	const Fragment = element.Fragment;
	const useBlockProps = blockEditor.useBlockProps;
	const InspectorControls = blockEditor.InspectorControls;
	const ServerSideRender = window.wp.serverSideRender;
	const PanelBody = components.PanelBody;
	const TextControl = components.TextControl;

	blocks.registerBlockType( 'procoders/benefits-section', {
		edit: function( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;
			const blockProps = useBlockProps( {
				className: 'procoders-benefits',
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: i18n.__( 'Benefits Settings', 'procoders' ),
							initialOpen: true,
						},
						el( TextControl, {
							label: i18n.__( 'Title', 'procoders' ),
							value: attributes.title || '',
							onChange: function( value ) {
								setAttributes( { title: value } );
							},
						} ),
						el( TextControl, {
							label: i18n.__( 'Benefits per request', 'procoders' ),
							type: 'number',
							min: 1,
							max: 24,
							value: attributes.postsPerPage || 3,
							onChange: function( value ) {
								const parsed = parseInt( value, 10 );

								if ( Number.isNaN( parsed ) ) {
									setAttributes( { postsPerPage: 3 } );
									return;
								}

								setAttributes( { postsPerPage: Math.max( 1, Math.min( 24, parsed ) ) } );
							},
							help: i18n.__( 'How many benefits are loaded initially and on each "Load more".', 'procoders' ),
						} )
					)
				),
				el(
					'section',
					blockProps,
					ServerSideRender
						? el( ServerSideRender, {
							block: 'procoders/benefits-section',
							attributes: attributes,
							httpMethod: 'POST',
						} )
						: el(
							'p',
							{ className: 'procoders-benefits__editor-note' },
							i18n.__( 'Preview unavailable. Front-end rendering still works.', 'procoders' )
						)
				)
			);
		},
		save: function() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
