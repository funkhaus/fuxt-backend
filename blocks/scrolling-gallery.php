<?php
/**
 * Scrolling Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'scrolling-gallery-' . $block['id'];

// Create class attribute allowing for custom "className" and "align" values.
$className = 'scrolling-gallery fuxt-block';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$images = get_field('images') ?: array();
$background_color = get_field('background_color');

// Handle variants
if( empty($images) ) {
    $className .= ' has-no-images';
} 

?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">

    <?php if( $images ) : ?>
    <div class="items">
        <?php 
            foreach($images as $image_id) {
                echo wp_get_attachment_image( $image_id, 'medium' ); 
            }                
        ?>
    </div>
    <?php endif; ?>
    
    <?php if( !$images ) : ?>
        <div class="label">Scrolling Gallery</div>
        <div class="instructions">Please upload new files or select files from your library.</div>
    <?php endif; ?>
    
    <style type="text/css">
        #<?php echo $id; ?> {
            background: <?php echo $background_color; ?>;
        }
        .scrolling-gallery {
            width: 100%;
            overflow-x: scroll;
        }
        .scrolling-gallery .items {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: flex-start;
            align-content: center;
            align-items: center;     

            padding-right: 20px;
        }
        .scrolling-gallery .items > * {
            width: 33vw;
            height: auto;
            margin-left: 20px;
            min-width: 300px;
        }
        .scrolling-gallery .items > *:first-child {
            margin-left: 0;
        }        
        .scrolling-gallery.has-no-images {
            box-shadow: inset 0 0 0 1px #1e1e1e;
            overflow: hidden;
        }
    </style>
</div>
