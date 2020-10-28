<?php
/*
 * Register custom API endpoints
 */
function add_fuxt_api_routes()
{
    // Sitemap
    register_rest_route('fuxt', '/sitemap', [
        [
            'methods' => 'GET',
            'callback' => 'fuxt_sitemap_get',
            'permission_callback' => '__return_true',
        ],
    ]);

}
add_action('rest_api_init', 'add_fuxt_api_routes');


 /**
 * GET an array of all site URLs. Used to generate a sitemap.
 *
 * @return array of URLs for all pages of the site
 */
function fuxt_sitemap_get()
{
    $args = [
        'post_type'        => ['post', 'page'],
    	'orderby'          => 'parent menu_order',
    	'posts_per_page'   => -1,
    	'fields'		   => 'ids',
    	'post_status'	   => 'publish',
    ];
    $posts = get_posts($args);

    $urls = [];
    foreach($posts as $post_id) {
        $urls[] = wp_make_link_relative( get_permalink($post_id) );
    }

    return new WP_REST_Response( $urls, 200 );
}
