<?php
/*
 * Allow WP Graph QL to request meta from any post type
 */
    // function register_gql_meta_strings() {
    //     $post_types = WPGraphQL::$allowed_post_types;
    //
    //     // These all need to be strings
    //     $meta_keys = array(
    //         'custom_developer_id'   => ''
    //     )
    //
    //     // Loop thropugh all posts types and register meta keys from white list above
    //     if ( ! empty( $post_types ) && is_array( $post_types ) ) {
    //         foreach ( $post_types as $post_type ) {
    //             $post_type_object = get_post_type_object( $post_type );
    //
    //             foreach($meta_keys as $meta_key) {
    //                 register_graphql_field( $post_type_object->graphql_single_name, $meta_key, [
    //                     'type'        => 'String',
    //                     'description' => __( 'Meta for the key '.$meta_key. ' as a string', 'wp-graphql' ),
    //                     'resolve'     => function( $post ) {
    //                         $meta = get_post_meta( $post->ID, $meta_key, true );
    //                         return ! empty( $meta ) ? $meta : '';
    //                     }
    //                 ]);
    //             }
    //         }
    //     }
    // }
    // add_action( 'graphql_register_types', 'register_gql_meta_strings');
