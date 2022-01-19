<?php
/**
 * Press Links Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
 	
 	$links = get_field('latest_news') ?: array();
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-links <?php echo $class; ?>">
	<?php foreach( $links as $link ): ?>
		<a class="link" target="_blank" href="<?php echo $link['url']; ?>">
			<h4 class="title">
				<?php echo $link['title']; ?>
			</h4>
			
			<?php 
				if($link['logo']) {
					echo wp_get_attachment_image($link['logo']);					
				} elseif($link['credit']) {
					echo '<span class="credit">'.$link['credit'].'</span>';
				}
			?>
			
		</a>
	<?php endforeach; ?>
</div>