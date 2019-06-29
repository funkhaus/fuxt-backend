<?php
/*
 * Translate shortcode to Vue components.
 * Use a new copy of this function for each shortcode supported in wp-content.
 * See the Readme.md file in this directory for more examples.
 *
 * @see https://codex.wordpress.org/Function_Reference/add_shortcode
 */
	function custom_shortcode_function( $atts, $content = '', $name ) {

		// Include default props here
        extract(shortcode_atts(array(
			'title'         => ''
        ), $atts));

		// Props to pass to Vue component
        $props = 'title="' . $title . '"';
        $content = custom_filter_shortcode_text($content);

		return '<vue-component-name ' . $props . '>'. $content .'</vue-component-name>';
	}
	//add_shortcode( 'shortcode-name', 'custom_shortcode_function' );


/*
 * Gallery shortcode
 */
	function add_gallery_shortcode( $atts ) {

        extract(shortcode_atts(array(
			'ids'         => '',
			'columns'	  => 2
        ), $atts));

        // Get all images
        $images_ids = explode(',', $ids);
        $images = array();

        foreach($images_ids as $image_id) {
	        $images[] = custom_build_repsponsive_image($image_id);
        }

        return '<shortcode-gallery :columns="'. esc_attr(json_encode($columns)) .'" :images="'. esc_attr(json_encode($images)) .'"></shortcode-gallery>';
	}
	//add_shortcode( 'gallery', 'add_gallery_shortcode' );


/*
 * Creates a an [svg-image] shortcode so a user can add SVGs into the editor correctly
 */
	function add_svg_image_shortcode( $atts ) {

        extract(shortcode_atts(array(
			'name'         => ''
        ), $atts));

		return '<svg-'. $name .'></svg-'. $name .'>';
	}
	//add_shortcode( 'svg', 'add_svg_image_shortcode' );


/**
 * Utility function to clean up the way WordPress auto formats text in a shortcode.
 *
 * @param string $text A string of HTML text
 */
    function custom_filter_shortcode_text($text = '') {
        // Replace all the poorly formatted P tags that WP adds by default.
        $tags = array("<p>", "</p>");
        $text = str_replace($tags, "\n", $text);

        // Remove any BR tags
        $tags = array("<br>", "<br/>", "<br />");
        $text = str_replace($tags, "", $text);

        // Add back in the P and BR tags again, this will remove empty ones
        return apply_filters('the_content', $text);
    }


/**
 * Utility function to build an image int he same format <responsive-image> expects it
 *
 * @param int $attachment_id The WordPress attachment ID
 * @param string $size The WordPress image size keyword
 */
	function custom_build_repsponsive_image($attachment_id, $size = "medium") {
		$attachment = get_post($attachment_id);
		$attachment_data = wp_get_attachment_image_src($attachment_id, $size);

		// Build image details array
		$media_details = array(
			"width"		=> $attachment_data[1],
			"height"	=> $attachment_data[2]
		);

		// Add ACF image meta data
		$acf_image_meta = array(
			"videoUrl"		=> $attachment->videoUrl,
			"primaryColor"	=> $attachment->primaryColor
		);

		// Build base image data
		$image = array(
			"id"			=> "attachment-".$attachment->ID,
			"mediaItemId"	=> $attachment->ID,
			"sourceUrl"		=> $attachment_data[0],
			"title"			=> $attachment->post_title,
			"caption"		=> $attachment->post_excerpt,
			"mediaDetails"	=> $media_details,
			"acfImageMeta"	=> $acf_image_meta,
			"srcSet"		=> wp_get_attachment_image_srcset($attachment->ID, $size),
			"sizes"			=> wp_calculate_image_sizes($size, $attachment_data[0], null, $attachment->ID)
		);

		return $image;
	}

/*
 * Change default gallery shortcode columns to 2
 */
	function theme_gallery_defaults( $settings ) {
	    $settings['galleryDefaults']['columns'] = 2;
	    return $settings;
	}
	add_filter('media_view_settings', 'theme_gallery_defaults');
