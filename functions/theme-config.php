<?php
/*
 * Setup image sizes that WordPress should generate on the server.
 */
	function custom_image_sizes(){
		// Enable post thumbnail support
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 960, 540, false ); // Normal post thumbnails
		add_image_size( 'social-preview', 600, 315, true ); // Square thumbnail used by sharethis and facebook
		add_image_size( 'medium-preview', 1280, 720, false ); // Square thumbnail used by sharethis and facebook
		add_image_size( 'fullscreen', 1920, 1080, false ); // Fullscreen image size
	}
	add_action( 'after_setup_theme', 'custom_image_sizes' );

/**
 * Registers a settings text field setting for Wordpress 4.7 and higher.
 **/
	function register_my_setting() {
	    $args = array(
	            'type' => 'string',
	            'sanitize_callback' => 'sanitize_text_field',
	            'default' => NULL,
	            'show_in_graphql' => true,
	            );
	    register_setting( 'my_options_group', 'my_option_name', $args );
	}
	add_action( 'admin_init', 'register_my_setting' );

/*
 * Setup menu locations
 */
    function register_custom_nav_menus() {
        register_nav_menus( array(
			// TODO: rename menus as locations: //header-menu, social-menu, footer-menu etc
            'header-menu'         => 'Main Menu'
        ));
    }
    add_action('after_setup_theme', 'register_custom_nav_menus');