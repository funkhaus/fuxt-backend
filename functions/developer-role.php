<?php

/*
 * Add Developer role
 */ 
    function custom_theme_switch(){
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

        // set initial user to Developer
        $user = new WP_User(1);
        $user->set_role('developer');
    }
    add_action('after_switch_theme', 'custom_theme_switch');


/*
 * Disable Rich Editor on certain pages
 */
    function disabled_rich_editor($allow_rich_editor) {
        global $post;

        if($post->_custom_hide_richedit === 'on') {
            return false;
        }
        return true;
    }
    add_filter( 'user_can_richedit', 'disabled_rich_editor');

/*
 * Deactivate attachment serialization on certain pages
 */
    function deactivate_attachment_serialization($allow_attachment_serialization) {
        global $post;

        if($post->_custom_deactivate_attachment_serialization === 'on') {
            return false;
        }
        return true;
    }
    add_filter( 'rez-serialize-attachments', 'deactivate_attachment_serialization');

/*
 * Add developer metaboxes to the new/edit page
 */
    function custom_add_developer_metaboxes($post_type, $post){
        if( !is_user_developer() ) return;

        add_meta_box('custom_dev_meta', 'Developer Meta', 'custom_dev_meta', 'page', 'normal', 'low');
    }
    add_action('add_meta_boxes', 'custom_add_developer_metaboxes', 10, 2);


/*
 * Build dev meta box (only for users with Developer role)
 */ 
    function custom_dev_meta($post) {

        ?>
            <div class="custom-meta">
                <label for="custom-developer-id">Enter the Developer ID for this page:</label>
                <input id="custom-developer-id" class="short" title="Developer ID" name="custom_developer_id" type="text" value="<?php echo $post->custom_developer_id; ?>">
                <br/>

                <label for="custom-lock">Prevent non-dev deletion:</label>
                <input id="custom-lock" class="short" title="Prevent deletion" name="_custom_lock" type="checkbox" <?php if( $post->_custom_lock ) echo 'checked'; ?>>
                <br/>

                <label for="custom-richedit">Hide rich editor:</label>
                <input id="custom-richedit" class="short" title="Hide rich editor" name="_custom_hide_richedit" type="checkbox" <?php if( $post->_custom_hide_richedit === 'on' ) echo 'checked'; ?>>
                <br/>

            </div>

        <?php
    }

/*
 * Prevent non-dev from deleting locked pages/posts
 */ 
    function check_custom_post_lock( $target_post ){
        $target_post = get_post($target_post);

        if( !is_user_developer() and $target_post->_custom_lock ){
            echo 'Only a user with the Developer role can delete this page.<br/><br/>';
            echo '<a href="javascript:history.back()">Back</a>';
            exit;
        }
    }
    add_action('wp_trash_post', 'check_custom_post_lock', 10, 1);
    add_action('before_delete_post', 'check_custom_post_lock', 10, 1);

/*
 * Save routine for developer meta boxes
 */ 
    function custom_save_developer_metabox($post_id){

        // check autosave
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // these values will only be updated if the current user is a Developer
        if( !is_user_developer() ) return;

        if( isset($_POST['custom_developer_id']) ) {
            update_post_meta($post_id, 'custom_developer_id', $_POST['custom_developer_id']);
        }
        if( isset($_POST['_custom_lock']) ) {
            $value = $_POST['_custom_lock'] == 'on' ? 'on' : 0;
            update_post_meta($post_id, '_custom_lock', $value);
        } else {
            update_post_meta($post_id, '_custom_lock', 0);
        }

        if( isset($_POST['_custom_hide_richedit']) ){
            $value = $_POST['_custom_hide_richedit'] == 'on' ? 'on' : 0;
            update_post_meta($post_id, '_custom_hide_richedit', $_POST['_custom_hide_richedit']);
        } else {
            update_post_meta($post_id, '_custom_hide_richedit', 0);
        }

    }
    add_action('save_post', 'custom_save_developer_metabox');

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
        if( is_user_developer() ){
            $classes .= ' is-developer';
        }
        return $classes;
    }
    add_filter('admin_body_class', 'add_developer_admin_body_class');