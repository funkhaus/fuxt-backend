/**
 * Internal dependencies
 */
import * as helper from '../utils/helper';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	Button,
	Toolbar,
} = wp.components;
const {
	BlockControls,
	MediaUpload,
	MediaUploadCheck,
} = wp.blockEditor;

/**
 * Global Toolbar Component
 */
class GalleryToolbar extends Component {
	constructor() {
		super( ...arguments );
		this.onSelectImages = this.onSelectImages.bind( this );
	}

	onSelectImages( images ) {
		this.props.setAttributes( {
			images: images.map( ( image ) => helper.pickRelevantMediaFiles( image ) ),
		} );
	}

	render() {
		const {
			attributes,
			isSelected,
			setAttributes,
		} = this.props;

		const {
			images,
		} = attributes;

		return (
			isSelected && (
				<Fragment>
					<BlockControls>
						{ !! images.length && (
							<Toolbar label="edit">
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ this.onSelectImages }
										allowedTypes={ helper.ALLOWED_MEDIA_TYPES }
										multiple
										gallery
										value={ images.map( ( img ) => img.id ) }
										render={ ( { open } ) => (
											<Button
												className="components-toolbar__control"
												label={ __( 'Edit Gallery' ) }
												icon="edit"
												onClick={ open }
											/>
										) }
									/>
								</MediaUploadCheck>
							</Toolbar>
						) }
					</BlockControls>
				</Fragment>
			)
		);
	}
}

export default GalleryToolbar;
