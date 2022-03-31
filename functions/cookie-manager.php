<?php
/**
 * Cookie management.
 *
 * @package funkhaus
 */

define( 'FUXT_COOKIE_SETTING_SAMESITE', 'fuxt_cookie_samesite' );
define( 'FUXT_COOKIE_SETTING_DOMAIN', 'fuxt_cookie_domain' );

// Init global secure variables.
global $fuxt_secure, $fuxt_secure_logged_in_cookie, $fuxt_is_login;
$fuxt_secure                  = is_ssl();
$fuxt_secure_logged_in_cookie = $fuxt_secure && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );
$fuxt_is_login                = false;

/**
 * Init $fuxt_secure with site secure value.
 *
 * @param bool $secure  Whether the cookie should only be sent over HTTPS.
 * @param int  $user_id User ID.
 *
 * @return bool
 */
function fuxt_secure_auth_cookie( $secure, $user_id ) {
	global $fuxt_secure;
	$fuxt_secure = $secure;
	return $secure;
}
add_filter( 'secure_auth_cookie', 'fuxt_secure_auth_cookie', PHP_INT_MAX, 2 );

/**
 * Init $fuxt_secure_logged_in_cookie with site secure value and set fuxt_is_login true.
 *
 * @param bool $secure_logged_in_cookie Whether the logged in cookie should only be sent over HTTPS.
 * @param int  $user_id                 User ID.
 * @param bool $secure                  Whether the auth cookie should only be sent over HTTPS.
 *
 * @return bool
 */
function fuxt_secure_logged_in_cookie( $secure_logged_in_cookie, $user_id, $secure ) {
	global $fuxt_secure_logged_in_cookie, $fuxt_is_login;
	$fuxt_secure_logged_in_cookie = $secure_logged_in_cookie;
	$fuxt_is_login                = true;
	return $secure_logged_in_cookie;
}
add_filter( 'secure_logged_in_cookie', 'fuxt_secure_logged_in_cookie', PHP_INT_MAX, 3 );

/**
 * Set auth cookie.
 * Fires immediately before the authentication cookie is set.
 *
 * @param string $auth_cookie Authentication cookie value.
 * @param int    $expire      The time the login grace period expires as a UNIX timestamp.
 *                            Default is 12 hours past the cookie's expiration time.
 * @param int    $expiration  The time when the authentication cookie expires as a UNIX timestamp.
 *                            Default is 14 days from now.
 * @param int    $user_id     User ID.
 * @param string $scheme      Authentication scheme. Values include 'auth' or 'secure_auth'.
 * @param string $token       User's session token to use for this cookie.
 *
 * @return void
 */
function fuxt_set_auth_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
	global $fuxt_secure;

	$same_site     = get_option( FUXT_COOKIE_SETTING_SAMESITE, 'None' ); // Lax|Strict|None.
	$cookie_domain = get_option( FUXT_COOKIE_SETTING_DOMAIN, COOKIE_DOMAIN );

	if ( $fuxt_secure ) {
		$auth_cookie_name = SECURE_AUTH_COOKIE;
	} else {
		$auth_cookie_name = AUTH_COOKIE;
	}

	if ( version_compare( PHP_VERSION, '7.3.0' ) >= 0 ) {
		setcookie(
			$auth_cookie_name,
			$auth_cookie,
			array(
				'expires'  => $expire,
				'path'     => PLUGINS_COOKIE_PATH,
				'domain'   => $cookie_domain,
				'secure'   => $fuxt_secure,
				'httponly' => true,
				'samesite' => $same_site,
			)
		);

		setcookie(
			$auth_cookie_name,
			$auth_cookie,
			array(
				'expires'  => $expire,
				'path'     => ADMIN_COOKIE_PATH,
				'domain'   => $cookie_domain,
				'secure'   => $fuxt_secure,
				'httponly' => true,
				'samesite' => $same_site,
			)
		);
	} else {
		setcookie( $auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, $cookie_domain, $fuxt_secure, true );
		setcookie( $auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, $cookie_domain, $fuxt_secure, true );
	}
}
add_action( 'set_auth_cookie', 'fuxt_set_auth_cookie', 10, 6 );

/**
 * Set logged in cookie.
 * Fires immediately before the logged-in authentication cookie is set.
 *
 * @param string $logged_in_cookie The logged-in cookie value.
 * @param int    $expire           The time the login grace period expires as a UNIX timestamp.
 *                                 Default is 12 hours past the cookie's expiration time.
 * @param int    $expiration       The time when the logged-in authentication cookie expires as a UNIX timestamp.
 *                                 Default is 14 days from now.
 * @param int    $user_id          User ID.
 * @param string $scheme           Authentication scheme. Default 'logged_in'.
 * @param string $token            User's session token to use for this cookie.
 */
