<?php
/*
 * Register custom ACF blocks
 * SEE https://www.advancedcustomfields.com/resources/blocks/
 * SEE https://www.advancedcustomfields.com/resources/acf_register_block_type/
 */

function fuxt_custom_blocks() {

    // Abort early if custom blocks not supported (not ACF Pro).
    if( !function_exists('acf_register_block_type') ) {
		return;
    }

    // Credit block.
    acf_register_block_type(array(
        'name'              => 'credit',
        'title'             => 'Credit',
        'description'       => 'A custom credit block.',
        'category'          => 'text',
        'keywords' 			=> array('text', 'credit'),
        'icon'				=> 'editor-textcolor',
        'render_template'   => get_template_directory() . '/blocks/credit/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/credit/block.css',
    ));

    // Scrolling Gallery
    acf_register_block_type(array(
        'name'              => 'scrolling-gallery',
        'title'             => 'Scrolling Gallery',
        'description'       => 'A side scrolling gallery block.',
        'category'          => 'media',
        'keywords' 			=> array('gallery', 'scrolling', 'image'),
        'icon'				=> 'images-alt',
        'render_template'   => get_template_directory() . '/blocks/scrolling-gallery/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/scrolling-gallery/block.css',
    ));

    // Stats Block
    acf_register_block_type(array(
        'name'              => 'stats',
        'title'             => 'Stats',
        'description'       => 'A block used to show statistics.',
        'category'          => 'text',
        'keywords' 			=> array('statistics', 'numbers', 'count'),
        'icon'				=> 'info',
        'render_template'   => get_template_directory() . '/blocks/stats/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/stats/block.css',
    ));

    // Video Tray
    acf_register_block_type(array(
        'name'              => 'video-tray',
        'title'             => 'Video Tray',
        'description'       => 'A block to show multiple small Vimeo videos in a scrolling tray.',
        'category'          => 'media',
        'keywords' 			=> array('video', 'vimeo', 'scrolling'),
        'icon'				=> 'video-alt2',
        'render_template'   => get_template_directory() . '/blocks/video-tray/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/video-tray/block.css',
    ));

    // Press Links
    acf_register_block_type(array(
        'name'              => 'links',
        'title'             => 'Links',
        'description'       => 'A block to show multiple links to press articles.',
        'category'          => 'widgets',
        'keywords' 			=> array('press', 'links', 'article'),
        'icon'				=> 'admin-links',
        'render_template'   => get_template_directory() . '/blocks/links/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/links/block.css',
    ));

    // Awards
    acf_register_block_type(array(
        'name'              => 'awards',
        'title'             => 'Awards',
        'description'       => 'A block to show Awards.',
        'category'          => 'widgets',
        'keywords' 			=> array('awards', 'links'),
        'icon'				=> 'awards',
        'render_template'   => get_template_directory() . '/blocks/awards/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/awards/block.css',
    ));

    // Video with badge
    acf_register_block_type(array(
        'name'              => 'video-with-badge',
        'title'             => 'Video with Badge',
        'description'       => 'A block to to embed a video, with a badge in the top corner.',
        'category'          => 'media',
        'keywords' 			=> array('video', 'badge', 'vimeo'),
        'icon'				=> 'video-alt3',
        'render_template'   => get_template_directory() . '/blocks/video-with-badge/block.php',
        'enqueue_style' 	=> get_template_directory_uri() . '/blocks/video-with-badge/block.css',
    ));

}
// add_action('acf/init', 'fuxt_custom_blocks');
