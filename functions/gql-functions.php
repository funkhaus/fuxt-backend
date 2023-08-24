<?php
/**
 * Misc Graph QL functions, mostly filters used to extend the Schema
 *
 * @package fuxt-backend
 */

/**
 * Expose general settings() to WP-GQL API
 */
function whitelist_settings() {
	// Define a field to get Theme screenshot URL
	register_graphql_field(
		'GeneralSettings',
		'themeScreenshotUrl',
		array(
			'type'        => 'String',
			'description' => __( 'URL to the active theme screenshot', 'fuxt' ),
			'resolve'     => function ( $root, $args, $context, $info ) {
				$theme = wp_get_theme();
				$url   = '';
				if ( $theme->screenshot ) {
					$url = get_theme_file_uri( $theme->screenshot );
				}
				return $url;
			},
		)
	);

	// Define a fields to get both WordPress URLs
	register_graphql_field(
		'GeneralSettings',
		'backendUrl',
		array(
			'type'        => 'String',
			'description' => __( 'WordPress Address (URL)', 'fuxt' ),
			'resolve'     => function ( $root, $args, $context, $info ) {
				return get_site_url();
			},
		)
	);
	register_graphql_field(
		'GeneralSettings',
		'frontendUrl',
		array(
			'type'        => 'String',
			'description' => __( 'Site Address (URL)', 'fuxt' ),
			'resolve'     => function ( $root, $args, $context, $info ) {
				return get_home_url();
			},
		)
	);
}
add_action( 'graphql_init', 'whitelist_settings', 1 );

/**
 * Give media items a `html` field that outputs the SVG element or an IMG element.
 * SEE https://github.com/wp-graphql/wp-graphql/issues/1035
 */
function fuxt_add_media_element() {
	// Add content field for media item
	register_graphql_field(
		'mediaItem',
		'element',
		array(
			'type'    => 'String',
			'resolve' => function ( $source, $args ) {
				// phpcs:ignore
				if ( $source->mimeType === 'image/svg+xml' ) {
					$media_file = get_attached_file( $source->ID );
					if ( $media_file ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
						$svg_file_content = file_get_contents( $media_file );

						$find_string = '<svg';
						$position    = strpos( $svg_file_content, $find_string );

						return trim( substr( $svg_file_content, $position ) );
					} else {
						return 'File is missing';
					}
				} else {
					return wp_get_attachment_image( $source->ID, 'full' );
				}
			},
		)
	);
}
add_action( 'graphql_register_types', 'fuxt_add_media_element' );

/**
 * Give each content node a field of HTML encoded to play nicely with wp-content Vue component
 * SEE https://github.com/wp-graphql/wp-graphql/issues/1035
 */
function add_encoded_content_field() {
	register_graphql_field(
		'NodeWithContentEditor',
		'encodedContent',
		array(
			'type'    => 'String',
			'resolve' => function ( $post ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$content = get_post( $post->databaseId )->post_content;
				return ! empty( $content ) ? apply_filters( 'the_content', $content ) : null;
			},
		)
	);
}
add_action( 'graphql_register_types', 'add_encoded_content_field' );

/**
 * Make menus publicly accessible.
 *
 * @param bool   $is_private   If the data is private.
 * @param string $model_name   Name of the model the filter is currently being executed in.
 * @return bool
 */
function enable_public_menus( $is_private, $model_name ) {
	if ( 'MenuObject' === $model_name || 'MenuItemObject' === $model_name ) {
		return false;
	}
	return $is_private;
}
add_filter( 'graphql_data_is_private', 'enable_public_menus', 10, 2 );

/**
 * This allows any frontend domain to access the GQL endpoint. This mirros how WP-JSON API works.
 * SEE https://developer.wordpress.org/reference/functions/rest_send_cors_headers/
 * SEE https://github.com/funkhaus/wp-graphql-cors/blob/master/includes/process-request.php
 *
 * @param array $headers Array of headers to filter.
 * @return array
 */
