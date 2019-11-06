<?php
/*
 * Setup WordPress
 */
    function custom_wordpress_setup() {

        // Enable tags for Pages (@see: https://wordpress.org/support/topic/enable-tags-screen-for-pages#post-29500520
        //register_taxonomy_for_object_type('post_tag', 'page');

        // Enable excerpts for pages
        add_post_type_support('page', 'excerpt');

    }
    add_action('init', 'custom_wordpress_setup');


/*
 * Setup theme
 */
    function custom_theme_setup() {

	    // Turn on menus
		add_theme_support('menus');

		// Enable HTML5 support
		add_theme_support('html5');

	}
	add_action( 'after_setup_theme', 'custom_theme_setup' );


/*
 * Enqueue any Custom Admin Scripts
 */
	function custom_admin_scripts() {
		wp_register_script('site-admin', get_template_directory_uri() . '/js/admin.js', 'jquery', "1.0");
		wp_enqueue_script('site-admin');
	}
	add_action( 'admin_enqueue_scripts', 'custom_admin_scripts' );


/*
 * Style login page and dashboard
 */
	// Style the login page
	function custom_loginpage_logo_link($url) {
	     // Return a url; in this case the homepage url of wordpress
	     return get_bloginfo('url');
	}
	function custom_loginpage_logo_title($message) {
	     // Return title text for the logo to replace 'wordpress'; in this case, the blog name.
	     return get_bloginfo('name');
	}
	function custom_loginpage_styles() {
        wp_enqueue_style('login_css', get_template_directory_uri() . '/css/login.css');
	}
	function custom_admin_styles() {
        wp_enqueue_style('admin-stylesheet', get_template_directory_uri() . '/css/admin.css');
	}
	add_filter('login_headerurl','custom_loginpage_logo_link');
	add_filter('login_headertext','custom_loginpage_logo_title');
	add_action('login_head','custom_loginpage_styles');
    add_action('admin_print_styles', 'custom_admin_styles');


/*
 * Add post thumbnail into RSS feed
 */
    function rss_post_thumbnail($content) {
        global $post;

        if( has_post_thumbnail($post->ID) ) {
            $content = '<p><a href='.get_permalink($post->ID).'>'.get_the_post_thumbnail($post->ID).'</a></p>'.$content;
        }

		return $content;
	}
	add_filter('the_excerpt_rss', 'rss_post_thumbnail');



/*
 * Allow SVG uploads
 */
    function add_mime_types($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
    //add_filter('upload_mimes', 'add_mime_types');


/*
 * Allow subscriber to see Private posts/pages
 */
	function add_theme_caps() {
	    // Gets the author role
	    $role = get_role('subscriber');

	    // Add capabilities
	    $role->add_cap('read_private_posts');
		$role->add_cap('read_private_pages');
	}
	//add_action( 'switch_theme', 'add_theme_caps');


/*
 * Change the [...] that comes after excerpts
 */
    function custom_excerpt_ellipsis( $more ) {
        return '...';
    }
    add_filter('excerpt_more', 'custom_excerpt_ellipsis');


/*
 * Add Google Analytics tracking settings to admin dashboard
 */
    function my_general_section() {
        add_settings_section(
            'sh_google_analytics_section',  // Section ID
            'Google Analytics Tracking IDs',        // Section Title
            'sh_google_analytics_section', // Callback
            'general'                      // This makes the section show up on the General Settings Page
        );

        add_settings_field(
            'ga_tracking_code_1',   // Option ID
            'Tracking ID #1',       // Label
            'sh_google_analytics_settings', // !important - This is where the args go!
            'general',                      // Page it will be displayed (General Settings)
            'sh_google_analytics_section',  // Name of our section
            array(
                'ga_tracking_code_1' // Should match Option ID
            )
        );

        add_settings_field(
            'ga_tracking_code_2',   // Option ID
            'Tracking ID #2',       // Label
            'sh_google_analytics_settings', // !important - This is where the args go!
            'general',                      // Page it will be displayed (General Settings)
            'sh_google_analytics_section',  // Name of our section
            array(
                'ga_tracking_code_2' // Should match Option ID
            )
        );
    }
    add_action('admin_init', 'my_general_section');


/*
 * Settings callbacks that build the Analytics markup
 */
    function sh_google_analytics_section() {
        echo '<p>Enter Google Analytics tracking codes. Uses the <code>gtag.js</code> tracking method.</p>';
    }

    function sh_google_analytics_settings($args) {
        $option = get_option($args[0]);
        echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" placeholder="UA-12345678-1"/>';
    }


/*
 * Add useful args to post/page preview URLs
 */
	function add_custom_preview_link($link, $post) {
		$args = array(
			"id"		=> $post->ID,
			"type"		=> get_post_type($post),
			"uri"		=> "/" . get_page_uri($post),
			"status"	=> get_post_status($post)
		);
		$link = add_query_arg($args, $link);

		return $link;
	}
	add_filter( 'preview_post_link', "add_custom_preview_link", 10, 2);
