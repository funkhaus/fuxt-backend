<?php
/**
 * Misc WordPress functions.
 *
 * @package fuxt-backend
 */

/**
 * Custom WP setup.
 */
function custom_wordpress_setup() {
	// Enable tags for Pages
	register_taxonomy_for_object_type( 'post_tag', 'page' );

	// Enable excerpts for pages
	add_post_type_support( 'page', 'excerpt' );

	// Disable the hiding of big images
	add_filter( 'big_image_size_threshold', '__return_false' );
	add_filter( 'max_srcset_image_width', '__return_false' );
}
add_action( 'init', 'custom_wordpress_setup' );

/**
 * Setup theme
 */
function custom_theme_setup() {
	// Turn on menus
	add_theme_support( 'menus' );

	// Enable HTML5 support
	add_theme_support( 'html5', array( 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'custom_theme_setup' );

/**
 * Enqueue custom Admin Scripts
 */
function custom_admin_scripts() {
	wp_enqueue_script( 'fuxt-admin', get_template_directory_uri() . '/js/admin.js', array( 'jquery' ), '1.1', true );
}
add_action( 'admin_enqueue_scripts', 'custom_admin_scripts' );

/**
 * Style login page and dashboard
 *
 * @param string $url Default login header url.
 * @return string
 */
function custom_loginpage_logo_link( $url ) {
	// Return a url; in this case the homepage url of WordPress
	return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'custom_loginpage_logo_link' );

/**
 * Custom login header text.
 *
 * @param string $message Default login header text.
 * @return string
 */
function custom_loginpage_logo_title( $message ) {
	// Return title text for the logo to replace 'WordPress'; in this case, the blog name.
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'custom_loginpage_logo_title' );

/**
 * Enqueue Custom login css.
 */
function custom_loginpage_styles() {
	wp_enqueue_style(
		'login_css',
		get_template_directory_uri() . '/css/login.css',
		null,
		FUXT_VERSION
	);
}
add_action( 'login_head', 'custom_loginpage_styles' );

/**
 * Enqueue Admin CSS.
 */
function custom_admin_styles() {
	wp_enqueue_style(
		'admin-stylesheet',
		get_template_directory_uri() . '/css/admin.css',
		null,
		FUXT_VERSION
	);
}
add_action( 'admin_print_styles', 'custom_admin_styles' );

/**
 * Add custom favicon.
 */
function custom_site_favicon() {
	echo '<link rel="shortcut icon" href="' . esc_url( get_stylesheet_directory_uri() . '/favicon.png' ) . '" />';
}
add_action( 'admin_head', 'custom_site_favicon' );
add_action( 'login_head', 'custom_site_favicon' );

/**
 * Add post thumbnail into RSS feed.
 *
 * @param string $content Post content.
 * @return string
 */
function rss_post_thumbnail( $content ) {
	global $post;

	if ( has_post_thumbnail( $post->ID ) ) {
		$content =
			'<p><a href=' .
			get_permalink( $post->ID ) .
			'>' .
			get_the_post_thumbnail( $post->ID ) .
			'</a></p>' .
			$content;
	}

	return $content;
}
add_filter( 'the_excerpt_rss', 'rss_post_thumbnail' );

/**
 * Change the [...] that comes after excerpts.
 *
 * @param string $more Default more string.
 * @return string
 */
function custom_excerpt_ellipsis( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'custom_excerpt_ellipsis' );

/**
 * Change the word length of auto excerpts.
 *
 * @param int $length Default length.
 * @return int
 */
function fuxt_custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'fuxt_custom_excerpt_length', 99 );

/**
 * Prevent Google from indexing any PHP generated part of the API.
 */
function add_nofollow_header() {
	header( 'X-Robots-Tag: noindex, nofollow', true );
}
add_action( 'send_headers', 'add_nofollow_header' );

/**
 * Add useful args to post/page preview URLs
 *
 * @param string   $link URL used for the post preview.
 * @param \WP_Post $post Post object.
 * @return string
 */
function add_custom_preview_link( $link, $post ) {
	$args = array(
		'id'      => $post->ID,
		'type'    => get_post_type( $post ),
		'status'  => get_post_status( $post ),
		'preview' => 'true',
	);

	// Add slug and build path
	if ( $post->post_name ) {
		// Build out new Preview permalink
		if ( ! function_exists( 'get_sample_permalink' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		$link = get_sample_permalink( $post->ID )[0] ?? '';
		$link = str_replace(
			array( '%postname%', '%pagename%' ),
			$post->post_name,
			$link
		);

		$args['slug'] = $post->post_name;
		$args['uri']  = wp_make_link_relative( $link );
	}

	return add_query_arg( $args, $link );
}
add_filter( 'preview_post_link', 'add_custom_preview_link', 10, 2 );

/**
 * Includes preview link in post data for a response for Gutenberg preview link to work.
 *
 * @param \WP_REST_Response $response The response object.
 * @param \WP_Post          $post     Post object.
 * @return \WP_REST_Response
 */
function fuxt_preview_link_in_rest_response( $response, $post ) {
	if ( 'draft' === $post->post_status ) {
		$response->data['link'] = get_preview_post_link( $post );
	}

	return $response;
}

/**
* Add the custom preview link to posts, pages and all public custom post types
*/
function custom_filter_preview_links() {
    add_filter( 'rest_prepare_post', 'fuxt_preview_link_in_rest_response', 10, 2 );
    add_filter( 'rest_prepare_page', 'fuxt_preview_link_in_rest_response', 10, 2 );
    
    $args = array(
        'public'   => true,
        '_builtin' => false
    );
    $post_types = get_post_types($args, 'names', 'and');
    
    foreach($post_types as $post_type) {   
        add_filter( 'rest_prepare_'.$post_type, 'fuxt_preview_link_in_rest_response', 10, 2 );    
    }    
}
add_action( 'admin_init', 'custom_filter_preview_links');

/**
 * This function auto saves drafts posts, to force them to get a URL for previews to work.
 * See: https://wordpress.stackexchange.com/questions/218168/how-to-make-draft-posts-or-posts-in-review-accessible-via-full-url-slug
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post Post object.
 * @param bool     $update Whether this is an existing post being updated.
 */
function auto_set_post_status( $post_id, $post, $update ) {
	if ( $post->post_status === 'draft' && ! $post->post_name ) {
		// Un-hook to prevent infinite loop
		remove_action( 'save_post', 'auto_set_post_status', 13, 3 );

		if ( is_plugin_active( 'funkhaus-netlify-deploy/netlify-deploy.php' ) ) {
			remove_action( 'save_post', 'nd_debounce_deploy', 20, 1 );
		}

		if ( is_plugin_active( 'funkhaus-cache-purge/cache-purge.php' ) ) {
			remove_action( 'save_post', 'cp_purge_cache', 20, 1 );
		}

		// Set the post to publish so it gets the slug is saved to post_name
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
				'post_date'   => '',
			)
		);

		// Immediately put it back to draft status
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
				'post_date'   => '',
			)
		);

		// Re-hook save
		add_action( 'save_post', 'auto_set_post_status', 13, 3 );

		if ( is_plugin_active( 'funkhaus-netlify-deploy/netlify-deploy.php' ) ) {
			add_action( 'save_post', 'nd_debounce_deploy', 20, 1 );
		}

		if ( is_plugin_active( 'funkhaus-cache-purge/ cache-purge.php' ) ) {
			add_action( 'save_post', 'cp_purge_cache', 20, 1 );
		}
	}
}
add_action( 'save_post', 'auto_set_post_status', 13, 3 );

