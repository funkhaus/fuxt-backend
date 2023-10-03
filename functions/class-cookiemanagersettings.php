<?php
/**
 * Cookie management settings.
 *
 * @package fuxt-backend
 */

/**
 * Cookie manager settings class.
 */
class CookieManagerSettings {
	/**
	 * Samesite setting name.
	 */
	const SETTING_SAMESITE = 'fuxt_cookie_samesite';

	/**
	 * Samesite setting name.
	 */
	const SETTING_DOMAIN = 'fuxt_cookie_domain';

	const SETTING_FIELD_ID = 'fuxt_cookie_samesite-id';

	/**
	 * Init function.
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'update_option_' . self::SETTING_DOMAIN, array( $this, 'remove_old_domain_cookies' ), 10, 1 );
		add_action( 'update_option_' . self::SETTING_SAMESITE, array( $this, 'same_site_updated' ), 10, 2 );
	}

	/**
	 * Registers our Setting for SameSite cookie value.
	 */
	public function register_setting() {

		register_setting(
			'general',
			self::SETTING_SAMESITE,
			array(
				'type'              => 'string',
				'group'             => 'general',
				'description'       => 'Authentication Cookie SameSite parameter',
				'sanitize_callback' => array( $this, 'sanitize_value' ),
				'show_in_rest'      => false,
			)
		);

		// add Field.
		add_settings_field(
			self::SETTING_FIELD_ID,
			'Authentication Cookie SameSite parameter',
			array( $this, 'setting_samesite_callback_function' ),
			'general',
			'default',
			array(
				'id'          => self::SETTING_FIELD_ID,
				'option_name' => self::SETTING_SAMESITE,
			)
		);

		register_setting(
			'general',
			self::SETTING_DOMAIN,
			array(
				'type'         => 'boolean',
				'group'        => 'general',
				'description'  => 'Authentication Cookie Domain',
				'show_in_rest' => false,
			)
		);

		// add Field.
		add_settings_field(
			'fuxt_cookie_domain-id',
			'Authentication Cookie Domain',
			array( $this, 'setting_domain_callback_function' ),
			'general',
			'default',
			array(
				'id'          => 'fuxt_cookie_domain-id',
				'option_name' => self::SETTING_DOMAIN,
			)
		);

	}

	/**
	 * Clear old domain cookie and set new domain cookie on cookie setting change.
	 *
	 * @param string $old_value Old value.
	 */
	public function remove_old_domain_cookies( $old_value ) {
		$user_id = get_current_user_id();
		$secure  = is_ssl();

		// Clear old domain cookie.
		if ( empty( $old_value ) ) {
			$old_value = COOKIE_DOMAIN;
		}

		do_action( 'clear_auth_cookie', $old_value );

		// Set new domain cookie.
		wp_set_auth_cookie( $user_id, false, $secure );
	}

	/**
	 * When previous samesite value is Strict, domain was overrided by COOKIE_DOMAIN.
	 * So need to reset cookie with correct domain value.
	 *
	 * @param string $old_same_site Old samesite value.
	 * @param string $new_same_site New samesite value.
	 */
	public function same_site_updated( $old_same_site, $new_same_site ) {
		$domain = get_option( self::SETTING_DOMAIN, COOKIE_DOMAIN );

		if ( 'Strict' === $old_same_site && $domain != COOKIE_DOMAIN ) {
			$this->remove_old_domain_cookies( COOKIE_DOMAIN );
		}

		if ( 'Strict' === $new_same_site ) {
			$this->remove_old_domain_cookies( $domain );
		}
	}

