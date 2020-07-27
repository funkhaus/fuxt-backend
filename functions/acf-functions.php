<?php
/*
 * Allow ACF to save a copy of it's settings to a JSON file
 */
	function custom_acf_save_directory($path) {
	    return get_stylesheet_directory() . '/acf';
	}
	add_filter('acf/settings/save_json', 'custom_acf_save_directory');

/*
 * Add ACF site options page
 */
	function fuxt_add_acf_options() {
	    if( function_exists('acf_add_options_page') ) {
	        // Add `Theme General Settings` sub page
	    	acf_add_options_page(array(
	    		'page_title' 	=> 'Site Options',
	    		'menu_title'	=> 'Site Options',
	    		'menu_slug' 	=> 'site-options',
	    		'capability'	=> 'edit_posts',
	    		'redirect'		=> false,
	        	'show_in_graphql' => true,
	        	'position'		  => '60.1'
	    	));
	    }
	}
	add_action('acf/init', 'fuxt_add_acf_options');


/*
 * Custom ACF filter rules.
 * This adds the label to the first <select> in the Field Group screen.
 */
    function acf_location_rules_uri_contains( $choices ) {

        $choices['Post']['post-uri-contains'] = "Post URI contains";
        $choices['Page']['page-uri-contains'] = "Page URI contains";

        return $choices;
    }
    add_filter('acf/location/rule_types', 'acf_location_rules_uri_contains');


/*
 * This provides the logic for the "uri-contains" option
 */
    function acf_location_rules_match_uri_contains( $match, $rule, $options ) {

        $current_post = get_post($options["post_id"]);
        $selected_uri = $rule['value'];
        $current_uri = '/' . trailingslashit( get_page_uri($current_post) );

        // Test if URI contains
        $contain = strpos($current_uri, $selected_uri);
        $does_contain = true;
        if($contain === false) {
            $does_contain = false;
        }

        switch($rule['operator']) {
            case '==' :
                $match = $does_contain === true;
                break;

            case '!=' :
                $match = $does_contain !== true;
                break;
        }

        return $match;
    }
    add_filter('acf/location/rule_match/post-uri-contains', 'acf_location_rules_match_uri_contains', 10, 3);
    add_filter('acf/location/rule_match/page-uri-contains', 'acf_location_rules_match_uri_contains', 10, 3);


/*
 * This adds the options on the right <select>.
 * You can add more options for slugs/URI's to test agaisnt here.
 */
    function acf_location_rules_values_uri_contains( $choices, $data ) {
    
        // Get any public custom post types that is hierarchical in nature
        $args = array(
           'public'         => true,
           '_builtin'       => false,
           'hierarchical'   => true
        );
        $post_types = array_keys( get_post_types($args) ); 
        $post_types[] = "page";
        
        // Get all top level pages/CPTs
        $args = array(
            "post_parent"   => 0,
            "post_type"     => $post_types,
            "post_per_page" => 100, // Limit this just in case
            "orderby"       => "name"
        );
        $pages = get_posts($args);
        
        // Build menu for ACF, wrap slug in "/"
        $slugs = array();
        foreach($pages as $page) {
            $name = "/".$page->post_name."/";
            $slugs[$name] = $page->post_type.": ".$name; 
        }

        return array_merge($choices, $slugs);
    }
    add_filter( 'acf/location/rule_values/post-uri-contains', 'acf_location_rules_values_uri_contains', 10, 2 );
    add_filter( 'acf/location/rule_values/page-uri-contains', 'acf_location_rules_values_uri_contains', 10, 2 );