/**
 * Polyfill functions to not throw errors on ACF import
 */
function fuxt_polyfill_functions() {
	if ( ! function_exists( 'cp_purge_cache' ) ) {
		// phpcs:ignore
		function cp_purge_cache(){}
	}
	if ( ! function_exists( 'nd_debounce_deploy' ) ) {
		// phpcs:ignore
		function nd_debounce_deploy(){}
	}
}
add_action( 'wp_dashboard_setup', 'fuxt_polyfill_functions' );

/**
 * Set permlinks on theme activate
 * SEE https://github.com/wp-graphql/wp-graphql/issues/1612
 */
function set_custom_permalinks() {
	$current_setting = get_option( 'permalink_structure' );

	// Abort if already saved to something else
	if ( $current_setting ) {
		return;
	}

	// Save permalinks to a custom setting, force create of rules file
	global $wp_rewrite;
	update_option( 'rewrite_rules', false );
	$wp_rewrite->set_permalink_structure( '/news/p/%postname%/' );
	$wp_rewrite->set_category_base( '/news/c/' );
	$wp_rewrite->flush_rules( true );
}
add_action( 'after_switch_theme', 'set_custom_permalinks' );

/**
 * Strip quotes from oEmbed title html attributes
 *
 * @param string $return The returned oEmbed HTML.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return string
 */
