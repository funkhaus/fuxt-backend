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
class Acf_Rules {

	/**
	 * Custom post types array. Used for caching purpose.
	 *
	 * @var array
	 */
	private $cpt_array;

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
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'acf/location/rule_types', array( $this, 'acf_location_rule_types' ) );
		add_filter( 'acf/location/rule_values', array( $this, 'acf_location_rule_values' ), 10, 2 );
		add_filter( 'acf/location/rule_match', array( $this, 'acf_location_rule_match' ), 10, 4 );
	}

	/**
	 * Custom ACF filter rules.
	 * This adds the label to the first <select> in the Field Group screen.
	 *
	 * @param array $types Rule types.
	 *
	 * @return array
	 */
	public function acf_location_rule_types( $types ) {
		$cpt_arr = $this->get_cpt_array();

		if ( count( $cpt_arr ) ) {
			foreach ( $cpt_arr as $cpt ) {
				$types[ $cpt['label'] ] = array(
					$cpt['rules']['parent']['key']   => $cpt['rules']['parent']['label'],
					$cpt['rules']['tree']['key']  => $cpt['rules']['tree']['label'],
				);
			}
		}
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
	public function acf_location_rule_values( $values, $rule ) {
		$rule_arr = $this->parse_key( $rule['param'] );
		if ( $rule_arr && in_array( $rule_arr['cpt_name'], array_column( $this->get_cpt_array(), 'name' ) ) ) {
			$args = array(
				'post_type'              => $rule_arr['cpt_name'],
				'posts_per_page'         => 100,
				'paged'                  => 0,
				'orderby'                => 'menu_order title',
				'order'                  => 'ASC',
				'post_status'            => 'any',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);
			$pages = get_posts( $args );

			foreach ( $pages as $page ) {
				$values[ $page->ID ] = $page->post_title;
			}
		}
		return $values;
	}

	/**
	 * Custom ACF filter values.
	 * This adds the options on the right <select>.
	 *
	 * @param bool  $result The match result.
	 * @param array $rule The location rule.
	 * @param array $screen The screen args.
	 * @param array $field_group The field group array.
	 *
	 * @return array
	 */
	public function acf_location_rule_match( $result, $rule, $screen, $field_group ) {

		// Abort if no post ID.
		if ( ! isset( $screen['post_id'] ) ) {
			return $result;
		}

		$post_id   = $screen['post_id'];
		$post_type = get_post_type( $post_id );

		$rule_arr = $this->parse_key( $rule['param'] );
		if ( $rule_arr && $rule_arr['cpt_name'] == $post_type ) {
			switch ( $rule_arr['surfix'] ) {
				case $this->surfix_parent:
					$parent = get_post_parent( $post_id );
					if ( $parent ) {
						return $parent->ID == $rule['value'];
					}
					return false;
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
					'name'   => $cpt->name,
					'label'  => $cpt->label,
					'rules'  => array(
						'parent' => array(
							'key'   => $this->get_key_parent( $cpt->name ),
							'label' => $cpt->label . ' Parent',
						),
						'tree' => array(
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

		array_shift( $arr ); // remove prefix.
		$surfix = array_pop( $arr );
		$cpt_name = join( '_', $arr );
		return array(
			'cpt_name' => $cpt_name,
			'surfix'   => $surfix,
		);
	}
}

new Acf_Rules();
