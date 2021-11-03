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
		'core/spacer',
		'core/cover',
        'core/html',
        //'acf/scrolling-gallery'
	);
}
add_filter('allowed_block_types', 'fuxt_block_whitelist');

/**
 * Disable the fullscreen editor as default
 * SEE: https://jeanbaptisteaudras.com/en/2020/03/disable-block-editor-default-fullscreen-mode-in-wordpress-5-4/
 */
function fuxt_disable_editor_fullscreen_default() {
	$script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
	wp_add_inline_script( 'wp-blocks', $script );
}
add_action( 'enqueue_block_editor_assets', 'fuxt_disable_editor_fullscreen_default' );

/**
 * Disable the paragraph dropcap
 */
function fuxt_disable_dropcap($editor_settings) {
    $editor_settings['__experimentalFeatures']['defaults']['typography']['dropCap'] = false;
    return $editor_settings;
}
add_filter('block_editor_settings', 'fuxt_disable_dropcap');
