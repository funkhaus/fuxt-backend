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
        return;
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

/**
 * Disable the fullscreen editor as default
 * SEE: https://jeanbaptisteaudras.com/en/2020/03/disable-block-editor-default-fullscreen-mode-in-wordpress-5-4/
 */
function fuxt_disable_editor_fullscreen_default() {
	$script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
	wp_add_inline_script( 'wp-blocks', $script );
}
add_action( 'enqueue_block_editor_assets', 'fuxt_disable_editor_fullscreen_default' );
