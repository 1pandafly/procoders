( function( blocks, blockEditor, components, element, i18n ) {
	var el = element.createElement;
	var InnerBlocks = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload;
	var MediaUploadCheck = blockEditor.MediaUploadCheck;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var Button = components.Button;

	var ALLOWED_BLOCKS = [ 'core/heading', 'core/paragraph', 'core/buttons' ];
	var TEMPLATE = [
		[
			'core/heading',
			{
				level: 1,
				placeholder: i18n.__( 'Add hero title...', 'procoders' ),
			},
		],
		[
			'core/paragraph',
			{
				placeholder: i18n.__( 'Add hero description...', 'procoders' ),
			},
		],
		[
			'core/buttons',
			{},
			[
				[
					'core/button',
					{
						text: i18n.__( 'Book a demo', 'procoders' ),
					},
				],
				[
					'core/button',
					{
						text: i18n.__( 'Contact us', 'procoders' ),
					},
				],
			],
		],
	];

	function getMediaUrl( media ) {
		if ( ! media ) {
			return '';
		}

		if ( media.sizes ) {
			if ( media.sizes.large ) {
				return media.sizes.large.url;
			}

			if ( media.sizes.full ) {
				return media.sizes.full.url;
			}
		}

		return media.url || '';
	}

	function renderMediaPicker( options ) {
		return el(
			MediaUploadCheck,
			null,
			el( MediaUpload, {
				onSelect: options.onSelect,
				allowedTypes: [ 'image' ],
				value: options.value,
				render: function( renderProps ) {
					return el(
						'div',
						{ className: 'procoders-hero-limited-section__media-control' },
						el(
							Button,
							{
								variant: 'secondary',
								onClick: renderProps.open,
							},
							options.hasValue ? options.replaceLabel : options.addLabel
						),
						options.hasValue
							? el(
								Button,
								{
									variant: 'link',
									isDestructive: true,
									onClick: options.onRemove,
								},
								i18n.__( 'Remove image', 'procoders' )
							)
							: null
					);
				},
			} )
		);
	}

	blocks.registerBlockType( 'procoders/hero-limited-section', {
		edit: function( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var leftBackgroundImageId = attributes.leftBackgroundImageId;
			var leftBackgroundImageUrl = attributes.leftBackgroundImageUrl;
			var rightImageId = attributes.rightImageId;
			var rightImageUrl = attributes.rightImageUrl;

			var className = 'procoders-hero-limited-section';

			if ( leftBackgroundImageUrl ) {
				className += ' has-left-background-image';
			}

			if ( rightImageUrl ) {
				className += ' has-right-image';
			}

			var blockProps = useBlockProps( {
				className: className,
			} );

			var leftPanelStyle = leftBackgroundImageUrl
				? { backgroundImage: 'url(' + leftBackgroundImageUrl + ')' }
				: undefined;
			var rightPanelStyle = rightImageUrl
				? { backgroundImage: 'url(' + rightImageUrl + ')' }
				: undefined;

			return el(
				element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: i18n.__( 'Hero Images', 'procoders' ),
							initialOpen: true,
						},
						el( 'p', null, i18n.__( 'Left panel background image', 'procoders' ) ),
						renderMediaPicker( {
							value: leftBackgroundImageId,
							hasValue: !! leftBackgroundImageUrl,
							addLabel: i18n.__( 'Add left background image', 'procoders' ),
							replaceLabel: i18n.__( 'Replace left background image', 'procoders' ),
							onSelect: function( media ) {
								setAttributes( {
									leftBackgroundImageId: media && media.id ? media.id : 0,
									leftBackgroundImageUrl: getMediaUrl( media ),
								} );
							},
							onRemove: function() {
								setAttributes( {
									leftBackgroundImageId: 0,
									leftBackgroundImageUrl: '',
								} );
							},
						} ),
						el( 'hr', null ),
						el( 'p', null, i18n.__( 'Right panel image', 'procoders' ) ),
						renderMediaPicker( {
							value: rightImageId,
							hasValue: !! rightImageUrl,
							addLabel: i18n.__( 'Add right image', 'procoders' ),
							replaceLabel: i18n.__( 'Replace right image', 'procoders' ),
							onSelect: function( media ) {
								setAttributes( {
									rightImageId: media && media.id ? media.id : 0,
									rightImageUrl: getMediaUrl( media ),
								} );
							},
							onRemove: function() {
								setAttributes( {
									rightImageId: 0,
									rightImageUrl: '',
								} );
							},
						} )
					)
				),
				el(
					'section',
					blockProps,
					el(
						'div',
						{ className: 'procoders-hero-limited-section__layout' },
						el(
							'div',
							{
								className: 'procoders-hero-limited-section__left',
								style: leftPanelStyle,
							},
							el(
								'div',
								{ className: 'procoders-hero-limited-section__left-content' },
								el( InnerBlocks, {
									allowedBlocks: ALLOWED_BLOCKS,
									template: TEMPLATE,
									templateLock: false,
								} )
							)
						),
						el(
							'div',
							{
								className:
									'procoders-hero-limited-section__right' +
									( rightImageUrl ? '' : ' is-empty' ),
								style: rightPanelStyle,
							},
							rightImageUrl
								? null
								: el(
									'span',
									{ className: 'procoders-hero-limited-section__right-placeholder' },
									i18n.__( 'Select right image in block settings', 'procoders' )
								)
						)
					)
				)
			);
		},
		save: function( props ) {
			var attributes = props.attributes;
			var leftBackgroundImageUrl = attributes.leftBackgroundImageUrl;
			var rightImageUrl = attributes.rightImageUrl;
			var className = 'procoders-hero-limited-section';

			if ( leftBackgroundImageUrl ) {
				className += ' has-left-background-image';
			}

			if ( rightImageUrl ) {
				className += ' has-right-image';
			}

			var blockProps = useBlockProps.save( {
				className: className,
			} );

			var leftPanelStyle = leftBackgroundImageUrl
				? { backgroundImage: 'url(' + leftBackgroundImageUrl + ')' }
				: undefined;
			var rightPanelStyle = rightImageUrl
				? { backgroundImage: 'url(' + rightImageUrl + ')' }
				: undefined;

			return el(
				'section',
				blockProps,
				el(
					'div',
					{ className: 'procoders-hero-limited-section__layout' },
					el(
						'div',
						{
							className: 'procoders-hero-limited-section__left',
							style: leftPanelStyle,
						},
						el(
							'div',
							{ className: 'procoders-hero-limited-section__left-content' },
							el( InnerBlocks.Content )
						)
					),
					el( 'div', {
						className:
							'procoders-hero-limited-section__right' +
							( rightImageUrl ? '' : ' is-empty' ),
						style: rightPanelStyle,
					} )
				)
			);
		},
		deprecated: [
			{
				save: function() {
					var blockProps = useBlockProps.save( {
						className: 'procoders-hero-limited-section',
					} );

					return el(
						'section',
						blockProps,
						el(
							'div',
							{ className: 'procoders-hero-limited-section__inner' },
							el( InnerBlocks.Content )
						)
					);
				},
			},
		],
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
