<?php
/**
 * Deprecated code
 *
 * @package fuxt-backend
 */

/**
 * Allow subscriber to see Private posts/pages
 */
function add_theme_caps() {
	// Gets the author role
	$role = get_role( 'subscriber' );

	// Add capabilities
	$role->add_cap( 'read_private_posts' );
	$role->add_cap( 'read_private_pages' );
}
//add_action( 'switch_theme', 'add_theme_caps' );


/**
 * Register custom ACF blocks
 * SEE https://www.advancedcustomfields.com/resources/blocks/
 * SEE https://www.advancedcustomfields.com/resources/acf_register_block_type/
 */
function fuxt_custom_blocks() {

	// Abort early if custom blocks not supported (not ACF Pro).
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	// Credit block.
	acf_register_block_type(
		array(
			'name'            => 'credit',
			'title'           => 'Credit',
			'description'     => 'A custom credit block.',
			'category'        => 'text',
			'keywords'        => array( 'text', 'credit' ),
			'icon'            => 'editor-textcolor',
			'render_template' => get_template_directory() . '/blocks/credit/block.php',
			'enqueue_style'   => get_template_directory_uri() . '/blocks/credit/block.css',
		)
	);

}
//add_action( 'acf/init', 'fuxt_custom_blocks' );