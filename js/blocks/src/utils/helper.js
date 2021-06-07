/**
 * External dependencies
 */
import pick from 'lodash/pick';
import get from 'lodash/get';

export const pickRelevantMediaFiles = ( image ) => {
	const imageProps = pick( image, [ 'alt', 'id', 'link', 'caption' ] );
	imageProps.url = get( image, [ 'sizes', 'large', 'url' ] ) || get( image, [ 'media_details', 'sizes', 'large', 'source_url' ] ) || image.url;
	return imageProps;
};

export const ALLOWED_MEDIA_TYPES = [ 'image' ];