function set_wpgql_cors_response_headers( $headers ) {
	// Abort if using Wp-GQL_CORS plugin for headers instead
	if ( class_exists( 'WP_GraphQL_CORS' ) ) {
		return $headers;
	}

	$origin = get_http_origin();

	// Cors protection check.
	$graphql_settings = get_option( 'graphql_general_settings' );
	if ( isset( $graphql_settings['restrict_gql_endpoint_cors'] ) && 'on' === $graphql_settings['restrict_gql_endpoint_cors'] ) {
		// Set site url as allowed origin.
		$allowed_origins = array(
			site_url(),
		);

		// Add fuxt home url to allowed origin.
		$fuxt_home_url = get_option( 'fuxt_home_url' );
		if ( $fuxt_home_url ) {
			$allowed_origins[] = $fuxt_home_url;
		}

		$allowed_origins = apply_filters( 'fuxt_allowed_origins', $allowed_origins );

		// Consider localhost case.
		$parsed_origin = wp_parse_url( $origin );

		if ( ! in_array( $origin, $allowed_origins, true ) && 'localhost' !== $parsed_origin['host'] ) {
			$origin = $allowed_origins[0];
		}
	}

	$headers['Access-Control-Allow-Origin']      = $origin;
	$headers['Access-Control-Allow-Credentials'] = 'true';

	// Allow certain header types. Respect the defauls from WP-GQL too.
	$access_control_allow_headers            = apply_filters(
		'graphql_access_control_allow_headers',
		array( 'Authorization', 'Content-Type', 'Preview' )
	);
	$headers['Access-Control-Allow-Headers'] = implode(
		', ',
		$access_control_allow_headers
	);

	return $headers;
}
add_filter( 'graphql_response_headers_to_send', 'set_wpgql_cors_response_headers' );

/**
 * Register Restrict GraphQL endpoint access setting.
 */
function restrict_gql_endpoint_cors_settings_field() {
	$name    = 'restrict_gql_endpoint_cors';
	$section = 'graphql_general_settings';

	$options = get_option( $section );
	$value   = isset( $options[ $name ] ) ? $options[ $name ] : '';

	$args = array(
		'name'    => $name,
		'section' => $section,
		'value'   => $value,
		'desc'    => __( 'Restrict GraphQL endpoint access to localhost, Site URL and Home URLs only', 'fuxt' ),
	);

	add_settings_field(
		"{$section}[{$name}]",
		__( 'Restrict GraphQL endpoint access to localhost, Site URL and Home URLs only', 'fuxt' ),
		'restrict_gql_endpoint_cors_field',
		$section,
		$section,
		$args
	);
}
add_action( 'admin_init', 'restrict_gql_endpoint_cors_settings_field', 11 );

/**
 * Restrict GraphQL endpoint access setting field markup.
 *
 * @param array $args Arguments for markup.
 */