function filter_oembed_attributes( $return, $data, $url ) {
	// Remove the title attribute, as often times it has a quote in it.
	$return = preg_replace( '/title="[\\s\\S]*?"/', '', $return );

	// Strip quotes from title
	$title = str_replace( '"', '', $data->title || '' );

	return str_replace( '<iframe', '<iframe title="' . $title . '"', $return );
}
add_filter( 'oembed_dataparse', 'filter_oembed_attributes', 10, 4 );

/**
 * Update fuxt_home_url option when Site Address(home) is updated.
 * Normally you'd use `update_option_home` here but I guess Flywheel disabled.
 *
 * @param string $option    Name of the option to update.
 * @param mixed  $old_value The old option value.
 * @param mixed  $new_value The new option value.
 */
function fuxt_update_home_url( $option, $old_value, $new_value ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['home'] ) ) {
		// Remove filter to not cause infinte loop
		remove_action( 'update_option', 'fuxt_update_home_url', 20, 3 );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_option( 'fuxt_home_url ', $_POST['home'], true );
	}
}
add_action( 'update_option', 'fuxt_update_home_url', 20, 3 );

/**
 * Return the fuxt_home_url value when code requests the Site Address (URL)
 *
 * @param string      $url         The complete home URL including scheme and path.
 * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative', 'rest', or null.
 * @return string
 */
function fuxt_get_home_url( $url, $path, $orig_scheme ) {
	if ( 'rest' !== $orig_scheme ) {
		$fuxt_home_url = get_option( 'fuxt_home_url' );
	} else {
		$fuxt_home_url = get_option( 'siteurl' );
	}

	if ( ! empty( $fuxt_home_url ) ) {
		$url = untrailingslashit( $fuxt_home_url );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}
	}

	return $url;
}
add_filter( 'home_url', 'fuxt_get_home_url', 99, 3 );

/**
 * Update the Site Address value in General Settings panel to return fuxt override
 *
 * @param string $value Site address value.
 * @return string
 */
function fuxt_filter_home_option( $value ) {
	global $pagenow;
	if ( $pagenow === 'options-general.php' ) {
		$value = get_option( 'fuxt_home_url' );
	}
	return $value;
}
add_filter( 'option_home', 'fuxt_filter_home_option', 99, 1 );

/**
 * Whitelist siteurl for wp_safe_redirect.
 *
 * @param string[] $hosts An array of allowed host names.
 * @return string[]
 */
function fuxt_allow_siteurl_safe_redirect( $hosts ) {
	$wpp = wp_parse_url( site_url() );
	return array_merge( $hosts, array( $wpp['host'] ) );
}
add_filter( 'allowed_redirect_hosts', 'fuxt_allow_siteurl_safe_redirect' );
