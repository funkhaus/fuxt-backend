<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5cccac8d93cd4',
	'title' => 'Image Meta',
	'fields' => array(
		array(
			'key' => 'field_5cccac9d781a1',
			'label' => 'Video URL',
			'name' => 'video_url',
			'type' => 'url',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
			'placeholder' => '',
		),
		array(
			'key' => 'field_5cccacb8781a2',
			'label' => 'Primary Color',
			'name' => 'primary_color',
			'type' => 'color_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'attachment',
				'operator' => '==',
				'value' => 'image',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'acfImageMeta',
));

endif;
