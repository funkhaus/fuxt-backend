<?php
/*
 * This file handles the required plugins for the theme
 */
    include_once get_template_directory() . '/functions/class-tgm-plugin-activation.php';

    function vuehaus_register_required_plugins() {

        // Change these values to install new versions of plugins
        $config = array(
            'id'           => 'stackhaus',             // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',                      // Default absolute path to bundled plugins.
            'menu'         => 'tgmpa-install-plugins', // Menu slug.
            'parent_slug'  => 'themes.php',            // Parent menu slug.
            'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,                   // Automatically activate plugins after installation or not.
            'message'      => '',                      // Message to output right before the plugins table.
        );

        $plugins = array(
            array(
                'name'      => 'WP Graph QL',
                'slug'      => 'wp-graphql',
                'source'    => 'https://github.com/wp-graphql/wp-graphql/archive/master.zip',
                'version'   => $latest_rest_easy
            ),
            array(
                'name'      => 'WP Graph QL Meta Query',
                'slug'      => 'wp-graphql-meta-query',
                'source'    => 'https://github.com/wp-graphql/wp-graphql-meta-query/archive/master.zip',
                'version'   => $latest_rest_easy
            ),
            array(
                'name'      => 'Classic Editor',
                'slug'      => 'classic-editor',
                'required'	=> false
            ),
            array(
                'name'      => 'Nested Pages',
                'slug'      => 'wp-nested-pages',
                'required'	=> false
            )
        );

        tgmpa( $plugins, $config );
    }
    add_action( 'tgmpa_register', 'vuehaus_register_required_plugins' );
