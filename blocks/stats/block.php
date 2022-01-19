<?php
/**
 * Stats Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
 	
	$image_id = get_field('background_image') ?: false;	
	$stats = get_field('stats') ?: array();
	$font_color = get_field('font_color', $post_id) ?: '#000000';
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-stats <?php echo $class; ?>" style="color: <?php echo $font_color; ?>;">
    <?php if($image_id) : $image_src = wp_get_attachment_image_src($image_id, 'large');	?>
	    <img src="<?php echo $image_src[0]; ?>" class="image" height="<?php echo $image_src[1]; ?>" width="<?php echo $image_src[2]; ?>" />
	<?php endif; ?>

	
	<?php foreach( $stats as $stat ): ?>
		<div class="stat">
			<?php echo $stat['metric']; ?><br>
			<?php echo $stat['label']; ?>			
		</div>	
	<?php endforeach; ?>
</div>