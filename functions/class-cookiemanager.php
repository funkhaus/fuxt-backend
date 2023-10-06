<?php
/**
 * Cookie management.
 *
 * @package fuxt-backend
 */

/**
 * Cookie manager class.
 */
class CookieManager {

	/**
	 * Is SSL or Not.
	 *
	 * @var boolean
	 */
	private $secure;

	/**
	 * Front-end cookie is secure when the auth cookie is secure and the site's home URL uses HTTPS.
	 *
	 * @var boolean
	 */
	private $secure_logged_in_cookie;

	/**
	 * .
	 *
	 * @var boolean
	 */
	private $send_auth_cookies;

	/**
	 * Init function.
	 */
	public function init() {
		// Init variables.
		$this->secure                  = is_ssl();
		$this->secure_logged_in_cookie = $this->secure && 'https' === wp_parse_url( get_option( 'home' ), PHP_URL_SCHEME );
		$this->send_auth_cookies       = true;

		add_filter( 'secure_auth_cookie', array( $this, 'secure_auth_cookie' ), PHP_INT_MAX, 1 );
		add_filter( 'secure_logged_in_cookie', array( $this, 'secure_logged_in_cookie' ), PHP_INT_MAX, 1 );
		add_action( 'set_auth_cookie', array( $this, 'set_auth_cookie' ), 10, 6 );
		add_action( 'set_logged_in_cookie', array( $this, 'set_logged_in_cookie' ), 10, 6 );
		add_filter( 'send_auth_cookies', array( $this, 'send_auth_cookies' ) );

		add_action( 'clear_auth_cookie', array( $this, 'clear_auth_cookie' ) );
	}

	/**
	 * Init secure with site secure value.
	 *
	 * @param bool $secure  Whether the cookie should only be sent over HTTPS.
	 *
	 * @return bool
	 */
	public function secure_auth_cookie( $secure ) {
		$this->secure = $secure;

		return $secure;
	}

	/**
	 * Init $secure_logged_in_cookie with site secure value.
	 *
	 * @param bool $secure_logged_in_cookie Whether the logged in cookie should only be sent over HTTPS.
	 *
	 * @return bool
	 */
	public function secure_logged_in_cookie( $secure_logged_in_cookie ) {
		$this->secure_logged_in_cookie = $secure_logged_in_cookie;

		return $secure_logged_in_cookie;
	}

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
	public function set_auth_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {

		$same_site     = CookieManagerSettings::get_samesite();
		$cookie_domain = CookieManagerSettings::get_domain();

		$auth_cookie_name = $this->secure ? SECURE_AUTH_COOKIE : AUTH_COOKIE;

		if ( version_compare( PHP_VERSION, '7.3.0' ) >= 0 ) {
			setcookie(
				$auth_cookie_name,
				$auth_cookie,
				array(
					'expires'  => $expire,
					'path'     => PLUGINS_COOKIE_PATH,
					'domain'   => $cookie_domain,
					'secure'   => $this->secure,
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
					'secure'   => $this->secure,
					'httponly' => true,
					'samesite' => $same_site,
				)
			);
		} else {
			setcookie( $auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, $cookie_domain, $this->secure, true );
			setcookie( $auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, $cookie_domain, $this->secure, true );
		}
	}

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
	public function set_logged_in_cookie( $logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token ) {

		$same_site     = CookieManagerSettings::get_samesite();
		$cookie_domain = CookieManagerSettings::get_domain();

		if ( version_compare( PHP_VERSION, '7.3.0' ) >= 0 ) {
			setcookie(
				LOGGED_IN_COOKIE,
				$logged_in_cookie,
				array(
					'expires'  => $expire,
					'path'     => COOKIEPATH,
					'domain'   => $cookie_domain,
					'secure'   => $this->secure_logged_in_cookie,
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
						'secure'   => $this->secure_logged_in_cookie,
						'httponly' => true,
						'samesite' => $same_site,
					)
				);
			}
		} else {
			setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, $cookie_domain, $this->secure_logged_in_cookie, true );
			if ( COOKIEPATH != SITECOOKIEPATH ) {
				setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, $cookie_domain, $this->secure_logged_in_cookie, true );
			}
		}

		// Set to don't send auth cookie again.
		$this->send_auth_cookies = false;
	}

	/**
	 * Disable default auth cookie function on login.
	 *
	 * @param bool $send True.
	 */
	public function send_auth_cookies( $send ) {
		return $this->send_auth_cookies;
	}

	/**
	 * Clear auth cookie.
	 *
	 * @param string $cookie_domain Cookie domain.
	 */
	public function clear_auth_cookie( $cookie_domain = '' ) {
		$this->send_auth_cookies = false;

		if ( $cookie_domain === '' ) {
			$cookie_domain = CookieManagerSettings::get_domain();
		}

		$time = time() - YEAR_IN_SECONDS;

		setcookie( AUTH_COOKIE, ' ', $time, ADMIN_COOKIE_PATH, $cookie_domain );
		setcookie( SECURE_AUTH_COOKIE, ' ', $time, ADMIN_COOKIE_PATH, $cookie_domain );
		setcookie( AUTH_COOKIE, ' ', $time, PLUGINS_COOKIE_PATH, $cookie_domain );
		setcookie( SECURE_AUTH_COOKIE, ' ', $time, PLUGINS_COOKIE_PATH, $cookie_domain );
		setcookie( LOGGED_IN_COOKIE, ' ', $time, COOKIEPATH, $cookie_domain );
		setcookie( LOGGED_IN_COOKIE, ' ', $time, SITECOOKIEPATH, $cookie_domain );
	}

}

( new CookieManager() )->init();
