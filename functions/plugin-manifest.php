<?php
/*
 * This file handles the required plugins for the theme
 */
    include_once get_template_directory() . '/libs/class-tgm-plugin-activation.php';

    function vuehaus_register_required_plugins() {

        // Change these values to install new versions of plugins
        $config = array(
            'id'           => 'fuxt',                  // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',                      // Default absolute path to bundled plugins.
            'menu'         => 'tgmpa-install-plugins', // Menu slug.
            'parent_slug'  => 'themes.php',            // Parent menu slug.
            'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => true,                    // Automatically activate plugins after installation or not.
            'message'      => '',                      // Message to output right before the plugins table.
        );

        $plugins = array(
            array(
                'name'      => 'WP Graph QL',
                'slug'      => 'wp-graphql',
                'version'   => "1.0"
            ),
            array(
                'name'      => 'Advanced Custom Fields Pro',
                'slug'      => 'advanced-custom-fields-pro',
                'external_url'	=> 'https://www.advancedcustomfields.com/pro/',
                'required'	=> false
            ),
            array(
                'name'      => 'Auto SEO',
                'slug'      => 'funkhaus-auto-seo',
                'external_url'	=> 'https://github.com/funkhaus/funkhaus-auto-seo/',
                'required'	=> false
            ),
            array(
                'name'      => 'WP GraphQL for Advanced Custom Fields',
                'slug'      => 'wp-graphql-acf',
                'source'    => 'https://github.com/wp-graphql/wp-graphql-acf/archive/master.zip',
                'version'   => "0.3.3",
                'required'  => false
            ),
            array(
                'name'      => 'Focushuas',
                'slug'      => 'focushaus',
                'source'    => 'https://github.com/funkhaus/focushaus/archive/master.zip',
                'version'   => "2.0",
                'required'  => false
            ),
            array(
                'name'      => 'Classic Editor',
                'slug'      => 'classic-editor',
                'required'  => false
            ),
            array(
                'name'      => 'Nested Pages',
                'slug'      => 'wp-nested-pages',
                'required'  => false
            ),
            array(
                'name'      => 'Netlify Deploy',
                'slug'      => 'funkhaus-netlify-deploy',
                'source'    => 'https://github.com/funkhaus/netlify-deploy/archive/main.zip',
                'version'   => "1.0"
            ),
        );

        tgmpa( $plugins, $config );
    }
    add_action( 'tgmpa_register', 'vuehaus_register_required_plugins' );