function restrict_gql_endpoint_cors_field( array $args ) {
	$html  = '<fieldset>';
	$html .= sprintf( '<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['name'] );
	$html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off">', $args['section'], $args['name'] );
	$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s>', $args['section'], $args['name'], checked( $args['value'], 'on', false ) );
	$html .= sprintf( '%1$s</label>', $args['desc'] );
	$html .= '</fieldset>';
	echo $html; // phpcs:ignore
}

/**
 * Adds next post node to all the custom Post Types
 */
function gql_register_next_post() {
	$post_types = WPGraphQL::get_allowed_post_types();

	if ( ! empty( $post_types ) && is_array( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			// Get the Type name with ucfirst
			$ucfirst = ucfirst( $post_type_object->graphql_single_name );

			// Register a new Edge Type
			register_graphql_type(
				'Next' . $ucfirst . 'Edge',
				array(
					'fields' => array(
						'node' => array(
							'description' => __(
								'The node of the next item',
								'fuxt'
							),
							'type'        => $ucfirst,
							'resolve'     => function ( $post_id, $args, $context ) {
								return $context->get_loader( 'post' )->load_deferred( $post_id );
							},
						),
					),
				)
			);

			// Register the next{$type} field
			register_graphql_field(
				$ucfirst,
				'next' . $ucfirst,
				array(
					'type'        => 'Next' . $ucfirst . 'Edge',
					'description' => __(
						'The next post of the current port',
						'fuxt'
					),
					'args'	=> array(
						'inSameTerm'    => array(
							'type'        => 'Boolean',
							'description' => __( 'Whether post should be in a same taxonomy term. Default value: false', 'fuxt' ),
							'default'     => false,
						),
						'taxonomy'      => array(
							'type'        => 'String',
							'description' => __( 'Taxonomy, if inSameTerm is true', 'fuxt' ),
							'default'	=> 'category'
						),
						'termNotIn'     => array(
							'type'        => 'String',
							'description' => __( 'Comma-separated list of excluded term IDs.', 'fuxt' ),
						),
						'termSlugNotIn' => array(
							'type'        => 'String',
							'description' => __( 'Comma-separated list of excluded term slugs.', 'fuxt' ),
					),
						'loop'    => array(
							'type'        => 'Boolean',
							'description' => __( 'Whether to return boundary post if on first or last. Default value: true', 'fuxt' ),
							'default'     => true,
						),						
					),
					'resolve'     => function ( $post_model, $args, $context ) {
						$post = get_post( $post_model->ID );
						$loop = $args['loop'] ?? true;
						$post_type = get_post_type($post);
						$is_is_post_type_hierarchical = is_post_type_hierarchical($post_type);	

						if($is_is_post_type_hierarchical) {
							return fuxt_get_next_prev_page($post, true, $loop);							
						}

						// Assuming is a "post" type now
						$in_same_term = $args['inSameTerm'] ?? false;
						$taxonomy = $args['taxonomy'] ?? 'category';
						$excluded_term_ids = $args['termNotIn'] ?? '';
						$excluded_term_slugs = $args['termSlugNotIn'] ?? '';
						return fuxt_get_next_prev_post($post, true, $loop, $in_same_term, $excluded_term_ids, $excluded_term_slugs, $taxonomy);
					},
				)
			);
		}
	}
}
add_action( 'graphql_register_types', 'gql_register_next_post' );

/**
 * Adds previous post node to all the custom Post Types
 */
function gql_register_previous_post() {
	$post_types = WPGraphQL::get_allowed_post_types();

	if ( ! empty( $post_types ) && is_array( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			// Get the Type name with ucfirst
			$ucfirst = ucfirst( $post_type_object->graphql_single_name );

			// Register a new Edge Type
			register_graphql_type(
				'Previous' . $ucfirst . 'Edge',
				array(
					'fields' => array(
						'node' => array(
							'description' => __(
								'The node of the previous item',
								'fuxt'
							),
							'type'        => $ucfirst,
							'resolve'     => function ( $post_id, $args, $context ) {
								return $context->get_loader( 'post' )->load_deferred( $post_id );
							},
						),
					),
				)
			);

			// Register the next{$type} field
			register_graphql_field(
				$ucfirst,
				'previous' . $ucfirst,
				array(
					'type'        => 'Previous' . $ucfirst . 'Edge',
					'description' => __(
						'The previous post of the current post',
						'fuxt'
					),
					'args' => array(
						'inSameTerm'    => array(
							'type'        => 'Boolean',
							'description' => __( 'Whether post should be in a same taxonomy term. Default value: false', 'fuxt' ),
							'default'     => false,
						),
						'taxonomy'      => array(
							'type'        => 'String',
							'description' => __( 'Taxonomy, if inSameTerm is true', 'fuxt' ),
							'default'	=> 'category'
						),
						'termNotIn'     => array(
							'type'        => 'String',
							'description' => __( 'Comma-separated list of excluded term IDs.', 'fuxt' ),
						),
						'termSlugNotIn' => array(
							'type'        => 'String',
							'description' => __( 'Comma-separated list of excluded term slugs.', 'fuxt' ),
						),
						'loop'    => array(
							'type'        => 'Boolean',
							'description' => __( 'Whether to return boundary post if on first or last. Default value: true', 'fuxt' ),
							'default'     => true,
						),						
					),
					'resolve'     => function ( $post_model, $args, $context ) {
						$post = get_post( $post_model->ID );
						$loop = $args['loop'] ?? true;
						$post_type = get_post_type($post);						
						$is_is_post_type_hierarchical = is_post_type_hierarchical($post_type);

						if($is_is_post_type_hierarchical) {
							return fuxt_get_next_prev_page($post, false, $loop);
						}

						// Assuming is a "post" type now
						$in_same_term = $args['inSameTerm'] ?? false;
						$taxonomy = $args['taxonomy'] ?? 'category';
						$excluded_term_ids = $args['termNotIn'] ?? '';
						$excluded_term_slugs = $args['termSlugNotIn'] ?? '';
						return fuxt_get_next_prev_post($post, false, $loop, $in_same_term, $excluded_term_ids, $excluded_term_slugs, $taxonomy);
					},
				)
			);
		}
	}
}
add_action( 'graphql_register_types', 'gql_register_previous_post' );


/**
 * Get the next/previous hierarchical post type (eg: Pages)
 */
function fuxt_get_next_prev_page($post, $is_next = true, $loop = true) {
	// Get all siblings pages
	// Yes this is isn't effienct to query all pages, 
	// but actually it works well for thousands of pages in practice. 
	$args = array(
		'post_type' => get_post_type($post),
		'posts_per_page'	=> -1,
		'fields'			=> 'ids',
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'post_parent'		=> $post->post_parent
	);
	
	// Get all siblings
	$siblings = get_posts($args);
	
	// Find where current posts exists in Siblings
	$index = array_search($post->ID, $siblings);
	
	if($is_next) {
		$on_last = $index == count($siblings)-1;

		// If on last element, then return first, or null if not looping 
		if( $loop && $on_last ) {
			return $siblings[0];
		} elseif(!$loop && $on_last) {
			return null;
		}
		
		// Get next
		return $siblings[$index+1];
		
	} else {
		$on_first = $index == 0;
		
		// If on first, then return last, or null if not looping
		if( $loop && $on_first ) {
			return end($siblings);
		} elseif( !$loop && $on_first) {
			return null;
		}
		
		// Get previous
		return $siblings[$index-1];
	}
	
}

/**
 * Get the next/previous date-based post type (eg: Posts)
 */
function fuxt_get_next_prev_post(
	$cur_post, 
	$is_next = true, 
	$loop = true, 
	$in_same_term = false, 
	$excluded_term_ids = '', 
	$excluded_term_slugs = '', 
	$taxonomy = 'category'
) {
	global $post;
	$post = $cur_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );

	// Get IDs of slugs, and merge with any IDs we were given as args
	if ( ! empty( $excluded_term_slugs ) ) {
		$slugs_as_ids = array_map(
			function( $slug ) use ( $taxonomy ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( $term ) {
					return $term->term_id;
				}
				return false;
			},
			$excluded_term_slugs
		);

		$excluded_term_ids = array_merge( $excluded_term_ids, array_filter( $slugs_as_ids ) );
	}

	$next_prev_id = null;

	// Return the adjacent post
	$adjacent_post = get_adjacent_post($in_same_term, $excluded_term_ids, !$is_next, $taxonomy);
	if( !empty($adjacent_post) ) {
		$next_prev_id = $adjacent_post->ID;
	} else if( $loop ) {
		// If looping, and we won't have $adjacent_post above so get boundry post now
		$boundary_post = get_boundary_post_custom($in_same_term, $excluded_term_ids, $is_next, $taxonomy);
		$next_prev_id  = $boundary_post[0]->ID ?? null;
	}

	wp_reset_postdata();

	return $next_prev_id;
}

