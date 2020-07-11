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
    function acf_location_rules_types( $choices ) {
        $choices['Post']['uri-contains'] = "Post URI contains";
        $choices['Page']['uri-contains'] = "Page URI contains";
        return $choices;
    }
    add_filter('acf/location/rule_types', 'acf_location_rules_types');


/*
 * This provides the logic for the "uri-contains" option
 */
    function acf_location_rules_match_has_taxonomy( $match, $rule, $options ) {

        $current_post = get_post($options["post_id"]);
        $selected_uri = $rule['value'];
        $current_uri = '/' . trailingslashit( get_page_uri($current_post) );

        switch($rule['operator']) {
            case '==' :
                $match = $selected_uri == $current_uri;
                break;

            case '!=' :
                $match = $selected_uri != $current_uri;
                break;
        }

        return $match;
    }
    add_filter('acf/location/rule_match/uri-contains', 'acf_location_rules_match_has_taxonomy', 10, 3);


/*
 * This adds the options on the right <select>. You can add more options for slugs/URI's to test agaisnt here.
 */
    function acf_location_rules_values_has_taxonomy( $choices ) {

        $slugs = array(
            '/contact'      =>  '/contact',
            //'/featured/'    =>  '/featured/'
        );

        return array_merge($choices, $slugs);
    }
    add_filter( 'acf/location/rule_values/uri-contains', 'acf_location_rules_values_has_taxonomy' );
