<?php
/**
 * Deprecated code
 *
 * @package fuxt-backend
 */

/**
 * Allow subscriber to see Private posts/pages
 */
function add_theme_caps() {
	// Gets the author role
	$role = get_role( 'subscriber' );

	// Add capabilities
	$role->add_cap( 'read_private_posts' );
	$role->add_cap( 'read_private_pages' );
}
//add_action( 'switch_theme', 'add_theme_caps' );


/**
 * Register custom ACF blocks
 * SEE https://www.advancedcustomfields.com/resources/blocks/
 * SEE https://www.advancedcustomfields.com/resources/acf_register_block_type/
 */
function fuxt_custom_blocks() {

	// Abort early if custom blocks not supported (not ACF Pro).
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	// Credit block.
	acf_register_block_type(
		array(
			'name'            => 'credit',
			'title'           => 'Credit',
			'description'     => 'A custom credit block.',
			'category'        => 'text',
			'keywords'        => array( 'text', 'credit' ),
			'icon'            => 'editor-textcolor',
			'render_template' => get_template_directory() . '/blocks/credit/block.php',
			'enqueue_style'   => get_template_directory_uri() . '/blocks/credit/block.css',
		)
	);

}
//add_action( 'acf/init', 'fuxt_custom_blocks' );

/**
 * Extend GraphQL to add a mutation to send emails via the wp_mail() function.
 * SEE https://developer.wordpress.org/reference/functions/wp_mail/ for more info on how each input works.
 * SEE https://docs.wpgraphql.com/extending/mutations/
 */
function gql_register_email_mutation() {
	// Define the input parameters
	$input_fields = array(
		'to'      => array(
			'type'        => array( 'list_of' => 'String' ),
			'description' =>
				'Array of email addresses to send email to. Must comply to RFC 2822 format.',
		),
		'subject' => array(
			'type'        => 'String',
			'description' => 'Email subject.',
		),
		'message' => array(
			'type'        => 'String',
			'description' => 'Message contents.',
		),
		'headers' => array(
			'type'        => array( 'list_of' => 'String' ),
			'description' =>
				'Array of any additional headers. This is how you set BCC, CC and HTML type. See wp_mail() function for more details.',
		),
		'trap'    => array(
			'type'        => 'String',
			'description' =>
				'Crude anti-spam measure. This must equal the clientMutationId, otherwise the email will not be sent.',
		),
	);

	// Define the ouput parameters
	$output_fields = array(
		'to'      => array(
			'type'        => array( 'list_of' => 'String' ),
			'description' =>
				'Array of email addresses to send email to. Must comply to RFC 2822 format.',
		),
		'subject' => array(
			'type'        => 'String',
			'description' => 'Email subject.',
		),
		'message' => array(
			'type'        => 'String',
			'description' => 'Message contents.',
		),
		'headers' => array(
			'type'        => array( 'list_of' => 'String' ),
			'description' =>
				'Array of any additional headers. This is how you set BCC, CC and HTML type. See wp_mail() function for more details.',
		),
		'sent'    => array(
			'type'        => 'Boolean',
			'description' => 'Returns true if the email was sent successfully.',
			'resolve'     => function ( $payload, $args, $context, $info ) {
				return isset( $payload['sent'] ) ? $payload['sent'] : false;
			},
		),
	);

	// This function processes the submitted data
	$mutate_and_get_payload = function ( $input, $context, $info ) {
		// Spam honeypot. Make sure that the clientMutationId matches the trap input.
		if ( $input['clientMutationId'] !== $input['trap'] ) {
			throw new \GraphQL\Error\UserError( 'You got caught in a spam trap' );
		}

		// Vailidate email before trying to send
		foreach ( $input['to'] as $email ) {
			if ( ! is_email( $email ) ) {
				throw new \GraphQL\Error\UserError( 'Invalid email address: ' . $email );
			}
		}

		// Send email!
		$input['sent'] = wp_mail(
			$input['to'],
			$input['subject'],
			$input['message'],
			$input['headers'],
			$input['attachments']
		);

		return $input;
	};

	// Add mutation to WP-GQL now
	$args = array(
		'inputFields'         => $input_fields,
		'outputFields'        => $output_fields,
		'mutateAndGetPayload' => $mutate_and_get_payload,
	);
	register_graphql_mutation( 'sendEmail', $args );
}
//add_action( 'graphql_register_types', 'gql_register_email_mutation' );
