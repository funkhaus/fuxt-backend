/**
 * External Dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { compose } = wp.compose;
const { Button, Spinner } = wp.components;
const { withSelect } = wp.data;
const { BACKSPACE, DELETE } = wp.keycodes;
const { isBlobURL } = wp.blob;

/**
 * Gallery Image Component
 */
class GalleryImage extends Component {
	constructor() {
		super( ...arguments );

		this.onImageClick = this.onImageClick.bind( this );
		this.onKeyDown = this.onKeyDown.bind( this );
		this.bindContainer = this.bindContainer.bind( this );
	}

	bindContainer( ref ) {
		this.container = ref;
	}

	onImageClick() {
		if ( ! this.props.isSelected ) {
			this.props.onSelect();
		}
	}

	onKeyDown( event ) {
		if (
			this.container === document.activeElement &&
			this.props.isSelected &&
			[ BACKSPACE, DELETE ].indexOf( event.keyCode ) !== -1
		) {
			event.stopPropagation();
			event.preventDefault();
			this.props.onRemove();
		}
	}

	componentDidUpdate() {
		const { image, url } = this.props;
		if ( image && ! url ) {
			this.props.setAttributes( {
				url: image.source_url,
				alt: image.alt_text,
			} );
		}
	}

	render() {
		const {
			alt,
			id,
			isSelected,
			link,
			linkTo,
			onRemove,
			setAttributes,
			url,
			'aria-label': ariaLabel,
		} = this.props;

		let href;

		switch ( linkTo ) {
			case 'media':
				href = url;
				break;
			case 'attachment':
				href = link;
				break;
		}

		const imgClasses = classnames( {} );

		// Disable reason: Image itself is not meant to be
		// interactive, but should direct image selection and unfocus caption fields
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions, jsx-a11y/click-events-have-key-events
		const img = (
			// Disable reason: Image itself is not meant to be interactive, but should
			// direct image selection and unfocus caption fields.
			/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
			<Fragment>
				<img
					src={ url }
					className={ imgClasses }
					alt={ alt }
					data-id={ id }
					onClick={ this.onImageClick }
					tabIndex="0"
					onKeyDown={ this.onImageClick }
					aria-label={ ariaLabel }
				/>
				{ isBlobURL( url ) && <Spinner /> }
			</Fragment>
			/* eslint-enable jsx-a11y/no-noninteractive-element-interactions */
		);

		const className = classnames( {
			'is-selected': isSelected,
			'is-transient': url && 0 === url.indexOf( 'blob:' ),
		} );

		// Disable reason: Each block can be selected by clicking on it and we should keep the same saved markup
		/* eslint-disable jsx-a11y/no-noninteractive-element-interactions, jsx-a11y/onclick-has-role, jsx-a11y/click-events-have-key-events */
		return (
			<figure
				className={ 'blockfunkhaus--figure ' + className }
				tabIndex="-1"
				onKeyDown={ this.onKeyDown }
				ref={ this.bindContainer }
			>
				{ isSelected && (
					<div className="components-blockfunkhaus-gallery-item__remove-wrapper">
						<Button
							icon="no-alt"
							onClick={ onRemove }
							className="components-blockfunkhaus-gallery-item__remove-button"
							label={ __( 'Remove Image' ) }
						/>
					</div>
				) }
				{ href ? <a href={ href }>{ img }</a> : img }
			</figure>
		);
		/* eslint-enable jsx-a11y/no-noninteractive-element-interactions, jsx-a11y/onclick-has-role, jsx-a11y/click-events-have-key-events */
	}
}

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { getMedia } = select( 'core' );
		const { id } = ownProps;

		return {
			image: id ? getMedia( id ) : null,
		};
	} ),
] )( GalleryImage );
