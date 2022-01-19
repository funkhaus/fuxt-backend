<?php
/**
 * Press Links Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
	
	$title = get_field('text') ?: '';
	$awards = get_field('award') ?: array();
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-awards <?php echo $class; ?>">
	<?php if($title) : ?>
		<h3 class="block-title">
			<?php echo $title; ?>
		</h3>
	<?php endif; ?>
	
	<?php foreach( $awards as $award ): ?>
		<span class="award">
			<h4 class="title">
				<?php echo $award['title']; ?>				
			</h4>

			<?php if($award['count']) : ?>				
				<span class="count">
					[<?php echo $award['count']; ?>]
				</span>
			<?php endif; ?>			
		</span>
	<?php endforeach; ?>
</div>