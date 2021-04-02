<?php
/*
 * Filter function to add missing attributes for image block.
 * srcSet, sizes, width, height, title, caption and description
 *
 * @param array attributes
 * @return array
 */
function ggb_alter_img_data( $attributes ) {
	$size = 'medium';
	if ( ! empty( $attributes['sizeSlug'] ) ) {
		$size = $attributes['sizeSlug'];
	}

	$image_post_id = $attributes['id'];

	$image = wp_get_attachment_image_src( $image_post_id, $size );
	if ( $image ) {
		list( $src, $width, $height ) = $image;

		// set sizes and srcSet
		$sizes                = wp_calculate_image_sizes( array( absint( $width ), absint( $height ) ), $src, null, $image_post_id );
		$attributes['sizes']  = ! empty( $sizes ) ? $sizes : null;
		$attributes['srcSet'] = wp_get_attachment_image_srcset( $image_post_id, $size );

		// reset width and height
		if ( empty( $attributes['width'] ) ) {
			$attributes['width'] = $width;
		}
		if ( empty( $attributes['height'] ) ) {
			$attributes['height'] = $height;
		}

		// set imgTitle, imgCaption and imgDescription
		$attachment = get_post( $image_post_id );

		$attributes['imgAlt']         = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
		$attributes['imgCaption']     = $attachment->post_excerpt;
		$attributes['imgDescription'] = $attachment->post_content;
		$attributes['imgTitle']       = $attachment->post_title;
	}

	return $attributes;
}
add_filter( 'ggb_attributes_core/image', 'ggb_alter_img_data' );

/**
 * Filter function to alter Gutenberg GraphQL fields
 *
 * @param array $registry
 * @return array
 */
function ggb_alter_img_fields( $registry ) {
	$registry['core/image']['attributes']['sizes']          = array( 'type' => 'string' );
	$registry['core/image']['attributes']['srcSet']         = array( 'type' => 'string' );
	$registry['core/image']['attributes']['imgAlt']         = array( 'type' => 'string' );
	$registry['core/image']['attributes']['imgCaption']     = array( 'type' => 'string' );
	$registry['core/image']['attributes']['imgDescription'] = array( 'type' => 'string' );
	$registry['core/image']['attributes']['imgTitle']       = array( 'type' => 'string' );
	return $registry;
}
add_filter( 'ggb_get_registry', 'ggb_alter_img_fields' );

/**
 * Filter function to whitelist blocks
 *
 * @param array $registry
 * @return array new registry value
 */
function ggb_whitelist_fields( $registry ) {
	$whitelist    = ggb_get_block_whitelist();
	$new_registry = array();
	foreach ( $whitelist as $item ) {
		if ( array_key_exists( $item, $registry ) ) {
			$new_registry[ $item ] = $registry[ $item ];
		}
	}

	return $new_registry;
}
add_filter( 'ggb_get_registry', 'ggb_whitelist_fields' );

/**
 * Gets block whitelist
 *
 * @return array
 */
function ggb_get_block_whitelist() {
	return array(
		'core/paragraph',
		'core/image',
		'core/heading',
		'core/gallery',
		'core/list',
		'core/quote',
		'core/columns',
		'core/column',
		'core/embed',
	);
}


if ( ! current_user_can( 'manage_options' ) ) {
	// whitelist blocks for editors
	add_filter( 'allowed_block_types', 'ggb_get_block_whitelist' );
}
