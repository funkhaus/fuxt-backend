<?php
/*
 * Setup image sizes that WordPress should generate on the server.
 */
	function custom_image_sizes(){
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 960, 540, false );
		add_image_size( 'social-preview', 600, 315, true ); // Square thumbnail used by sharethis and facebook

		// You may want to change these, but these defaults cover most use cases
		add_image_size( 'small-preview', 375, 0, false );
		add_image_size( 'medium-preview', 960, 0, false );
		add_image_size( 'large-preview', 1280, 0, false );
		add_image_size( 'fullscreen-small', 1920, 0, false );
		add_image_size( 'fullscreen', 2560, 0, false );
		add_image_size( 'fullscreen-large', 3840, 0, false );
		add_image_size( 'fullscreen-xlarge', 6016, 0, false );		
	}
	add_action( 'after_setup_theme', 'custom_image_sizes' );
