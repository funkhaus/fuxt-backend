<?php
/*
 * Allow ACF to save a copy of it's settings to a JSON file
 */
	function custom_acf_save_directory($path) {
	    return get_stylesheet_directory() . '/acf';
	}
	add_filter('acf/settings/save_json', 'custom_acf_save_directory');


/*
 * Have ACF load fields from theme
 */
	function custom_acf_load_directory( $paths ) {
	    // remove original path and add new one
	    unset($paths[0]);
	    $paths[] = get_stylesheet_directory() . '/acf';

	    return $paths;
	}
	add_filter('acf/settings/load_json', 'custom_acf_load_directory');


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
    function acf_location_rules_is_tree( $choices ) {

        $choices['Post']['post-is-tree'] = "Post belongs to tree";
        $choices['Page']['page-is-tree'] = "Page belongs to tree";

        return $choices;
    }
    add_filter('acf/location/rule_types', 'acf_location_rules_is_tree');


/*
 * This provides the logic for the "is tree" option
 */
    function acf_location_rules_match_is_tree( $match, $rule, $options ) {

        // Abort if no post ID
        if( empty($options["post_id"]) ) {
            return $match;
        }

        // Current and selected vars
        $current_post = get_post($options["post_id"]);
        $tree_id = (int) str_replace("post_id_", "", $rule['value']);

        // Is current post in the selected tree?
        $ancestors = get_ancestors($current_post->ID, $current_post->post_type);
        $in_tree = ($current_post->ID == $tree_id) || in_array($tree_id, $ancestors);

        switch($rule['operator']) {
            case '==' :
                $match = $in_tree === true;
                break;

            case '!=' :
                $match = $in_tree !== true;
                break;
        }

        return $match;
    }
    add_filter('acf/location/rule_match/post-is-tree', 'acf_location_rules_match_is_tree', 10, 3);
    add_filter('acf/location/rule_match/page-is-tree', 'acf_location_rules_match_is_tree', 10, 3);


/*
 * This adds the options on the right <select>.
 * You can add more options for top level pages to test agaisnt here.
 */
    function acf_location_rules_values_is_tree( $choices, $data ) {

        // Get any public custom post types that is hierarchical in nature
        $args = array(
           'public'         => true,
           '_builtin'       => false,
           'hierarchical'   => true
        );

        // Use CPTs, or page
        $post_types[] = "page";
        if( $data['param'] == 'post-is-tree' ) {
            $post_types = array_keys( get_post_types($args) );
        }

        // Get all top level pages/CPTs
        $args = array(
            "post_parent"       => 0,
            "post_type"         => $post_types,
            "posts_per_page"    => 100, // Limit this just in case
            "orderby"           => "type name",
            "order"             => "ASC"
        );
        $pages = get_posts($args);

        // Build menu for ACF filter rule
        $slugs = array();
        foreach($pages as $page) {
            // Value => Label
            $slugs['post_id_'.$page->ID] = $page->post_type.": ".$page->post_title;
        }

        return array_merge($choices, $slugs);
    }
    add_filter( 'acf/location/rule_values/post-is-tree', 'acf_location_rules_values_is_tree', 10, 2 );
    add_filter( 'acf/location/rule_values/page-is-tree', 'acf_location_rules_values_is_tree', 10, 2 );
