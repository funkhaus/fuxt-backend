/**
 * External dependencies
 */
import classnames from 'classnames';
import filter from 'lodash/filter';

/**
 * Internal dependencies
 */
import GalleryImage from '../../../components/gallery-image';
import GalleryPlaceholder from '../../../components/gallery-placeholder';
import GalleryDropZone from '../../../components/gallery-dropzone';
import GalleryToolbar from '../../../components/gallery-toolbar';
import { title, icon } from '../';

/**
 * WordPress dependencies
 */
const { __, sprintf } = wp.i18n;
const { Component, Fragment } = wp.element;
const { compose } = wp.compose;
const { withNotices } = wp.components;

/**
 * Block edit function
 */
class Edit extends Component {
	constructor() {
		super( ...arguments );

		this.onSelectImage = this.onSelectImage.bind( this );
		this.onRemoveImage = this.onRemoveImage.bind( this );
		this.setImageAttributes = this.setImageAttributes.bind( this );
		this.onItemClick = this.onItemClick.bind( this );

		this.state = {
			selectedImage: null,
		};
	}

	componentDidMount() {
		// This block does not support the following attributes.
		this.props.setAttributes( {
			lightbox: undefined,
			lightboxStyle: undefined,
			shadow: undefined,
		} );
	}

	componentDidUpdate( prevProps ) {
		// Deselect images when deselecting the block.
		if ( ! this.props.isSelected && prevProps.isSelected ) {
			this.setState( {
				selectedImage: null,
			} );
		}
	}

	onSelectImage( index ) {
		return () => {
			if ( this.state.selectedImage !== index ) {
				this.setState( {
					selectedImage: index,
				} );
			}
		};
	}

	onRemoveImage( index ) {
		return () => {
			const images = filter(
				this.props.attributes.images,
				( img, i ) => index !== i
			);
			this.setState( { selectedImage: null } );
			this.props.setAttributes( {
				images,
			} );
		};
	}

	setImageAttributes( index, attributes ) {
		const {
			attributes: { images },
			setAttributes,
		} = this.props;
		if ( ! images[ index ] ) {
			return;
		}
		setAttributes( {
			images: [
				...images.slice( 0, index ),
				{
					...images[ index ],
					...attributes,
				},
				...images.slice( index + 1 ),
			],
		} );
	}

	onItemClick() {
		if ( ! this.props.isSelected ) {
			this.props.onSelect();
		}
	}

	render() {
		const {
			attributes,
			className,
			isSelected,
			noticeUI,
			setAttributes,
		} = this.props;

		const {
			align,
			images,
		} = attributes;

		const dropZone = (
			<GalleryDropZone
				{ ...this.props }
				// translators: %s: Lowercase block title
				label={ sprintf( __( 'Drop to add to the %s' ), title.toLowerCase() ) }
			/>
		);

		const wrapperClasses = classnames(
			className,
			{
				[ `align${ align }` ]: align,
				'is-selected': isSelected,
			}
		);

		if ( images.length === 0 ) {
			return (
				<GalleryPlaceholder
					{ ...this.props }
					// translators: %s: Block title
					label={ sprintf( __( '%s' ), title ) }
					icon={ icon }
				/>
			);
		}

		return (
			<Fragment>
				<GalleryToolbar { ...this.props } />
				{ noticeUI }
				<div className="funkhaus-gallery-wrapper">
					{ dropZone }
					<ul className={ wrapperClasses }>
						{ images.map( ( img, index ) => {
							// translators: %1$d is the order number of the image, %2$d is the total number of images
							const ariaLabel = sprintf( 'image %1$d of %2$d in gallery', index + 1, images.length );

							return (
								<li className="blockfunkhaus--item" key={ img.id || img.url } onClick={ this.onItemClick } >
									<GalleryImage
										url={ img.url }
										alt={ img.alt }
										id={ img.id }
										marginRight={ true }
										marginLeft={ true }
										isSelected={
											isSelected && this.state.selectedImage === index
										}
										onRemove={ this.onRemoveImage( index ) }
										onSelect={ this.onSelectImage( index ) }
										setAttributes={ ( attrs ) =>
											this.setImageAttributes( index, attrs )
										}
										aria-label={ ariaLabel }
									/>
								</li>
							);
						} ) }
						{ /* { isSelected && (
							<GalleryUpload
								{ ...this.props }
								marginRight={ true }
								marginLeft={ true }
							/>
						) } */ }
					</ul>
				</div>
			</Fragment>
		);
	}
}

export default compose( [
	withNotices,
] )( Edit );
