<?php
/**
 * Scrolling Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
 	
	$images = get_field('images') ?: array();
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-scrolling-gallery <?php echo $class; ?>">
	<?php foreach( $images as $image_id ): ?>
		<?php
			$image_src = wp_get_attachment_image_src($image_id, 'large');
		?>
	     <img src="<?php echo $image_src[0]; ?>" class="image" height="<?php echo $image_src[1]; ?>" width="<?php echo $image_src[2]; ?>" />
	<?php endforeach; ?>
</div>