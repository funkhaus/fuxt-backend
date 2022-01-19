<?php
/**
 * Credit Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
	
	$name = get_field('name') ?: '';
	$title = get_field('title') ?: '';
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-credit <?php echo $class; ?>">

	<?php if($name) : ?>
		<h3 class="name">
			<?php echo $name; ?>
		</h3>
	<?php endif; ?>

	<?php if($title) : ?>	
		<h4 class="title">
			<?php echo $title; ?>		
		</h4>
	<?php endif; ?>

	<?php if(!$name && !$title) : ?>
		<h3 class="name">
			Empty credit block
		</h3>
	<?php endif; ?>

</div>