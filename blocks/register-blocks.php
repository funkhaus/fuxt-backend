<?php
/**
 * Register custom ACF Blocks
 * SEE: https://www.advancedcustomfields.com/resources/blocks/
 * SEE: https://www.advancedcustomfields.com/resources/acf_register_block_type/
 */
function fuxt_init_custom_block() {

    // Abort if ACF function does not exists.
    if( ! function_exists('acf_register_block_type') ) {
        return;
    }

    // Register an example "testimonial" block.
    acf_register_block_type(array(
        'name'              => 'scrolling-gallery',
        'title'             => __('Scrolling Gallery'),
        'description'       => __('A sideways scrolling gallery.'),
        'render_template'   => 'blocks/scrolling-gallery.php',
        'category'          => 'media',
        'icon'              => 'images-alt',
        'keywords'          => array( 'gallery', 'scrolling' ),
        'enqueue_style'     => get_template_directory_uri() . '/blocks/blocks.css',
    ));
}
add_action('acf/init', 'fuxt_init_custom_block');