function fuxt_set_logged_in_cookie( $logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
	global $fuxt_secure_logged_in_cookie;

	$same_site     = get_option( FUXT_COOKIE_SETTING_SAMESITE, 'None' ); // Lax|Strict|None.
	$cookie_domain = get_option( FUXT_COOKIE_SETTING_DOMAIN, COOKIE_DOMAIN );

	if ( version_compare( PHP_VERSION, '7.3.0' ) >= 0 ) {
		setcookie(
			LOGGED_IN_COOKIE,
			$logged_in_cookie,
			array(
				'expires'  => $expire,
				'path'     => COOKIEPATH,
				'domain'   => $cookie_domain,
				'secure'   => $fuxt_secure_logged_in_cookie,
				'httponly' => true,
				'samesite' => $same_site,
			)
		);

		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie(
				LOGGED_IN_COOKIE,
				$logged_in_cookie,
				array(
					'expires'  => $expire,
					'path'     => SITECOOKIEPATH,
					'domain'   => $cookie_domain,
					'secure'   => $fuxt_secure_logged_in_cookie,
					'httponly' => true,
					'samesite' => $same_site,
				)
			);
		}
	} else {
		setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, $cookie_domain, $fuxt_secure_logged_in_cookie, true );
		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, $cookie_domain, $fuxt_secure_logged_in_cookie, true );
		}
	}
}
add_action( 'set_logged_in_cookie', 'fuxt_set_logged_in_cookie', 10, 6 );

/**
 * Set $fuxt_is_login false on logout.
 */
function fuxt_clear_auth_cookie() {
	global $fuxt_is_login;
	$fuxt_is_login = false;
}
add_action( 'clear_auth_cookie', 'fuxt_clear_auth_cookie' );

/**
 * Disable default auth cookie function on login.
 *
 * @param bool $send True.
 */
function fuxt_send_auth_cookies( $send ) {
	global $fuxt_is_login;
	return ! $fuxt_is_login;
}
add_filter( 'send_auth_cookies', 'fuxt_send_auth_cookies' );

/**
 * Registers our Setting for SameSite cookie value.
 */
function fuxt_register_setting() {

	register_setting(
		'general',
		FUXT_COOKIE_SETTING_SAMESITE,
		array(
			'type'              => 'string',
			'group'             => 'general',
			'description'       => 'Authentication Cookie SameSite parameter',
			'sanitize_callback' => 'fuxt_sanitize_value',
			'show_in_rest'      => false,
			'default'           => 'Lax',
		)
	);

	// add Field.
	add_settings_field(
		'fuxt_cookie_samesite-id',
		'Authentication Cookie SameSite parameter',
		'fuxt_setting_samesite_callback_function',
		'general',
		'default',
		array(
			'id'          => 'fuxt_cookie_samesite-id',
			'option_name' => FUXT_COOKIE_SETTING_SAMESITE,
		)
	);

	register_setting(
		'general',
		FUXT_COOKIE_SETTING_DOMAIN,
		array(
			'type'              => 'string',
			'group'             => 'general',
			'description'       => 'Authentication Cookie Domain parameter',
			// 'sanitize_callback' => 'fuxt_sanitize_value',
			'show_in_rest'      => false,
			'default'           => COOKIE_DOMAIN,
		)
	);

	// add Field.
	add_settings_field(
		'fuxt_cookie_domain-id',
		'Authentication Cookie Domain parameter',
		'fuxt_setting_domain_callback_function',
		'general',
		'default',
		array(
			'id'          => 'fuxt_cookie_domain-id',
			'option_name' => FUXT_COOKIE_SETTING_DOMAIN,
		)
	);

}
add_action( 'admin_init', 'fuxt_register_setting' );

/**
 * Sanitizes SameSite value.
 *
 * @param string $val Value to sanitize.
 *
 * @return string
 */
function fuxt_sanitize_value( $val ) {

	$valid_values = fuxt_get_valid_values();

	if ( in_array( $val, $valid_values, true ) ) {
		return $val;
	} else {
		return 'Lax'; // default one.
	}
}

/**
 * Valid values for SameSite Cookie attribute.
 *
 * @return array
 */
function fuxt_get_valid_values() {
	return array(
		'None',
		'Lax',
		'Strict',
	);
}

/**
 * Renders Selector for our SameSite option field.
 *
 * @param array $val Data to render.
 */
function fuxt_setting_samesite_callback_function( $val ) {
	$id           = $val['id'];
	$option_name  = $val['option_name'];
	$option_value = get_option( $option_name );
	$valid_values = fuxt_get_valid_values();
	?>
	<select name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<?php foreach ( $valid_values as $valid_value ) : ?>
			<option value="<?php echo esc_attr( $valid_value ); ?>"  <?php echo esc_attr( $valid_value === $option_value ? ' selected ' : '' ); ?> > <?php echo esc_html( $valid_value ); ?> </option>		<?php endforeach; ?>
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
			<li>`None` if you need to display wp-admin in iframe on other site,</li>
			<li>`Strict` to allow cookie being used only on same site domain </li>
			<li>`Lax` to allow usage on subdomains as well (default is Lax)</li>
		</ul>
	</p>

	<?php
}

/**
 * Renders Input for our Domain option field.
 *
 * @param array $val Data to render.
 */
function fuxt_setting_domain_callback_function( $val ) {
	$id           = $val['id'];
	$option_name  = $val['option_name'];
	$option_value = get_option( $option_name );
	?>
	<input type="text" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $option_value ); ?>">
	<p class="description">
		Be carefully! You won't be able to login to site if you set wrong value.
		Please check <a href="https://datatracker.ietf.org/doc/html/rfc6265#section-5.1.3">RFC 6265 section 5.1.3 Domain Matching</a>
	</p>
	<?php
}