/**
 * Retrieves the boundary post.
 *
 * Boundary being either the first or last post by publish date within the constraints specified
 * by `$in_same_term` or `$excluded_terms`.
 *
 * @since 2.8.0
 *
 * @param bool         $in_same_term   Optional. Whether returned post should be in the same taxonomy term.
 *                                     Default false.
 * @param int[]|string $excluded_terms Optional. Array or comma-separated list of excluded term IDs.
 *                                     Default empty.
 * @param bool         $start          Optional. Whether to retrieve first or last post.
 *                                     Default true.
 * @param string       $taxonomy       Optional. Taxonomy, if `$in_same_term` is true. Default 'category'.
 * @return array|null Array containing the boundary post object if successful, null otherwise.
 */
function get_boundary_post_custom( $in_same_term = false, $excluded_terms = '', $start = true, $taxonomy = 'category' ) {
	$post = get_post();

	if ( ! $post || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$query_args = array(
		'posts_per_page'         => 1,
		'order'                  => $start ? 'ASC' : 'DESC',
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	$term_array = array();

	if ( ! is_array( $excluded_terms ) ) {
		if ( ! empty( $excluded_terms ) ) {
			$excluded_terms = explode( ',', $excluded_terms );
		} else {
			$excluded_terms = array();
		}
	}

	if ( $in_same_term || ! empty( $excluded_terms ) ) {
		if ( $in_same_term ) {
			$term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		}

		if ( ! empty( $excluded_terms ) ) {
			$excluded_terms = array_map( 'intval', $excluded_terms );
			$excluded_terms = array_diff( $excluded_terms, $term_array );

			$inverse_terms = array();
			foreach ( $excluded_terms as $excluded_term ) {
				$inverse_terms[] = $excluded_term * -1;
			}
			$excluded_terms = $inverse_terms;
		}

		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'terms'    => array_merge( $term_array, $excluded_terms ),
			),
		);
	}

	return get_posts( $query_args );
}

/**
 * Set the default ordering of quieres in WP-GQL
 *
 * @param array $query_args The args that will be passed to the WP_Query.
 * @return array
 */
function custom_default_where_args( $query_args ) {
	$post_types = $query_args['post_type'];
	$gql_args   = $query_args['graphql_args'];

	if ( isset( $gql_args['where'] ) ) {
		// Where args set, so use them
		return $query_args;
	} elseif (
		is_array( $post_types ) &&
		count( $post_types ) === 1 &&
		in_array( 'post', $post_types, true )
	) {
		// Is just Posts, so use defaults
		return $query_args;
	}

	// Is anything else, so set to menu_order
	$query_args['orderby'] = 'menu_order';
	$query_args['order']   = 'ASC';
	return $query_args;
}
add_filter( 'graphql_post_object_connection_query_args', 'custom_default_where_args', 10, 1 );

/**
 * Set the default max query result amount (default is 100)
 *
 * @return int
 */
function custom_max_query_amount( $amount, $source, $args, $context, $info ) {
    return 1000;
}
add_filter( 'graphql_connection_max_query_amount', 'custom_max_query_amount', 10, 5 );