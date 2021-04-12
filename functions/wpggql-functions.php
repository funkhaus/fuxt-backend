<?php
/**
 * Gets block whitelist
 *
 * @return array
 */
function ggb_get_block_whitelist() {
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

// whitelist blocks for editors
add_filter( 'allowed_block_types', 'ggb_get_block_whitelist' );