	/**
	 * Sanitizes SameSite value.
	 *
	 * @param string $val Value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_value( $val ) {

		$valid_values = $this->get_valid_values();

		if ( in_array( $val, $valid_values, true ) ) {
			// Do not allow "None" for Non-SSL site.
			if ( ! is_ssl() && 'None' === $val ) {
				return 'Lax';
			}

			return $val;
		} else {
			return 'Lax'; // default one.
		}
	}

	/**
	 * Renders Selector for our SameSite option field.
	 *
	 * @param array $val Data to render.
	 */
	public function setting_samesite_callback_function( $val ) {
		$id           = $val['id'];
		$option_name  = $val['option_name'];
		$option_value = get_option( $option_name );
		$valid_values = $this->get_valid_values();
		?>
		<select name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>">
			<?php foreach ( $valid_values as $valid_value ) : ?>
				<option value="<?php echo esc_attr( $valid_value ); ?>"  <?php echo esc_attr( $valid_value === $option_value ? ' selected ' : '' ); ?> <?php disabled( ! is_ssl() && ( 'None' == $valid_value ) ); ?> > <?php echo esc_html( $valid_value ); ?> </option>		<?php endforeach; ?>
		</select>
		<?php if ( version_compare( PHP_VERSION, '7.3.0' ) < 0 ) : ?>
			<p class="description" style="color: red;">
				Warning: Upgrade to PHP 7.3.0 or above to be able to set SameSite Authentication Cookie,<br>
				Current PHP version is: <?php echo esc_html( PHP_VERSION ); ?><br>
				Otherwise setting will not be applied. <br>
			</p>
		<?php endif; ?>

		<p class="description">
			Authentication Cookie SameSite parameter, Use:
			<ul>
				<li><b>None</b>: if you need to display wp-admin in iframe on other site. You can use this value for HTTPS site only.</li>
				<li><b>Strict</b>: to allow cookie being used only on same site domain </li>
				<li><b>Lax</b>: to allow usage on subdomains as well (default is Lax)</li>
			</ul>
		</p>

		<?php
	}

	/**
	 * Renders Input for our Domain option field.
	 *
	 * @param array $val Data to render.
	 */
	public function setting_domain_callback_function( $val ) {
		$id           = $val['id'];
		$option_name  = $val['option_name'];
		$option_value = get_option( $option_name );
		$wild_cards   = self::get_domain_wildcards();
		?>

		<div id="<?php echo esc_attr( $id ); ?>">
		<?php foreach ( $wild_cards as $wild_card ) : ?>
			<div>
			<label for="fuxt-domain-<?php echo esc_attr( $wild_card ); ?>">
				<input type="radio" name="<?php echo esc_attr( $option_name ); ?>" id="fuxt-domain-<?php echo esc_attr( $wild_card ); ?>" value="<?php echo esc_attr( $wild_card ); ?>" <?php checked( $wild_card, $option_value ); ?>>
				<?php if ( $wild_card ) : ?>
					Enable cookie for <b><?php echo esc_html( $wild_card ); ?></b>
				<?php else : ?>
					Default Cookie Domain
				<?php endif; ?>
			</label>
			</div>
		<?php endforeach; ?>
		<p class="description">
			If <b>SameSite</b> parameter is <b>Strict</b>, this setting is ignored and <b>Default Cookie Domain</b> is used by Default.
		</p>
		</div>

		<?php
	}

	/**
	 * Valid values for SameSite Cookie attribute.
	 *
	 * @return array
	 */
	private function get_valid_values() {
		return array(
			'None',
			'Lax',
			'Strict',
		);
	}

	/**
	 * Get domain wildcard list.
	 *
	 * @return array
	 */
	private static function get_domain_wildcards() {
		$domain_name  = wp_parse_url( site_url(), PHP_URL_HOST );
		$domain_parts = explode( '.', $domain_name );
		$count        = count( $domain_parts );
		$wildcard_arr = array();
		if ( $count >= 2 ) {
			$wildcard_str = '.' . $domain_parts[ $count - 1 ];
			for ( $i = $count - 2; $i >= 0; $i -- ) {
				$wildcard_str = '.' . $domain_parts[ $i ] . $wildcard_str;
				array_unshift( $wildcard_arr, $wildcard_str );
			}
		}

		array_unshift( $wildcard_arr, COOKIE_DOMAIN );

		return $wildcard_arr;
	}

	/**
	 * Get samesite setting value.
	 *
	 * @param string $default Default value.
	 * @return string // one of Lax|Strict|None.
	 */
	public static function get_samesite( $default = '' ) {
		return get_option( self::SETTING_SAMESITE, $default );
	}

	/**
	 * Get domain setting value.
	 *
	 * @return string Domain string value.
	 */
	public static function get_domain() {
		$domain     = get_option( self::SETTING_DOMAIN, COOKIE_DOMAIN );
		$whildcards = self::get_domain_wildcards();
		if ( empty( $domain ) || 'Strict' === self::get_samesite() || ! in_array( $domain, $whildcards ) ) {
			$domain = COOKIE_DOMAIN;
		}

		return $domain;
	}

}

( new CookieManagerSettings() )->init();
