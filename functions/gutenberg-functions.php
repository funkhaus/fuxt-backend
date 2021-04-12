<?php
/**
 * Whitelist blocks for the Gutenberg editor
 *
 * @return array
 */
function fuxt_block_whitelist() {
	return array(
		'core/paragraph',
		'core/image',
		'core/heading',
		'core/gallery',
		'core/list',
		'core/quote',
		'core/columns',
		'core/column',
		'core/embed',
	);
}
add_filter('allowed_block_types', 'fuxt_block_whitelist');

/**
 * Register custom ACF Blocks
 * SEE: https://www.advancedcustomfields.com/resources/blocks/
 */
function fuxt_init_custom_block() {

    // Abort if ACF function does not exists.
    if( ! function_exists('acf_register_block_type') ) {
        return
    }

    // Register an example "testimonial" block.
    acf_register_block_type(array(
        'name'              => 'testimonial',
        'title'             => __('Testimonial'),
        'description'       => __('A custom testimonial block.'),
        'render_template'   => 'template-parts/blocks/testimonial/testimonial.php',
        'category'          => 'formatting',
        'icon'              => 'admin-comments',
        'keywords'          => array( 'testimonial', 'quote' ),
    ));
}
//add_action('acf/init', 'fuxt_init_custom_block');
