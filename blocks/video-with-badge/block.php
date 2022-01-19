<?php
/**
 * Video with badge Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */	
	
	$video_url = get_field('video_url') ?: '';
	$badge_id = get_field('badge') ?: '';
	$iframe_html = wp_oembed_get($video_url);
	
	$class = $block['className'] ?? '';
?>
<div class="custom-block block-video-with-badge <?php echo $class; ?>">
	
	<?php if($iframe_html) : ?>
		<?php echo $iframe_html; ?>
		<?php echo wp_get_attachment_image($badge_id); ?>
	<?php else : ?>
		<span class="message">Please set a valid Vimeo URL</span>
	<?php endif; ?>

</div>