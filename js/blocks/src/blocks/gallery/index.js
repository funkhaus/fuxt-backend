/**
 * External dependencies
 */
import classnames from 'classnames';
import filter from 'lodash/filter';

/**
 * Internal dependencies
 */
import icons from './../../utils/icons';
import Edit from './components/edit';
import * as helper from './../../utils/helper';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { createBlock } = wp.blocks;

/**
 * Block constants
 */
const name = 'gallery';

const title = __( 'Funkhaus Gallery' );

const icon = icons.gallery;

const keywords = [ __( 'gallery' ), __( 'images' ), __( 'photos' ) ];

const blockAttributes = {
	images: {
		type: 'array',
		default: [],
		source: 'query',
		selector: '.blockfunkhaus--item',
		query: {
			url: {
				source: 'attribute',
				selector: 'img',
				attribute: 'src',
			},
			link: {
				source: 'attribute',
				selector: 'img',
				attribute: 'data-link',
			},
			alt: {
				source: 'attribute',
				selector: 'img',
				attribute: 'alt',
				default: '',
			},
			id: {
				source: 'attribute',
				selector: 'img',
				attribute: 'data-id',
			},
			caption: {
				type: 'array',
				source: 'children',
				selector: 'figcaption',
			},
		},
	},
	linkTo: {
		type: 'string',
		default: 'none',
	},
	align: {
		type: 'string',
	},
	height: {
		type: 'number',
		default: 400,
	},
};

const settings = {
	title: title,

	description: __( 'Display multiple images in a beautiful carousel gallery.' ),

	keywords: keywords,

	attributes: blockAttributes,

	supports: {
		align: [ 'wide', 'full' ],
	},

	transforms: {
		from: [
			{
				type: 'block',
				isMultiBlock: true,
				blocks: [ 'core/image' ],
				transform: ( attributes ) => {
					const validImages = filter( attributes, ( { id, url } ) => id && url );
					if ( validImages.length > 0 ) {
						return createBlock( `blockfunkhaus/${ name }`, {
							images: validImages.map( ( { id, url, alt, caption } ) => ( {
								id,
								url,
								alt,
								caption,
							} ) ),
							ids: validImages.map( ( { id } ) => id ),
						} );
					}
					return createBlock( `blockfunkhaus/${ name }` );
				},
			},
			{
				type: 'prefix',
				prefix: ':gallery',
				transform: function( content ) {
					return createBlock( `blockfunkhaus/${ name }`, {
						content,
					} );
				},
			},
		],
		to: [
			{
				type: 'block',
				blocks: [ 'core/gallery' ],
				transform: ( attributes ) =>
					createBlock( 'core/gallery', {
						align: attributes.align,
						images: attributes.images.map( ( image ) => helper.pickRelevantMediaFiles( image ) ),
						linkTo: attributes.linkTo,
						height: attributes.height,
					} ),
			},
		],
	},

	edit: Edit,

	save( { attributes, className } ) {
		const {
			images,
		} = attributes;

		// Return early if there are no images.
		if ( images.length <= 0 ) {
			return;
		}

		const wrapperClasses = classnames( className );
		const figureClasses = classnames();

		return (
			<div className={ wrapperClasses }>
				{ images.map( ( image ) => {
					const img = (
						<img
							src={ image.url }
							alt={ image.alt }
							data-id={ image.id }
							data-link={ image.link }
							className={ image.id ? `wp-image-${ image.id }` : null }
						/>
					);

					return (
						<div key={ image.id || image.url } className="blockfunkhaus--item" >
							<figure className={ figureClasses }>{ img }</figure>
						</div>
					);
				} ) }
			</div>
		);
	},
};

export { name, title, icon, settings };
