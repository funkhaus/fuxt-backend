<?php

/*
 * Add Developer role
 */
    function custom_add_developer_role(){
        global $wp_roles;
        if ( ! isset( $wp_roles ) ){
            $wp_roles = new WP_Roles();
        }

        $admin_role = $wp_roles->get_role('administrator');

        add_role(
            'developer',
            __('Developer'),
            $admin_role->capabilities
        );

        // Set initial user to Developer
        $user = new WP_User(1);
        $user->set_role('developer');
        $user->add_role('administrator');
    }
    add_action('after_switch_theme', 'custom_add_developer_role');

/*
 * Prevent non-dev from deleting locked pages/posts
 */
    function check_custom_post_lock( $target_post ){
        $target_post = get_post($target_post);

        // Abort if just trashing a revision
        if( wp_is_post_revision($target_post) ) {
            return;
        }

        if( !is_user_developer() and $target_post->prevent_deletion ){
            echo 'Only a user with the Developer role can delete this page.<br/><br/>';
            echo '<a href="javascript:history.back()">Back</a>';
            exit;
        }
    }
    add_action('wp_trash_post', 'check_custom_post_lock', 10, 1);
    add_action('before_delete_post', 'check_custom_post_lock', 10, 1);



/*
 * Conditional to test if current user is a developer
 */
    function is_user_developer(){
        $roles = wp_get_current_user()->roles;
        return in_array( 'developer', $roles );
    }


/*
 * Makes sure Developer role can sort Nested Pages automatically
 */
    function give_developer_ordering_permissions(){

        if( is_plugin_active('wp-nested-pages/nestedpages.php') ){

            $allowed_to_sort = get_option('nestedpages_allowsorting');

            if( !$allowed_to_sort ){
                $allowed_to_sort = array();
            }

            if( !in_array('developer', $allowed_to_sort) ){
                $allowed_to_sort[] = 'developer';
                update_option('nestedpages_allowsorting', $allowed_to_sort);
            }
        }

    }
    add_action('admin_init', 'give_developer_ordering_permissions', 1);


/*
 * Add 'is-developer' class to WP admin pages if we're a developer
 */
    function add_developer_admin_body_class($classes){
        global $post;

        if( is_user_developer() ){
            $classes .= ' is-developer';
        }
        if( !empty($post->prevent_deletion) ) {
	        $classes .= ' is-developer-locked';
        }
        if( empty($post->post_name) ) {
	        $classes .= ' no-slug';
        }
        return $classes;
    }
    add_filter('admin_body_class', 'add_developer_admin_body_class');
