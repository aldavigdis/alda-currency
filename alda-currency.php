<?php
/**
 * Plugin Name:       Alda Currency
 * Description:       Automatically convert between currencies from an interactive block
 * Requires at least: 6.2
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Alda Vigdís
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       alda-currency
 * Domain Path:       alda-currency
 *
 * @package           alda-currency
 */

/**
 * The main AldaCurrency class
 */
class AldaCurrency {
	const CURRENCIES = array(
		'EUR',
		'USD',
		'JPY',
		'GBP',
		'PLN',
		'DKK',
		'NOK',
		'SEK',
		'CHF',
		'CAD',
		'ISK',
	);

	const DEFAULT_BASE_CURRENCY = 'EUR';
	const REST_NAMESPACE        = 'alda-currency/v1';

	/**
	 * The constructor
	 *
	 * Initiates all the hooks what we are going to use in the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'currency_block_init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'rest_api_init', array( $this, 'register_base_currency_rest_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_rates_rest_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_info_rest_route' ) );
		add_action( 'admin_init', array( $this, 'wp_enqueue_admin_script' ) );
		add_action( 'init', array( $this, 'wp_enqueue_frontend_script' ) );
		add_filter( 'script_loader_tag', array( $this, 'load_frontend_script_as_module' ), 10, 3 );
	}

	/**
	 * The activationh hook
	 *
	 * Runs during the plugin activation and sets all the WordPress options.
	 */
	public static function activate_plugin() {
		update_option(
			'alda_currency_base_currency',
			self::DEFAULT_BASE_CURRENCY,
			'no'
		);
		$rates = array();
		foreach ( self::CURRENCIES as $c ) {
			$rates[ $c ] = 1;
		}
		update_option( 'alda_currency_rates', $rates, 'no' );
	}

	/**
	 * Enable JavaScript modules for frontend script
	 *
	 * Hooks into the wp_enqueue_script function to enable JavaScript modules to
	 * run on the WordPress frontend.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The handle paramter of the wp_enqueue_script call.
	 * @param string $src    The src parameter of the script tag.
	 */
	public function load_frontend_script_as_module( $tag, $handle, $src ) {
		if ( 'alda-currency-converter-frontend' === $handle ) {
			$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		}
		return $tag;
	}

	/**
	 * Enqueue the JavaScript frontend bits
	 *
	 * This enqueues the JS module that enables us to render the interactive
	 * block in the site frontend.
	 */
	public function wp_enqueue_frontend_script() {
		wp_enqueue_script(
			'alda-currency-converter-frontend',
			plugins_url( 'alda-currency/build/frontend.js' ),
			array( 'wp-api', 'react', 'react-dom' ),
			'0.1.0',
			false
		);
	}

	/**
	 * Eneueue scripts and stylesheets used in the admin interface
	 */
	public function wp_enqueue_admin_script() {
		wp_enqueue_script(
			'alda-currency-admin-view',
			plugins_url( 'alda-currency/build/admin.js' ),
			array( 'wp-api' ),
			'0.1.0',
			false
		);
		wp_enqueue_style(
			'alda-currency-admin-style',
			plugins_url( 'alda-currency/build/admin.css' ),
			array(),
			'0.1.0',
			false
		);
	}

