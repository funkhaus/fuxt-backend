<?php
/**
 * Scrolling Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
 	
	$videos = get_field('videos') ?: array();	
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-video-tray <?php echo $class; ?>">
	<?php foreach( $videos as $video ): ?>
		<div class="item">
		<?php
			$image_src = wp_get_attachment_image_src($video['image'], 'large');	
		?>
			<img src="<?php echo $image_src[0]; ?>" class="image" height="<?php echo $image_src[1]; ?>" width="<?php echo $image_src[2]; ?>" />
			
			<h3 class="title">
				<?php echo $video['title']; ?>
			</h3>				
			<h4 class="sub-title">
				<?php echo $video['sub-title']; ?>
			</h4>							
		</div>
	<?php endforeach; ?>
</div>