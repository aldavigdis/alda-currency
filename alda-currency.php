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
		'ISK'
	);

	const DEFAULT_BASE_CURRENCY = 'EUR';
	const REST_NAMESPACE        = 'alda-currency/v1';

	function __construct() {
		add_action( 'init', array( $this, 'currency_block_init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'rest_api_init', array( $this, 'register_base_currency_rest_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_rates_rest_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_info_rest_route' ) );
		add_action( 'admin_init', array( $this, 'wp_enqueue_admin_script' ) );
		add_action( 'init', array( $this, 'wp_enqueue_frontend_script' ) );
		add_filter( 'script_loader_tag', array( $this, 'load_frontend_script_as_module'), 10, 3 );
	}

	static function activate_plugin() {
		update_option(
			'alda_currency_base_currency',
			AldaCurrency::DEFAULT_BASE_CURRENCY,
			'no'
		);
		$rates = array();
		foreach( self::CURRENCIES as $c ) {
			$rates[$c] = 1;
		}
		update_option( 'alda_currency_rates', $rates, 'no' );
	}

	function load_frontend_script_as_module($tag, $handle, $src) {
		if ("alda-currency-converter-frontend" === $handle) {
			$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		}
		return $tag;
	}

	function wp_enqueue_frontend_script() {
		wp_enqueue_script(
			'alda-currency-converter-frontend',
			plugins_url( 'alda-currency/build/frontend.js' ),
			array( 'wp-api', 'react', 'react-dom' )
		);
	}

	function wp_enqueue_admin_script() {
		wp_enqueue_script(
			'alda-currency-admin-view',
			plugins_url( 'alda-currency/build/admin.js' ),
			array( 'wp-api' )
		);
		wp_enqueue_style(
			'alda-currency-admin-style',
			plugins_url( 'alda-currency/build/admin.css' )
		);
	}

	function register_info_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/info',
			array(
				'callback' => array( $this, 'get_info_rest_endpoint' ),
				'methods' => 'GET',
				'permission_callback' => '__return_true'
			)
		);
	}

	function get_info_rest_endpoint() {
		$all_rates          = $this->rates();
		$enabled_currencies = $this->enabled_currencies();
		$enabled_rates      = [];

		foreach ($all_rates as $currency => $rate) {
			if ( in_array( $currency, $enabled_currencies ) ) {
				$enabled_rates[$currency] = $rate;
			}
		}

		return array(
			'base_currency' => $this->get_base_currency(),
			'rates'         => $enabled_rates
		);
	}

	function register_rates_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/rates',
			array(
				'callback' => array(
					$this,
					'set_currency_rates_from_rest_endpoint'
				),
				'methods' => 'POST',
				'permission_callback' => '__return_true'
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/rates',
			array(
				'callback' => array(
					$this,
					'get_currency_rates_for_rest_endpoint'
				),
				'methods' => 'GET',
				'permission_callback' => '__return_true'
			)
		);
	}

	function set_currency_rates_from_rest_endpoint(WP_REST_Request $request) {
		$request_body = (array) json_decode($request->get_body());
		$this->set_rates($request_body['rates']);
		$this->set_enabled_currencies( $request_body['enabledCurrencies']);
		return $this->get_info_rest_endpoint();
	}

	function get_currency_rates_for_rest_endpoint() {
		return $this->rates();
	}

	function register_base_currency_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/base-currency',
			array(
				'callback' => array(
					$this,
					'set_base_currency_from_rest_request_body'
				),
				'methods' => 'POST',
				'permission_callback' => '__return_true'
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/base-currency',
			array(
				'callback' => array(
					$this,
					'get_base_currency_from_rest_request_body'
				),
				'methods' => 'GET',
				'permission_callback' => '__return_true'
			)
		);
	}

	function get_base_currency_from_rest_request_body() {
		return $this->get_base_currency();
	}

	function set_base_currency_from_rest_request_body(WP_REST_Request $request) {
		$request_body = json_decode($request->get_body());
		$currency_code = $request_body->currency_code;

		if ( in_array( $currency_code, self::CURRENCIES ) ) {
			$this->set_base_currency( $currency_code );
			return true;
		}

		return false;
	}

	function currency_block_init() {
		register_block_type( __DIR__ . '/build' );
	}

	function add_menu_page() {
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

	function render_admin_page() {
		if ( false === current_user_can( 'manage_options' ) ) {
			return false;
		}
		require __DIR__ . '/views/main-page.php';
	}

	function initialize_base_currency() {
		return $this->set_base_currency( self::DEFAULT_BASE_CURRENCY );
	}

	function set_base_currency( $currency_code ) {
		return update_option(
			'alda_currency_base_currency',
			$currency_code,
			'no'
		);
	}

	function get_base_currency() {
		return get_option('alda_currency_base_currency');
	}

	function enable_all_currencies() {
		return update_option(
			'alda_currency_enabled_currencies',
			self::CURRENCIES,
			'no'
		);
	}

	function initialize_rates() {
		$rates = array();
		foreach( self::CURRENCIES as $c ) {
			$rates[$c] = 1;
		}
		return update_option(
			'alda_currency_rates',
			$rates,
			'no'
		);
	}

	function set_enabled_currencies(array $currencies) {
		return update_option(
			'alda_currency_enabled_currencies',
			$currencies,
			'no'
		);
	}

	function set_rates($rates) {
		return update_option('alda_currency_rates', $rates, 'no');
	}

	function rates() {
		$rates = get_option('alda_currency_rates');
		if ( false === $rates ) {
			$this->initialize_rates();
			return get_option('alda_currency_rates');
		}
		return (array) $rates;
	}

	function enabled_currencies() {
		$enabled_currencies = get_option('alda_currency_enabled_currencies');
		if ( false === $enabled_currencies ) {
			return self::CURRENCIES;
		}
		return $enabled_currencies;
	}

	function currency_flag( $currency_code ) {
		$country_code = substr( $currency_code, 0, 2 );
		$flags_path   = plugins_url(
			'alda-currency/flags/4x3/'
		);
		return $flags_path . $country_code . '.svg';
	}
}

$alda_currency = new AldaCurrency();

register_activation_hook( __FILE__, array('AldaCurrency', 'activate_plugin') );