	/**
	 * Register the public-facing info JSON endpoint
	 *
	 * This one is used bv the CurrencyConverter React component to read
	 * information about the base currency and the enabled currency rates.
	 */
	public function register_info_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/info',
			array(
				'callback'            => array( $this, 'get_info_rest_endpoint' ),
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * The callback for the info endpoint
	 */
	public function get_info_rest_endpoint() {
		$all_rates          = $this->rates();
		$enabled_currencies = $this->enabled_currencies();
		$enabled_rates      = array();

		foreach ( $all_rates as $currency => $rate ) {
			if ( in_array( $currency, $enabled_currencies, true ) ) {
				$enabled_rates[ $currency ] = $rate;
			}
		}

		return array(
			'base_currency' => $this->get_base_currency(),
			'rates'         => $enabled_rates,
		);
	}

	/**
	 * Register the rates JSON API route
	 *
	 * This enables site administrators to set and get the current currency
	 * rates via wp-api.
	 */
	public function register_rates_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/rates',
			array(
				'callback'            => array(
					$this,
					'set_currency_rates_from_rest_endpoint',
				),
				'methods'             => 'POST',
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/rates',
			array(
				'callback'            => array(
					$this,
					'get_currency_rates_for_rest_endpoint',
				),
				'methods'             => 'GET',
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * The POST callback for the /rates JSON endpoint
	 *
	 * @param WP_REST_Request $request The WP REST request object.
	 */
	public function set_currency_rates_from_rest_endpoint( WP_REST_Request $request ) {
		$request_body = (array) json_decode( $request->get_body() );
		$this->set_rates( $request_body['rates'] );
		$this->set_enabled_currencies( $request_body['enabledCurrencies'] );
		return $this->get_info_rest_endpoint();
	}

	/**
	 * The GET callbackk for the /rates JSON endpoint
	 */
	public function get_currency_rates_for_rest_endpoint() {
		return $this->rates();
	}

	/**
	 * Register the base currency JSON endpoint
	 */
	public function register_base_currency_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/base-currency',
			array(
				'callback'            => array(
					$this,
					'set_base_currency_from_rest_request_body',
				),
				'methods'             => 'POST',
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/base-currency',
			array(
				'callback'            => array(
					$this,
					'get_base_currency_from_rest_request_body',
				),
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * The GET callback for the /base-currency endpoint
	 */
	public function get_base_currency_from_rest_request_body() {
		return $this->get_base_currency();
	}

	/**
	 * The POST callback for the /base-currency endpoint
	 *
	 * @param WP_REST_Request $request The WP REST request object.
	 */
	public function set_base_currency_from_rest_request_body( WP_REST_Request $request ) {
		$request_body  = json_decode( $request->get_body() );
		$currency_code = $request_body->currency_code;

		if ( in_array( $currency_code, self::CURRENCIES, true ) ) {
			$this->set_base_currency( $currency_code );
			return true;
		}

		return false;
	}

	/**
	 * Register the currency converter block
	 */
	public function currency_block_init() {
		register_block_type( __DIR__ . '/build' );
	}

	/**
	 * Add the admin page to the wp-admin sidebar menu
	 */
	public function add_menu_page() {
		add_menu_page(
			'Alda Currency',
			'Alda Currency',
			'manage_options',
			'alda-currency',
			array( $this, 'render_admin_page' ),
			'dashicons-money-alt',
			91
		);
	}

	/**
	 * Render the admin page
	 */
	public function render_admin_page() {
		if ( false === current_user_can( 'manage_options' ) ) {
			return false;
		}

		require __DIR__ . '/views/main-page.php';
	}

	/**
	 * Initializes/resets the base currency value
	 */
	public function initialize_base_currency() {
		return $this->set_base_currency( self::DEFAULT_BASE_CURRENCY );
	}

	/**
	 * Set the base currency to a certain value
	 *
	 * @param string $currency_code The ISO-alpha-3 values for the currency codes.
	 */
	public function set_base_currency( string $currency_code ) {
		return update_option(
			'alda_currency_base_currency',
			$currency_code,
			'no'
		);
	}

	/**
	 * Get the base currency code
	 */
	public function get_base_currency() {
		return get_option( 'alda_currency_base_currency' );
	}

	/**
	 * Enable all currencies
	 *
	 * This displays every available currency in the client-side rendered block
	 * and the JSON API endpoints.
	 */
	public function enable_all_currencies() {
		return update_option(
			'alda_currency_enabled_currencies',
			self::CURRENCIES,
			'no'
		);
	}

	/**
	 * Initialize/reset currency rates
	 *
	 * Set the rate for every currency to '1.00'.
	 */
	public function initialize_rates() {
		$rates = array();
		foreach ( self::CURRENCIES as $c ) {
			$rates[ $c ] = 1;
		}
		return update_option(
			'alda_currency_rates',
			$rates,
			'no'
		);
	}

	/**
	 * Set the available currencies
	 *
	 * @param array $currencies An array of ISO-alpha-3 currency codes to enable.
	 */
	public function set_enabled_currencies( array $currencies ) {
		return update_option(
			'alda_currency_enabled_currencies',
			$currencies,
			'no'
		);
	}

	/**
	 * Set the currency rates
	 *
	 * @param array $rates An associative array of currency rates, with the ISO-alpha-3 code as the key for each.
	 */
	public function set_rates( $rates ) {
		return update_option( 'alda_currency_rates', $rates, 'no' );
	}

	/**
	 * Get all the set currency rates
	 *
	 * @return array An associative array of currency values, with the ISO-alpha-3 code as the key for each.
	 */
	public function rates() {
		$rates = get_option( 'alda_currency_rates' );
		if ( false === $rates ) {
			$this->initialize_rates();
			return get_option( 'alda_currency_rates' );
		}
		return (array) $rates;
	}

	/**
	 * Get the ISO-alpha-3 codes for all the enabled currencies
	 *
	 * @return array An array of string values.
	 */
	public function enabled_currencies() {
		$enabled_currencies = get_option( 'alda_currency_enabled_currencies' );
		if ( false === $enabled_currencies ) {
			return self::CURRENCIES;
		}
		return $enabled_currencies;
	}

	/**
	 * Get the path to a country flag associated with the currency
	 *
	 * This is based on the fact that the ISO alpha-2 country code is the first
	 * two digits of each alpha-3 currency code.
	 *
	 * @param string $currency_code The currency code.
	 *
	 * @return string The relative path to an SVG image file.
	 */
	public function currency_flag( string $currency_code ) {
		$country_code = substr( $currency_code, 0, 2 );
		$flags_path   = plugins_url(
			'alda-currency/flags/4x3/'
		);
		return $flags_path . $country_code . '.svg';
	}
}

$alda_currency = new AldaCurrency();

register_activation_hook( __FILE__, array( 'AldaCurrency', 'activate_plugin' ) );
