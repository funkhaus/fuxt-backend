<?php
/**
 * Credit Block Template.
 *
 * @package fuxt-backend
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$block_name  = get_field( 'name' ) ?: '';
$block_title = get_field( 'title' ) ?: '';
$class       = $block['className'] ?? '';
?>
<div class="custom-block block-credit <?php echo esc_attr( $class ); ?>">

	<?php if ( $block_name ) : ?>
		<h3 class="name">
			<?php echo esc_html( $block_name ); ?>
		</h3>
	<?php endif; ?>

	<?php if ( $block_title ) : ?>	
		<h4 class="title">
			<?php echo esc_html( $block_title ); ?>		
		</h4>
	<?php endif; ?>

	<?php if ( ! $block_name && ! $block_title ) : ?>
		<h3 class="name">
			Empty credit block
		</h3>
	<?php endif; ?>

</div>
