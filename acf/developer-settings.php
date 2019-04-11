<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5cae81a34f41b',
	'title' => 'Developer Settings',
	'fields' => array(
		array(
			'key' => 'field_5cae81bc0d653',
			'label' => 'Developer ID:',
			'name' => 'developer_id',
			'type' => 'text',
			'instructions' => 'This developer ID can be used to hardcore functionality to this page in the frontend.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5cae81f90d654',
			'label' => 'Prevent non-developer deletion:',
			'name' => 'prevent_deletion',
			'type' => 'true_false',
			'instructions' => 'This prevents this page from being deleted by a non-developer user.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
		array(
			'key' => 'field_5cae827c0d655',
			'label' => 'Force text editor:',
			'name' => 'force_text_editor',
			'type' => 'true_false',
			'instructions' => 'Force the WYSIWYG editor into text mode for this page.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
		array(
			array(
				'param' => 'current_user_role',
				'operator' => '==',
				'value' => 'developer',
			),
		),
	),
	'menu_order' => 10,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'field',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));

endif;
