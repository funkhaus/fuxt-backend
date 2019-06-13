<?php
/*
 * Expose Google Analytics and theme screenshot to WP-GQL API
 */
function whitelist_settings()
{
    // Allow some WordPress settings to be exposed to WP-GQL
    $args = array(
        'sanitize_callback' => 'esc_attr',
        'show_in_graphql' => true
    );
    register_setting('general', 'ga_tracking_code_1', $args);
    register_setting('general', 'ga_tracking_code_2', $args);

    // Define a custom field to get Theme screenshot URL
    register_graphql_field('GeneralSettings', 'themeScreenshotUrl', [
        'type' => 'String',
        'description' => __('URL to the active theme screenshot', 'stackhaus'),
        'resolve' => function ($root, $args, $context, $info) {
            $theme = wp_get_theme();
            $url = "";
            if ($theme->screenshot) {
                $url = get_template_directory_uri() . "/" . $theme->screenshot;
            }
            return $url;
        }
    ]);
}
add_action('graphql_init', 'whitelist_settings', 1);

/**
 *
 * Adds next post node to all the custom Post Types
 *
 */
function gql_register_next_post()
{
    $post_types = WPGraphQL::$allowed_post_types;

    if (!empty($post_types) && is_array($post_types)) {
        foreach ($post_types as $post_type) {
            $post_type_object = get_post_type_object($post_type);

            // Get the Type name with ucfirst
            $ucfirst = ucfirst($post_type_object->graphql_single_name);

            // Register a new Edge Type
            register_graphql_type('Next' . $ucfirst . 'Edge', [
                'fields' => [
                    'node' => [
                        'description' => __(
                            'The node of the next item',
                            'wp-graphql'
                        ),
                        'type' => $ucfirst,
                        'resolve' => function ($post_id, $args, $context) {
                            return \WPGraphQL\Data\DataSource::resolve_post_object(
                                $post_id,
                                $context
                            );
                        }
                    ]
                ]
            ]);

            // Register the next{$type} field
            register_graphql_field($ucfirst, 'next' . $ucfirst, [
                'type' => 'Next' . $ucfirst . 'Edge',
                'description' => __(
                    'The next post of the current port',
                    'wp-graphql'
                ),
                'resolve' => function ($post_object, $args, $context) {
                    global $post;
                    $post = get_post($post_object->postId);
                    $next_post = get_next_post();
                    $post_id = $next_post->ID;
                    return $post_id;
                }
            ]);
        }
    }
}
add_action('graphql_register_types', 'gql_register_next_post');

/**
 *
 * Adds previous post node to all the custom Post Types
 *
 */
function gql_register_previous_post()
{
    $post_types = WPGraphQL::$allowed_post_types;

    if (!empty($post_types) && is_array($post_types)) {
        foreach ($post_types as $post_type) {
            $post_type_object = get_post_type_object($post_type);

            // Get the Type name with ucfirst
            $ucfirst = ucfirst($post_type_object->graphql_single_name);

            // Register a new Edge Type
            register_graphql_type('Previous' . $ucfirst . 'Edge', [
                'fields' => [
                    'node' => [
                        'description' => __(
                            'The node of the previous item',
                            'wp-graphql'
                        ),
                        'type' => $ucfirst,
                        'resolve' => function ($post_id, $args, $context) {
                            return \WPGraphQL\Data\DataSource::resolve_post_object(
                                $post_id,
                                $context
                            );
                        }
                    ]
                ]
            ]);

            // Register the next{$type} field
            register_graphql_field($ucfirst, 'previous' . $ucfirst, [
                'type' => 'Previous' . $ucfirst . 'Edge',
                'description' => __(
                    'The previous post of the current post',
                    'wp-graphql'
                ),
                'resolve' => function ($post_object, $args, $context) {
                    global $post;
                    $post = get_post($post_object->postId);
                    $prev_post = get_previous_post();
                    $post_id = $prev_post->ID;
                    return $post_id;
                }
            ]);
        }
    }
}
add_action('graphql_register_types', 'gql_register_previous_post');
