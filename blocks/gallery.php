<?php
add_action( 'init', function() {
    // Return early if this function does not exist.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
        'funkhaus/gallery',
        array(
            'editor_script' => 'funkhaus-block-editor',
            'editor_style'  => 'funkhaus-block-editor',
        )
    );
} );

add_action( 'graphql_register_types', function() {
    // Return early if this function does not exist.
    if ( ! function_exists( 'register_graphql_connection' ) ) {
		return;
	}

    register_graphql_connection([
        'fromType' => 'FunkhausGalleryBlock',
        'toType' => 'MediaItem',
        'fromFieldName' => 'mediaItems',
        'resolve' => function( $source, $args, $context, $info ) {
            // Instantiate a new PostObjectConnectionResolver class
            $resolver = new \WPGraphQL\Data\Connection\PostObjectConnectionResolver( $source, $args, $context, $info, 'attachment' );

            // Set the argument that will be passed to WP_Query. We want only Posts (of any post type) that are tagged with this Tag's ID
            if ( ! empty( $source['attributes']['images'] ) ) {
                $ids = array_map(function($image) {
                    return $image['id'];
                }, $source['attributes']['images']);
            } else {
                $ids = [];
            }

            $resolver->set_query_arg( 'post__in', $ids );

            // Return the connection
            return $resolver->get_connection();
        }
    ]);
} );