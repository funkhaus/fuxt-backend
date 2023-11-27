<?php
/**
 * ACF custom location rules for Custom Post Types.
 *
 * @package fuxt-backend
 */

namespace FuxtBackend;

/**
 * ACF rules for custom post type
 */
class Acf {

	/**
	 * Custom location rule prefix.
	 *
	 * @var string
	 */
	private $prefix = 'fuxt';

	/**
	 * Custom location rule "CPT Parent" surfix.
	 *
	 * @var string
	 */
	private $surfix_parent = 'parent';

	/**
	 * Custom location rule "CPT belongs to tree" surfix.
	 *
	 * @var string
	 */
	private $surfix_tree = 'tree';

	/**
	 * Location rule name for post is tree.
	 *
	 * @var string
	 */
	private $post_is_tree = 'post-is-tree';

	/**
	 * Location rule name for page is tree.
	 *
	 * @var string
	 */
	private $page_is_tree = 'page-is-tree';

	/**
	 * Location rule name for post has child.
	 *
	 * @var string
	 */
	private $post_has_child = 'post-has-child';

	/**
	 * Location rule name for page has child.
	 *
	 * @var string
	 */
	private $page_has_child = 'page-has-child';

	/**
	 * Custom post types array. Used for caching purpose.
	 *
	 * @var array
	 */
	private $cpt_array;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'add_options' ) );

		add_filter( 'acf/location/rule_types', array( $this, 'location_rule_types' ) );

		// Adds custom location rule types for post and page tree.
		add_filter( 'acf/location/rule_values/' . $this->post_is_tree, array( $this, 'tree_location_rules_values' ), 10, 2 );
		add_filter( 'acf/location/rule_values/' . $this->page_is_tree, array( $this, 'tree_location_rules_values' ), 10, 2 );

		add_filter( 'acf/location/rule_match/' . $this->post_is_tree, array( $this, 'tree_location_rule_match' ), 10, 3 );
		add_filter( 'acf/location/rule_match/' . $this->page_is_tree, array( $this, 'tree_location_rule_match' ), 10, 3 );

		// Adds custom location rule types for post and page has children.
		add_filter( 'acf/location/rule_values/' . $this->post_has_child, array( $this, 'has_child_location_rules_values' ), 10, 2 );
		add_filter( 'acf/location/rule_values/' . $this->page_has_child, array( $this, 'has_child_location_rules_values' ), 10, 2 );

		add_filter( 'acf/location/rule_match/' . $this->post_has_child, array( $this, 'has_child_location_rule_match' ), 10, 3 );
		add_filter( 'acf/location/rule_match/' . $this->page_has_child, array( $this, 'has_child_location_rule_match' ), 10, 3 );

		// Adds custom location rule types.
		add_filter( 'acf/location/rule_values', array( $this, 'cpt_location_rule_values' ), 10, 2 );
		add_filter( 'acf/location/rule_match', array( $this, 'cpt_location_rule_match' ), 10, 4 );

		add_filter( 'acf/fields/relationship/result', array( $this, 'acf_fields_relationship_result' ), 10, 2 );

		add_filter( 'acf/location/rule_values/page_parent', array( $this, 'page_location_rule_values_no_parent' ), 10, 2 );
		add_filter( 'acf/location/rule_match/page_parent', array( $this, 'page_location_rule_match_no_parent' ), 4, 3 );

	}

	/**
	 * Add custom site options panel.
	 *
	 * @return void
	 */
	public function add_options() {
		if ( $this->has_field_group( 'Site Options' ) && function_exists( 'acf_add_options_page' ) ) :
			acf_add_options_page(
				array(
					'page_title'      => 'Site Options',
					'menu_title'      => 'Site Options',
					'menu_slug'       => 'site-options',
					'capability'      => 'edit_posts',
					'redirect'        => false,
					'show_in_graphql' => true,
					'position'        => '60.1',
				)
			);
		endif;

		if ( $this->has_field_group( 'Proxy Settings' ) && function_exists( 'acf_add_options_page' ) ) :
			acf_add_options_page(
				array(
					'page_title'      => 'Proxy Settings',
					'menu_title'      => 'Proxy Settings',
					'menu_slug'       => 'proxy-settings',
					'capability'      => 'activate_plugins',
					'redirect'        => false,
					'show_in_graphql' => false,
					'icon_url'		  => 'dashicons-privacy',
					'position'        => '80.1'
				)
			);
		endif;
	}

	/**
	 * Check if acf group exists by title.
	 *
	 * @param string $title Group title.
	 *
	 * @return bool Returns true if exists
	 */
	private function has_field_group( $title ) {
		$posts = get_posts(
			array(
				'post_type' => 'acf-field-group',
				'title'     => $title,
			)
		);

		return ! empty( $posts );
	}

	/**
	 * This adds the options on the right <select>.
	 * You can add more options for top level pages to test agaisnt here.
	 *
	 * @param array $values Rule values.
	 * @param array $rule   Rule.
	 *
	 * @return array
	 */
	public function tree_location_rules_values( $values, $rule ) {

		// Get any public custom post types that is hierarchical in nature.
		$args = array(
			'public'       => true,
			'_builtin'     => false,
			'hierarchical' => true,
		);

		// Use CPTs, or page.
		$post_types[] = 'page';
		if ( $this->post_is_tree === $rule['param'] ) {
			$post_types = array_keys( get_post_types( $args ) );
		}

		// Get all top level pages/CPTs.
		$args  = array(
			'post_parent'    => 0,
			'post_type'      => $post_types,
			'posts_per_page' => 1000, // Limit this just in case.
			'orderby'        => 'type name',
			'order'          => 'ASC',
		);
		$pages = get_posts( $args );

		// Build menu for ACF filter rule.
		$slugs = array();
		foreach ( $pages as $page ) {
				// Value => Label.
				$slugs[ 'post_id_' . $page->ID ] = $page->post_type . ': ' . $page->post_title;
		}

		return array_merge( $values, $slugs );
	}

	/**
	 * Custom ACF rule match.
	 *
	 * @param bool  $result The match result.
	 * @param array $rule The location rule.
	 * @param array $screen The screen args.
	 *
	 * @return array
	 */
	public function tree_location_rule_match( $result, $rule, $screen ) {

		// Abort if no post ID.
		$post_id = $this->get_current_post_id();
		if ( is_null( $post_id ) ) {
			return $result;
		}

		// Current and selected vars.
		$current_post = get_post( $post_id );
		$tree_id      = (int) str_replace( 'post_id_', '', $rule['value'] );

		// Is current post in the selected tree?
		$ancestors = get_ancestors( $current_post->ID, $current_post->post_type );
		$in_tree   = ( $current_post->ID === $tree_id ) || in_array( $tree_id, $ancestors );

		switch ( $rule['operator'] ) {
			case '==':
				$result = true === $in_tree;
				break;
			case '!=':
				$result = true !== $in_tree;
				break;
		}

		return $result;
	}

	/**
	 * This adds the options on the right <select>.
	 * You can add more options for top level pages to test agaisnt here.
	 *
	 * @param array $values Rule values.
	 * @param array $rule   Rule.
	 *
	 * @return array
	 */
	public function has_child_location_rules_values( $values, $rule ) {
		return 'Has Children';
	}

	/**
	 * Custom ACF rule match.
	 *
	 * @param bool  $result The match result.
	 * @param array $rule The location rule.
	 * @param array $screen The screen args.
	 *
	 * @return array
	 */
	public function has_child_location_rule_match( $result, $rule, $screen ) {

		$post_id = $this->get_current_post_id();
		if ( is_null( $post_id ) ) {
			return $result;
		}

		$post_type = get_post_type( $post_id );

		// Current and selected vars.
		$children = get_children(
			array(
				'post_parent' => $post_id,
				'post_type'   => $post_type,
			)
		);

		switch ( $rule['operator'] ) {
			case '==':
				$result = ! empty( $children );
				break;
			case '!=':
				$result = empty( $children );
				break;
		}

		return $result;
	}

	/**
	 * Custom ACF filter rules.
	 * This adds the label to the first <select> in the Field Group screen.
	 *
	 * @param array $types Rule types.
	 *
	 * @return array
	 */
	public function location_rule_types( $types ) {
		// Adds Parent and Tree rule types.
		$cpt_arr = $this->get_cpt_array();
		if ( count( $cpt_arr ) ) {
			foreach ( $cpt_arr as $cpt ) {
				$types[ $cpt['label'] ] = array(
					$cpt['rules']['parent']['key'] => $cpt['rules']['parent']['label'],
					$cpt['rules']['tree']['key']   => $cpt['rules']['tree']['label'],
				);
			}
		}

		// Adds Tree rule types to Post and Page.
		$types['Post'][ $this->post_is_tree ] = 'Post belongs to tree';
		$types['Page'][ $this->page_is_tree ] = 'Page belongs to tree';

		// Adds Tree rule types to Post and Page.
		$types['Post'][ $this->post_has_child ] = 'Post has children';
		$types['Page'][ $this->page_has_child ] = 'Page has children';

		return $types;
	}

	/**
	 * Custom ACF filter values.
	 * This adds the options on the right <select>.
	 *
	 * @param array $values Rule values.
	 * @param array $rule   Rule.
	 *
	 * @return array
	 */
	public function cpt_location_rule_values( $values, $rule ) {
		$rule_arr = $this->parse_key( $rule['param'] );
		if ( $rule_arr && in_array( $rule_arr['cpt_name'], array_column( $this->get_cpt_array(), 'name' ) ) ) {
			$choices = array(
				0 => 'No Parent',
			);

			// Get grouped posts.
			$groups = acf_get_grouped_posts(
				array(
					'post_type' => array( $rule_arr['cpt_name'] ),
				)
			);

			// Get first group.
			$posts = reset( $groups );

			// Append to choices.
			if ( $posts ) {
				foreach ( $posts as $post ) {
					$choices[ $post->ID ] = acf_get_post_title( $post );
				}
			}
			return $choices;
		}
		return $values;
	}

	/**
	 * Custom ACF rule match.
	 *
	 * @param bool  $result The match result.
	 * @param array $rule The location rule.
	 * @param array $screen The screen args.
	 * @param array $field_group The field group array.
	 *
	 * @return array
	 */
	public function cpt_location_rule_match( $result, $rule, $screen, $field_group ) {

		// Abort if no post ID.
		$post_id = $this->get_current_post_id();
		if ( is_null( $post_id ) ) {
			return $result;
		}

		$post_type = get_post_type( $post_id );

		$rule_arr = $this->parse_key( $rule['param'] );

		if ( $rule_arr && $rule_arr['cpt_name'] === $post_type ) {
			switch ( $rule_arr['surfix'] ) {
				case $this->surfix_parent:
					$parent = get_post_parent( $post_id );
					if ( $parent ) {
						return $parent->ID == $rule['value'];
					} else {
						return empty( $rule['value'] );
					}
				case $this->surfix_tree:
					$ancestors   = get_ancestors( $post_id, $post_type, 'post_type' );
					$ancestor_id = $rule['value'];
					$in_tree     = ( $ancestor_id == $post_id ) || in_array( $ancestor_id, $ancestors );

					switch ( $rule['operator'] ) {
						case '==':
							return $in_tree;
						case '!=':
							return ! $in_tree;
					}
					return false;
			}
		}
		return $result;
	}

	/**
	 * Get all custom post types excluding registered by ACF ones.
	 *
	 * @return array Custom post types array.
	 *               array( 'name' => String, 'label' => String, 'rules' => array() )
	 */
	private function get_cpt_array() {
		// Caching the query for a better performance.
		if ( is_null( $this->cpt_array ) ) {
			$cpt_arr = get_post_types(
				array(
					'_builtin'     => false, // Custom post types only.
					'hierarchical' => true, // Hierarchical ones only.
					'public'       => true, // Exclude CPTs by ACF because those are private ones.
				),
				'objects'
			);

			$this->cpt_array = array();
			foreach ( $cpt_arr as $cpt ) {
				$this->cpt_array[] = array(
					'name'  => $cpt->name,
					'label' => $cpt->label,
					'rules' => array(
						'parent' => array(
							'key'   => $this->get_key_parent( $cpt->name ),
							'label' => $cpt->label . ' Parent',
						),
						'tree'   => array(
							'key'   => $this->get_key_tree( $cpt->name ),
							'label' => $cpt->label . ' belongs to tree',
						),
					),
				);
			}
		}
		return $this->cpt_array;
	}

	/**
	 * Get CPT Parent rule key name.
	 *
	 * @param string $cpt_name Custom post type name.
	 *
	 * @return string
	 */
	private function get_key_parent( $cpt_name ) {
		return $this->prefix . '_' . $cpt_name . '_' . $this->surfix_parent;
	}

	/**
	 * Get CPT Is Tree rule key name.
	 *
	 * @param string $cpt_name Custom post type name.
	 *
	 * @return string
	 */
	private function get_key_tree( $cpt_name ) {
		return $this->prefix . '_' . $cpt_name . '_' . $this->surfix_tree;
	}

	/**
	 * Check if key is for custom location rule by comparing key.
	 *
	 * @param string $key String to check if custom key.
	 *
	 * @return bool false|array [cpt_name, surfix]
	 */
	private function parse_key( $key ) {
		$arr = explode( '_', $key );

		// Validate key value.
		if ( count( $arr ) < 3 || $arr[0] !== $this->prefix ) {
			return false;
		}

		// remove prefix.
		array_shift( $arr );

		$surfix   = array_pop( $arr );
		$cpt_name = join( '_', $arr );
		return array(
			'cpt_name' => $cpt_name,
			'surfix'   => $surfix,
		);
	}

	/**
	 * Adds a hover state to Relationship selectors that shows the URI.
	 *
	 * @param string  $text The text displayed for this post (the post title).
	 * @param WP_Post $post The Relationship.
	 * @return string
	 */
	public function acf_fields_relationship_result( $text, $post ) {
		$uri = get_page_uri( $post );
		return '<span title="/' . $uri . '">' . $text . '</span>';
	}

	/**
	 * Get current post id in admin panel. Returns null if it's not a post page.
	 *
	 * @return int|null
	 */
	private function get_current_post_id() {
		global $pagenow;
		if ( $pagenow !== 'post.php' ) {
			return null;
		}

		global $post;
		return $post->ID;
	}

	/**
	 * Add no parent option to page location rule.
	 *
	 * @param array $values Rule values.
	 * @param array $rule   Rule.
	 *
	 * @return array
	 */
	public function page_location_rule_values_no_parent( $values, $rule ) {
		$values[ PHP_INT_MAX ] = 'No Parent';
		return $values;
	}

	/**
	 * No-parent page location rule match.
	 *
	 * @param bool  $result The match result.
	 * @param array $rule The location rule.
	 * @param array $screen The screen args.
	 *
	 * @return array
	 */
	public function page_location_rule_match_no_parent( $result, $rule, $screen ) {
		if ( $rule['value'] != PHP_INT_MAX ) {
			return $result;
		}

		if ( isset( $screen['post_id'] ) ) {
			$post = get_post( $screen['post_id'] );
			if ( $post->post_type != 'page' ) {
				return false;
			}
			$page_parent = $post ? $post->post_parent : false;

			return empty( $page_parent );
		}
	}
}

new Acf();
