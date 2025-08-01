<?php
/**
 * Plugin Name: WooCommerce Tax
 * Requires Plugins: woocommerce
 * Plugin URI: https://woocommerce.com/
 * Description: Automated tax calculation for WooCommerce.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-services
 * Domain Path: /i18n/languages/
 * Version: 3.0.5
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.6
 * Tested up to: 6.8
 * WC requires at least: 9.8
 * WC tested up to: 10.0
 *
 * Copyright (c) 2017-2023 Automattic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * WooCommerce Tax incorporates code from WooCommerce Sales Tax Plugin by TaxJar, Copyright 2014-2017 TaxJar.
 * WooCommerce Sales Tax Plugin by TaxJar is distributed under the terms of the GNU GPL, Version 2 (or later).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload_packages.php';

require_once __DIR__ . '/classes/class-wc-connect-extension-compatibility.php';
require_once __DIR__ . '/classes/class-wc-connect-functions.php';
require_once __DIR__ . '/classes/class-wc-connect-jetpack.php';
require_once __DIR__ . '/classes/class-wc-connect-options.php';

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'WC_Connect_Loader' ) ) {
	define( 'WOOCOMMERCE_CONNECT_MINIMUM_WOOCOMMERCE_VERSION', '2.6' );
	define( 'WOOCOMMERCE_CONNECT_MAX_JSON_DECODE_DEPTH', 32 );

	if ( ! defined( 'WOOCOMMERCE_CONNECT_SERVER_API_VERSION' ) ) {
		define( 'WOOCOMMERCE_CONNECT_SERVER_API_VERSION', '5' );
	}

	class WC_Connect_Loader {

		/**
		 * @var WC_Connect_Logger
		 */
		protected $logger;

		/**
		 * @var WC_Connect_Logger
		 */
		protected $shipping_logger;

		/**
		 * @var WC_Connect_API_Client
		 */
		protected $api_client;

		/**
		 * @var WC_Connect_Service_Schemas_Store
		 */
		protected $service_schemas_store;

		/**
		 * @var WC_Connect_Service_Settings_Store
		 */
		protected $service_settings_store;

		/**
		 * @var WC_Connect_Payment_Methods_Store
		 */
		protected $payment_methods_store;

		/**
		 * @var WC_REST_Connect_Account_Settings_Controller
		 */
		protected $rest_account_settings_controller;

		/**
		 * @var WC_REST_Connect_Packages_Controller
		 */
		protected $rest_packages_controller;

		/**
		 * @var WC_REST_Connect_Services_Controller
		 */
		protected $rest_services_controller;

		/**
		 * @var WC_REST_Connect_Self_Help_Controller
		 */
		protected $rest_self_help_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Label_Controller
		 */
		protected $rest_shipping_label_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Label_Status_Controller
		 */
		protected $rest_shipping_label_status_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Label_Refund_Controller
		 */
		protected $rest_shipping_label_refund_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Label_Preview_Controller
		 */
		protected $rest_shipping_label_preview_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Label_Print_Controller
		 */
		protected $rest_shipping_label_print_controller;

		/**
		 * @var WC_REST_Connect_Shipping_Rates_Controller
		 */
		protected $rest_shipping_rates_controller;

		/**
		 * @var WC_REST_Connect_Address_Normalization_Controller
		 */
		protected $rest_address_normalization_controller;

		/**
		 *
		 * WC_REST_Connect_Shipping_Carrier_Types_Controller
		 *
		 * @var WC_REST_Connect_Shipping_Carrier_Types_Controller
		 */
		protected $rest_carrier_types_controller;

		/**
		 * WC_REST_Connect_Assets_Controller
		 *
		 * @var WC_REST_Connect_Assets_Controller
		 */
		protected $rest_assets_controller;

		/**
		 * WC_REST_Connect_Shipping_Carrier_Controller
		 *
		 * @var WC_REST_Connect_Shipping_Carrier_Controller
		 */
		protected $rest_carrier_controller;

		/**
		 * WC_REST_Connect_Shipping_Carriers_Controller
		 *
		 * @var WC_REST_Connect_Shipping_Carriers_Controller
		 */
		protected $rest_carriers_controller;

		/**
		 * WC_REST_Connect_Subscriptions_Controller
		 *
		 * @var WC_REST_Connect_Subscriptions_Controller
		 */
		protected $rest_subscriptions_controller;

		/**
		 * WC_REST_Connect_Subscription_Activate_Controller
		 *
		 * @var WC_REST_Connect_Subscription_Activate_Controller
		 */
		protected $rest_subscription_activate_controller;

		/**
		 * WC_REST_Connect_Shipping_Carrier_Delete_Controller
		 *
		 * @var WC_REST_Connect_Shipping_Carrier_Delete_Controller
		 */
		protected $rest_carrier_delete_controller;

		/**
		 * WC_REST_Connect_Migration_Flag_Controller
		 *
		 * @var WC_REST_Connect_Migration_Flag_Controller
		 */
		protected $rest_migration_flag_controller;

		/**
		 * @var WC_Connect_Service_Schemas_Validator
		 */
		protected $service_schemas_validator;

		/**
		 * @var WC_Connect_Settings_Pages
		 */
		protected $settings_pages;

		/**
		 * @var WC_Connect_Help_View
		 */
		protected $help_view;

		/**
		 * @var WC_Connect_Shipping_Label
		 */
		protected $shipping_label;

		/**
		 * @var WC_Connect_Nux
		 */
		protected $nux;

		/**
		 * @var WC_Connect_TaxJar_Integration
		 */
		protected $taxjar;

		/**
		 * @var WC_Connect_PayPal_EC
		 */
		protected $paypal_ec;

		/**
		 * @var WC_Connect_Tracks
		 */
		protected $tracks;

		/**
		 * @var WC_Connect_Label_Reports
		 */
		protected $label_reports;

		/**
		 * @var WC_REST_Connect_Tos_Controller
		 */
		protected $rest_tos_controller;

		protected $services = array();

		protected $service_object_cache = array();

		protected $wc_connect_base_url;

		protected static $wcs_version;

		public const MIGRATION_DISMISSAL_COOKIE_KEY = 'wcst-wcshipping-migration-dismissed';

		public static function plugin_deactivation() {
			wp_clear_scheduled_hook( 'wc_connect_fetch_service_schemas' );

			/*
			 * When we deactivate the plugin after wcshipping_migration_state has started,
			 * that means the migration is done. We can mark it as completed before we deactivate the plugin.
			 */
			require_once __DIR__ . '/classes/class-wc-connect-logger.php';
			require_once __DIR__ . '/classes/class-wc-connect-tracks.php';
			require_once __DIR__ . '/classes/class-wc-connect-wcst-to-wcshipping-migration-state-enum.php';

			$migration_state = intval( get_option( 'wcshipping_migration_state' ) );
			if ( $migration_state === WC_Connect_WCST_To_WCShipping_Migration_State_Enum::COMPLETED ) {
				$core_logger = new WC_Logger();
				$logger      = new WC_Connect_Logger( $core_logger );
				$tracks      = new WC_Connect_Tracks( $logger, __FILE__ );
				$tracks->record_user_event(
					'migration_flag_state_update',
					array(
						'migration_state' => $migration_state,
						'updated'         => false,
					)
				);
			}
		}

		public static function plugin_uninstall() {
			WC_Connect_Options::delete_all_options();
			self::delete_notices();
		}

		/**
		 * Deletes WC Admin notices.
		 */
		public static function delete_notices() {
			if ( self::can_add_wc_admin_notice() ) {
				require_once __DIR__ . '/classes/class-wc-connect-note-dhl-live-rates-available.php';
				WC_Connect_Note_DHL_Live_Rates_Available::possibly_delete_note();
			}
		}
		/**
		 * Checks if WC Admin is active and includes needed classes.
		 *
		 * @return bool true|false.
		 */
		public static function can_add_wc_admin_notice() {
			if ( ! class_exists( 'WC_Data_Store' ) ) {
				return false;
			}

			try {
				WC_Data_Store::load( 'admin-note' );
			} catch ( Exception $e ) {
				return false;
			}

			return trait_exists( 'Automattic\WooCommerce\Admin\Notes\NoteTraits' ) && class_exists( 'Automattic\WooCommerce\Admin\Notes\Note' );
		}

		/**
		 * Get WCS plugin version
		 *
		 * @return string
		 */
		public static function get_wcs_version() {
			if ( null === self::$wcs_version ) {
				$plugin_data       = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
				self::$wcs_version = $plugin_data['Version'];
			}
			return self::$wcs_version;
		}

		/**
		 * Get base url.
		 *
		 * @return string
		 */
		private static function get_wc_connect_base_url() {
			return trailingslashit( defined( 'WOOCOMMERCE_CONNECT_DEV_SERVER_URL' ) ? WOOCOMMERCE_CONNECT_DEV_SERVER_URL : plugins_url( 'dist/', __FILE__ ) );
		}

		/**
		 * Get WCS admin script url.
		 *
		 * @return string
		 */
		public static function get_wcs_admin_script_url() {
			return self::get_wc_connect_base_url() . 'woocommerce-services-' . self::get_wcs_version() . '.js';
		}

		/**
		 * Get WCS admin css url.
		 *
		 * @return string
		 */
		public static function get_wcs_admin_style_url() {
			if ( ! defined( 'WOOCOMMERCE_CONNECT_DEV_SERVER_URL' ) ) {
				return self::get_wc_connect_base_url() . 'woocommerce-services-' . self::get_wcs_version() . '.css';
			} else {
				return '';
			}
		}

		public function __construct() {
			$this->wc_connect_base_url = self::get_wc_connect_base_url();
			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-services/woocommerce-services.php' );
					}
				}
			);

			add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
			add_action( 'after_setup_theme', array( $this, 'ensure_jetpack_connection' ), 1 );

			/**
			 * Used to let WC Tax know WCS&T will handle the plugin's coexistence.
			 *
			 * WCS&T does it by not registering its functionality and displaying an appropriate notice
			 * in WP admin.
			 *
			 * The filter also includes "woo_shipping" in its name, but we've since implemented conditional
			 * loading of all shipping functionality inside WCS&T, so there's no special need for any
			 * "parallel support" being done in WC Shipping anymore.
			 *
			 * This new feature is represented via the "wc_services_will_disable_shipping_logic" hook.
			 */
			if ( $this->are_woo_shipping_and_woo_tax_active() ) {
				add_filter( 'wc_services_will_handle_coexistence_with_woo_shipping_and_woo_tax', '__return_true' );
			}

			// Let WC Shipping know that the current version of WCS&T supports conditional shipping logic loading.
			add_filter( 'wc_services_will_disable_shipping_logic', '__return_true' );
		}

		public function get_logger() {
			return $this->logger;
		}

		public function set_logger( WC_Connect_Logger $logger ) {
			$this->logger = $logger;
		}

		public function get_shipping_logger() {
			return $this->shipping_logger;
		}

		public function set_shipping_logger( WC_Connect_Logger $logger ) {
			$this->shipping_logger = $logger;
		}

		public function get_api_client() {
			return $this->api_client;
		}

		public function set_api_client( WC_Connect_API_Client $api_client ) {
			$this->api_client = $api_client;
		}

		public function get_service_schemas_store() {
			return $this->service_schemas_store;
		}

		public function set_service_schemas_store( WC_Connect_Service_Schemas_Store $schemas_store ) {
			$this->service_schemas_store = $schemas_store;
		}

		public function get_service_settings_store() {
			return $this->service_settings_store;
		}

		public function set_service_settings_store( WC_Connect_Service_Settings_Store $settings_store ) {
			$this->service_settings_store = $settings_store;
		}

		public function get_payment_methods_store() {
			return $this->payment_methods_store;
		}

		public function set_payment_methods_store( WC_Connect_Payment_Methods_Store $payment_methods_store ) {
			$this->payment_methods_store = $payment_methods_store;
		}

		public function get_tracks() {
			return $this->tracks;
		}

		public function set_tracks( WC_Connect_Tracks $tracks ) {
			$this->tracks = $tracks;
		}

		public function get_rest_account_settings_controller() {
			return $this->rest_account_settings_controller;
		}

		public function set_rest_tos_controller( WC_REST_Connect_Tos_Controller $rest_tos_controller ) {
			$this->rest_tos_controller = $rest_tos_controller;
		}

		public function set_rest_assets_controller( WC_REST_Connect_Assets_Controller $rest_assets_controller ) {
			$this->rest_assets_controller = $rest_assets_controller;
		}

		public function set_rest_carriers_controller( WC_REST_Connect_Shipping_Carriers_Controller $rest_carriers_controller ) {
			$this->rest_carriers_controller = $rest_carriers_controller;
		}

		public function set_rest_subscriptions_controller( WC_REST_Connect_Subscriptions_Controller $rest_subscriptions_controller ) {
			$this->rest_subscriptions_controller = $rest_subscriptions_controller;
		}

		public function set_rest_subscription_activate_controller( WC_REST_Connect_Subscription_Activate_Controller $rest_subscription_activate_controller ) {
			$this->rest_subscription_activate_controller = $rest_subscription_activate_controller;
		}

		public function set_rest_carrier_controller( WC_REST_Connect_Shipping_Carrier_Controller $rest_carrier_controller ) {
			$this->rest_carrier_controller = $rest_carrier_controller;
		}

		public function set_rest_carrier_delete_controller( WC_REST_Connect_Shipping_Carrier_Delete_Controller $rest_carrier_delete_controller ) {
			$this->rest_carrier_delete_controller = $rest_carrier_delete_controller;
		}

		public function set_rest_packages_controller( WC_REST_Connect_Packages_Controller $rest_packages_controller ) {
			$this->rest_packages_controller = $rest_packages_controller;
		}

		public function set_rest_account_settings_controller( WC_REST_Connect_Account_Settings_Controller $rest_account_settings_controller ) {
			$this->rest_account_settings_controller = $rest_account_settings_controller;
		}

		public function get_rest_services_controller() {
			return $this->rest_services_controller;
		}

		public function set_rest_services_controller( WC_REST_Connect_Services_Controller $rest_services_controller ) {
			$this->rest_services_controller = $rest_services_controller;
		}

		public function get_rest_self_help_controller() {
			return $this->rest_self_help_controller;
		}

		public function set_rest_self_help_controller( WC_REST_Connect_Self_Help_Controller $rest_self_help_controller ) {
			$this->rest_self_help_controller = $rest_self_help_controller;
		}

		public function get_rest_shipping_label_controller() {
			return $this->rest_shipping_label_controller;
		}

		public function set_rest_shipping_label_controller( WC_REST_Connect_Shipping_Label_Controller $rest_shipping_label_controller ) {
			$this->rest_shipping_label_controller = $rest_shipping_label_controller;
		}

		public function get_rest_shipping_label_status_controller() {
			return $this->rest_shipping_label_status_controller;
		}

		public function set_rest_shipping_label_status_controller( WC_REST_Connect_Shipping_Label_Status_Controller $rest_shipping_label_status_controller ) {
			$this->rest_shipping_label_status_controller = $rest_shipping_label_status_controller;
		}

		public function get_rest_shipping_label_refund_controller() {
			return $this->rest_shipping_label_refund_controller;
		}

		public function set_rest_shipping_label_refund_controller( WC_REST_Connect_Shipping_Label_Refund_Controller $rest_shipping_label_refund_controller ) {
			$this->rest_shipping_label_refund_controller = $rest_shipping_label_refund_controller;
		}

		public function get_rest_shipping_label_preview_controller() {
			return $this->rest_shipping_label_preview_controller;
		}

		public function set_rest_shipping_label_preview_controller( WC_REST_Connect_Shipping_Label_Preview_Controller $rest_shipping_label_preview_controller ) {
			$this->rest_shipping_label_preview_controller = $rest_shipping_label_preview_controller;
		}

		public function get_rest_shipping_label_print_controller() {
			return $this->rest_shipping_label_print_controller;
		}

		public function set_rest_shipping_label_print_controller( WC_REST_Connect_Shipping_Label_Print_Controller $rest_shipping_label_print_controller ) {
			$this->rest_shipping_label_print_controller = $rest_shipping_label_print_controller;
		}

		public function set_rest_shipping_rates_controller( WC_REST_Connect_Shipping_Rates_Controller $rest_shipping_rates_controller ) {
			$this->rest_shipping_rates_controller = $rest_shipping_rates_controller;
		}

		public function set_rest_address_normalization_controller( WC_REST_Connect_Address_Normalization_Controller $rest_address_normalization_controller ) {
			$this->rest_address_normalization_controller = $rest_address_normalization_controller;
		}

		public function set_carrier_types_controller( WC_REST_Connect_Shipping_Carrier_Types_Controller $rest_carrier_types_controller ) {
			$this->rest_carrier_types_controller = $rest_carrier_types_controller;
		}

		public function set_rest_migration_flag_controller( WC_REST_Connect_Migration_Flag_Controller $rest_migration_flag_controller ) {
			$this->rest_migration_flag_controller = $rest_migration_flag_controller;
		}

		public function get_carrier_types_controller() {
			return $this->rest_carrier_types_controller;
		}

		public function get_service_schemas_validator() {
			return $this->service_schemas_validator;
		}

		public function set_service_schemas_validator( WC_Connect_Service_Schemas_Validator $validator ) {
			$this->service_schemas_validator = $validator;
		}

		public function get_settings_pages() {
			return $this->settings_pages;
		}

		public function set_settings_pages( WC_Connect_Settings_Pages $settings_pages ) {
			$this->settings_pages = $settings_pages;
		}

		public function get_help_view() {
			return $this->help_view;
		}

		public function set_help_view( WC_Connect_Help_View $help_view ) {
			$this->help_view = $help_view;
		}

		public function set_shipping_label( WC_Connect_Shipping_Label $shipping_label ) {
			$this->shipping_label = $shipping_label;
		}

		public function set_nux( WC_Connect_Nux $nux ) {
			$this->nux = $nux;
		}

		public function set_taxjar( WC_Connect_TaxJar_Integration $taxjar ) {
			$this->taxjar = $taxjar;
		}

		public function set_paypal_ec( WC_Connect_PayPal_EC $paypal_ec ) {
			$this->paypal_ec = $paypal_ec;
		}

		public function set_label_reports( WC_Connect_Label_Reports $label_reports ) {
			$this->label_reports = $label_reports;
		}

		/**
		 * Load our textdomain
		 *
		 * @codeCoverageIgnore
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'woocommerce-services', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
		}

		public function ensure_jetpack_connection() {
			$jetpack_config = new Automattic\Jetpack\Config();
			$jetpack_config->ensure(
				'connection',
				array(
					'slug' => WC_Connect_Jetpack::JETPACK_PLUGIN_SLUG,
					'name' => $this->get_plugin_name_for_new_sites(),
				)
			);
		}

		public function on_plugins_loaded() {

			/**
			 * Allow third party logic to determine if this plugin should initiate its logic.
			 *
			 * The primary purpose here is to allow a smooth transition between the new WC Tax plugin
			 * and WooCommerce Tax (this plugin), by letting them take over all responsibilities if all three
			 * plugins are activated at the same time.
			 *
			 * @since {{next-release}}
			 *
			 * @param bool $status The value will determine if we should initiate the plugins logic or not.
			 */
			if ( apply_filters( 'wc_services_will_handle_coexistence_with_woo_shipping_and_woo_tax', false ) ) {
				// Show message that WCS&T is no longer doing anything.
				add_action( 'admin_notices', array( $this, 'display_woo_shipping_and_woo_tax_are_active_notice' ) );

				// Bail, so none of our tax and shipping logic will be initiated.
				return;
			}

			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action(
					'admin_notices',
					function () {
						/* translators: %s WC download URL link. */
						echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'WooCommerce Tax requires the WooCommerce plugin to be installed and active. You can download %s here.', 'woocommerce-services' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
					}
				);
				return;
			}

			add_action( 'before_woocommerce_init', array( $this, 'pre_wc_init' ) );
		}

		/**
		 * Perform plugin bootstrapping that needs to happen before WC init.
		 *
		 * This allows the modification of extensions, integrations, etc.
		 */
		public function pre_wc_init() {
			$this->load_textdomain();
			$this->load_dependencies();

			$tos_accepted = WC_Connect_Options::get_option( 'tos_accepted' );

			// Prevent presenting users with TOS they've already
			// accepted in the core WC Setup Wizard or on WP.com.
			if ( ! $tos_accepted &&
			( get_option( 'woocommerce_setup_jetpack_opted_in' ) || WC_Connect_Jetpack::is_atomic_site() )
			) {
				WC_Connect_Options::update_option( 'tos_accepted', true );
				delete_option( 'woocommerce_setup_jetpack_opted_in' );

				$tos_accepted = true;
			}

			add_action( 'admin_init', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_init', array( $this->nux, 'set_up_nux_notices' ) );
			add_filter( 'all_plugins', array( $this, 'maybe_rename_plugin' ) );

			if ( WC_Connect_Nux::JETPACK_NOT_CONNECTED === $this->nux->get_jetpack_install_status() ) {
				return;
			}

			add_action( 'rest_api_init', array( $this, 'tos_rest_init' ) );

			// The entire plugin should be enabled if dev mode or connected + TOS.
			if ( ! $tos_accepted ) {
				return;
			}

			add_action( 'woocommerce_init', array( $this, 'after_wc_init' ) );
		}

		public function get_service_schema_defaults( $schema ) {
			$defaults = array();

			if ( ! property_exists( $schema, 'properties' ) ) {
				return $defaults;
			}

			foreach ( get_object_vars( $schema->properties ) as $prop_id => $prop_schema ) {
				if ( property_exists( $prop_schema, 'default' ) ) {
					$defaults[ $prop_id ] = $prop_schema->default;
				}

				if (
				property_exists( $prop_schema, 'type' ) &&
				'object' === $prop_schema->type
				) {
					$defaults[ $prop_id ] = $this->get_service_schema_defaults( $prop_schema );
				}
			}

			return $defaults;
		}

		public function save_defaults_to_shipping_method( $instance_id, $service_id, $zone_id ) {
			$shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );
			$schema          = $shipping_method->get_service_schema();
			$defaults        = (object) $this->get_service_schema_defaults( $schema->service_settings );
			WC_Connect_Options::update_shipping_method_option( 'form_settings', $defaults, $service_id, $instance_id );
		}

		protected function add_method_to_shipping_zone( $zone_id, $method_id ) {
			$method = $this->get_service_schemas_store()->get_service_schema_by_id( $method_id );
			if ( empty( $method ) ) {
				return;
			}

			$zone        = WC_Shipping_Zones::get_zone( $zone_id );
			$instance_id = $zone->add_shipping_method( $method->method_id );
			$zone->save();

			// Dismiss the "add a method to zone" pointer.
			$this->nux->dismiss_pointer( 'wc_services_add_service_to_zone' );
		}

		/**
		 * Bootstrap our plugin and hook into WP/WC core.
		 *
		 * @codeCoverageIgnore
		 */
		public function after_wc_init() {
			$this->schedule_service_schemas_fetch();
			$this->service_settings_store->migrate_legacy_services();
			$this->attach_hooks();
		}

		/**
		 * Load all plugin dependencies.
		 */
		public function load_dependencies() {
			require_once __DIR__ . '/classes/class-wc-connect-wcst-to-wcshipping-migration-state-enum.php';
			require_once __DIR__ . '/classes/class-wc-connect-utils.php';
			require_once __DIR__ . '/classes/class-wc-connect-logger.php';
			require_once __DIR__ . '/classes/class-wc-connect-service-schemas-validator.php';
			require_once __DIR__ . '/classes/class-wc-connect-taxjar-integration.php';
			require_once __DIR__ . '/classes/class-wc-connect-custom-surcharge.php';
			require_once __DIR__ . '/classes/class-wc-connect-error-notice.php';
			require_once __DIR__ . '/classes/class-wc-connect-compatibility.php';
			require_once __DIR__ . '/classes/class-wc-connect-compatibility-wcshipping-packages.php';
			require_once __DIR__ . '/classes/class-wc-connect-shipping-method.php';
			require_once __DIR__ . '/classes/class-wc-connect-service-schemas-store.php';
			require_once __DIR__ . '/classes/class-wc-connect-service-settings-store.php';
			require_once __DIR__ . '/classes/class-wc-connect-payment-methods-store.php';
			require_once __DIR__ . '/classes/class-wc-connect-tracks.php';
			require_once __DIR__ . '/classes/class-wc-connect-help-view.php';
			require_once __DIR__ . '/classes/class-wc-connect-shipping-label.php';
			require_once __DIR__ . '/classes/class-wc-connect-nux.php';
			require_once __DIR__ . '/classes/class-wc-connect-paypal-ec.php';
			require_once __DIR__ . '/classes/class-wc-connect-label-reports.php';
			require_once __DIR__ . '/classes/class-wc-connect-privacy.php';
			require_once __DIR__ . '/classes/class-wc-connect-account-settings.php';
			require_once __DIR__ . '/classes/class-wc-connect-package-settings.php';
			require_once __DIR__ . '/classes/class-wc-connect-continents.php';
			require_once __DIR__ . '/classes/class-wc-connect-order-presenter.php';
			require_once __DIR__ . '/classes/class-wc-connect-cart-validation.php';

			$core_logger     = new WC_Logger();
			$logger          = new WC_Connect_Logger( $core_logger );
			$taxes_logger    = new WC_Connect_Logger( $core_logger, 'taxes' );
			$shipping_logger = new WC_Connect_Logger( $core_logger, 'shipping' );

			WC_Connect_Compatibility_WCShipping_Packages::maybe_enable();

			$validator = new WC_Connect_Service_Schemas_Validator();

			if ( defined( 'WOOCOMMERCE_SERVICES_LOCAL_TEST_MODE' ) ) {
				require_once __DIR__ . '/tests/php/class-wc-connect-api-client-local-test-mock.php';
				$api_client = new WC_Connect_API_Client_Local_Test_Mock( $validator, $this );
			} else {
				require_once __DIR__ . '/classes/class-wc-connect-api-client-live.php';
				$api_client = new WC_Connect_API_Client_Live( $validator, $this );
			}
			$schemas_store         = new WC_Connect_Service_Schemas_Store( $api_client, $logger );
			$settings_store        = new WC_Connect_Service_Settings_Store( $schemas_store, $api_client, $logger );
			$payment_methods_store = new WC_Connect_Payment_Methods_Store( $settings_store, $api_client, $logger );
			$tracks                = new WC_Connect_Tracks( $logger, __FILE__ );
			$shipping_label        = new WC_Connect_Shipping_Label( $api_client, $settings_store, $schemas_store, $payment_methods_store );
			$nux                   = new WC_Connect_Nux( $tracks, $shipping_label );
			$taxjar                = new WC_Connect_TaxJar_Integration( $api_client, $taxes_logger, $this->wc_connect_base_url );
			$paypal_ec             = new WC_Connect_PayPal_EC( $api_client, $nux );
			$label_reports         = new WC_Connect_Label_Reports( $settings_store );

			new WC_Connect_Privacy( $settings_store, $api_client );

			$this->set_logger( $logger );
			$this->set_shipping_logger( $shipping_logger );
			$this->set_api_client( $api_client );
			$this->set_service_schemas_validator( $validator );
			$this->set_service_schemas_store( $schemas_store );
			$this->set_service_settings_store( $settings_store );
			$this->set_payment_methods_store( $payment_methods_store );
			$this->set_tracks( $tracks );
			$this->set_shipping_label( $shipping_label );
			$this->set_nux( $nux );
			$this->set_taxjar( $taxjar );
			$this->set_paypal_ec( $paypal_ec );
			$this->set_label_reports( $label_reports );

			$cart_validation = new WC_Connect_Cart_Validation();
			$cart_validation->register_filters();
			$cart_validation->register_actions();
		}

		/**
		 * Load admin-only plugin dependencies.
		 */
		public function load_admin_dependencies() {
			require_once __DIR__ . '/classes/class-wc-connect-debug-tools.php';
			new WC_Connect_Debug_Tools( $this->api_client );

			$schema   = $this->get_service_schemas_store();
			$settings = $this->get_service_settings_store();
			$logger   = $this->get_logger();
			$this->set_help_view( new WC_Connect_Help_View( $schema, $this->taxjar, $settings, $logger ) );
			add_action( 'admin_notices', array( WC_Connect_Error_Notice::instance(), 'render_notice' ) );
			add_action( 'admin_notices', array( $this, 'render_schema_notices' ) );

			// We only use the settings page for shipping since tax settings are part of
			// the core "WooCommerce > Settings > Tax" tab.
			require_once __DIR__ . '/classes/class-wc-connect-settings-pages.php';
			$settings_pages = new WC_Connect_Settings_Pages( $this->api_client, $this->get_service_schemas_store() );
			$this->set_settings_pages( $settings_pages );

			// Add WC Admin Notices.
			if ( ! self::is_wc_shipping_activated() && self::can_add_wc_admin_notice() ) {
				require_once __DIR__ . '/classes/class-wc-connect-note-dhl-live-rates-available.php';
				WC_Connect_Note_DHL_Live_Rates_Available::init( $schema );
			}
		}

		/**
		 * Hook plugin classes into WP/WC core.
		 */
		public function attach_hooks() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

			add_action( 'admin_enqueue_scripts', array( $this->nux, 'show_pointers' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );

			add_action( 'enqueue_wc_connect_script', array( $this, 'enqueue_wc_connect_script' ), 10, 2 );

			$tracks = $this->get_tracks();
			$tracks->init();

			$this->taxjar->init();
			$this->paypal_ec->init();

			// Only register shipping label-related logic if WC Shipping is not active.
			if ( ! self::is_wc_shipping_activated() && '1' !== WC_Connect_Options::get_option( 'only_tax' ) ) {
				add_action( 'rest_api_init', array( $this, 'wc_api_dev_init' ), 9999 );

				$this->init_shipping_labels();
			}

			/*
			 * Regardless of disabling shipping labels if WC Shipping is active,
			 * keep live rates enabled if the store supports them.
			 */
			$this->init_live_rates();

			if ( is_admin() ) {
				$this->load_admin_dependencies();
			}
		}

		/**
		 * Register shipping labels-related hooks.
		 *
		 * @return void
		 */
		public function init_shipping_labels() {
			add_filter( 'woocommerce_admin_reports', array( $this, 'reports_tabs' ) );

			// Changing the postcode, currency, weight or dimension units affect the returned schema from the server.
			// Make sure to update the service schemas when these options change.
			// TODO: Add other options that change the schema here, or figure out a way to do it automatically.
			add_action( 'update_option_woocommerce_store_postcode', array( $this, 'queue_service_schema_refresh' ) );
			add_action( 'update_option_woocommerce_currency', array( $this, 'queue_service_schema_refresh' ) );
			add_action( 'update_option_woocommerce_weight_unit', array( $this, 'queue_service_schema_refresh' ) );
			add_action( 'update_option_woocommerce_dimension_unit', array( $this, 'queue_service_schema_refresh' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 5, 2 );
			add_action( 'woocommerce_admin_shipping_fields', array( $this, 'add_shipping_phone_to_order_fields' ) );
			add_filter( 'woocommerce_shipping_fields', array( $this, 'add_shipping_phone_to_checkout' ) );
			add_filter( 'woocommerce_get_order_address', array( $this, 'get_shipping_or_billing_phone_from_order' ), 10, 3 );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_tracking_info_to_emails' ), 10, 3 );
			add_action( 'admin_print_footer_scripts', array( $this, 'add_sift_js_tracker' ) );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_wc_connect_package_meta_data' ) );
			add_filter( 'is_protected_meta', array( $this, 'hide_wc_connect_order_meta_data' ), 10, 3 );
			add_action( 'current_screen', array( $this, 'maybe_render_upgrade_banner' ) );
		}

		/**
		 * Register live rates-related hooks.
		 *
		 * @return void
		 */
		public function init_live_rates() {
			$schemas_store = $this->get_service_schemas_store();
			$schemas       = $schemas_store->get_service_schemas();

			if ( $schemas ) {
				add_filter( 'woocommerce_shipping_methods', array( $this, 'woocommerce_shipping_methods' ) );
				add_action( 'woocommerce_load_shipping_methods', array( $this, 'woocommerce_load_shipping_methods' ) );
				add_filter( 'woocommerce_payment_gateways', array( $this, 'woocommerce_payment_gateways' ) );
				add_action( 'wc_connect_service_init', array( $this, 'init_service' ), 10, 2 );
				add_action( 'wc_connect_service_admin_options', array( $this, 'localize_and_enqueue_service_script' ), 10, 2 );
				add_action( 'woocommerce_shipping_zone_method_added', array( $this, 'shipping_zone_method_added' ), 10, 3 );
				add_action( 'wc_connect_shipping_zone_method_added', array( $this, 'save_defaults_to_shipping_method' ), 10, 3 );
				add_action( 'woocommerce_shipping_zone_method_deleted', array( $this, 'shipping_zone_method_deleted' ), 10, 3 );
				add_action( 'woocommerce_shipping_zone_method_status_toggled', array( $this, 'shipping_zone_method_status_toggled' ), 10, 4 );
			}

			add_action( 'wc_connect_fetch_service_schemas', array( $schemas_store, 'fetch_service_schemas_from_connect_server' ) );
			add_filter( 'wc_connect_shipping_service_settings', array( $this, 'shipping_service_settings' ), 10, 3 );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'track_completed_order' ), 10, 3 );
		}

		/**
		 * Queue up a service schema refresh (on shutdown) if there isn't one already.
		 */
		public function queue_service_schema_refresh() {
			$schemas_store = $this->get_service_schemas_store();

			if ( has_action( 'shutdown', array( $schemas_store, 'fetch_service_schemas_from_connect_server' ) ) ) {
				return;
			}

			add_action( 'shutdown', array( $schemas_store, 'fetch_service_schemas_from_connect_server' ) );
		}

		public function tos_rest_init() {
			$settings_store = $this->get_service_settings_store();
			$logger         = $this->get_logger();

			require_once __DIR__ . '/classes/class-wc-rest-connect-base-controller.php';

			require_once __DIR__ . '/classes/class-wc-rest-connect-tos-controller.php';
			$rest_tos_controller = new WC_REST_Connect_Tos_Controller( $this->api_client, $settings_store, $logger );
			$this->set_rest_tos_controller( $rest_tos_controller );
			$rest_tos_controller->register_routes();
		}

		/**
		 * Hook the REST API
		 * Note that we cannot load our controller until this time, because prior to
		 * rest_api_init firing, WP_REST_Controller is not yet defined
		 */
		public function rest_api_init() {
			$schemas_store         = $this->get_service_schemas_store();
			$settings_store        = $this->get_service_settings_store();
			$payment_methods_store = $this->get_payment_methods_store();
			$logger                = $this->get_logger();

			if ( ! class_exists( 'WP_REST_Controller' ) ) {
				$this->logger->debug( 'Error. WP_REST_Controller could not be found', __FUNCTION__ );

				return;
			}

			require_once __DIR__ . '/classes/class-wc-rest-connect-base-controller.php';

			require_once __DIR__ . '/classes/class-wc-rest-connect-account-settings-controller.php';
			$rest_account_settings_controller = new WC_REST_Connect_Account_Settings_Controller( $this->api_client, $settings_store, $logger, $this->payment_methods_store );
			$this->set_rest_account_settings_controller( $rest_account_settings_controller );
			$rest_account_settings_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-services-controller.php';
			$rest_services_controller = new WC_REST_Connect_Services_Controller( $this->api_client, $settings_store, $logger, $schemas_store );
			$this->set_rest_services_controller( $rest_services_controller );
			$rest_services_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-self-help-controller.php';
			$rest_self_help_controller = new WC_REST_Connect_Self_Help_Controller( $this->api_client, $settings_store, $logger );
			$this->set_rest_self_help_controller( $rest_self_help_controller );
			$rest_self_help_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-service-data-refresh-controller.php';
			$rest_service_data_refresh_controller = new WC_REST_Connect_Service_Data_Refresh_Controller( $this->api_client, $settings_store, $logger );
			$rest_service_data_refresh_controller->set_service_schemas_store( $this->get_service_schemas_store() );
			$rest_service_data_refresh_controller->register_routes();

			if ( ! self::is_wc_shipping_activated() ) {

				require_once __DIR__ . '/classes/class-wc-rest-connect-packages-controller.php';
				$rest_packages_controller = new WC_REST_Connect_Packages_Controller( $this->api_client, $settings_store, $logger, $this->service_schemas_store );
				$this->set_rest_packages_controller( $rest_packages_controller );
				$rest_packages_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-controller.php';
				$rest_shipping_label_controller = new WC_REST_Connect_Shipping_Label_Controller( $this->api_client, $settings_store, $logger, $this->shipping_label, $this->payment_methods_store );
				$this->set_rest_shipping_label_controller( $rest_shipping_label_controller );
				$rest_shipping_label_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-status-controller.php';
				$rest_shipping_label_status_controller = new WC_REST_Connect_Shipping_Label_Status_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_shipping_label_status_controller( $rest_shipping_label_status_controller );
				$rest_shipping_label_status_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-refund-controller.php';
				$rest_shipping_label_refund_controller = new WC_REST_Connect_Shipping_Label_Refund_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_shipping_label_refund_controller( $rest_shipping_label_refund_controller );
				$rest_shipping_label_refund_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-preview-controller.php';
				$rest_shipping_label_preview_controller = new WC_REST_Connect_Shipping_Label_Preview_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_shipping_label_preview_controller( $rest_shipping_label_preview_controller );
				$rest_shipping_label_preview_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-print-controller.php';
				$rest_shipping_label_print_controller = new WC_REST_Connect_Shipping_Label_Print_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_shipping_label_print_controller( $rest_shipping_label_print_controller );
				$rest_shipping_label_print_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-rates-controller.php';
				$rest_shipping_rates_controller = new WC_REST_Connect_Shipping_Rates_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_shipping_rates_controller( $rest_shipping_rates_controller );
				$rest_shipping_rates_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-address-normalization-controller.php';
				$rest_address_normalization_controller = new WC_REST_Connect_Address_Normalization_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_address_normalization_controller( $rest_address_normalization_controller );
				$rest_address_normalization_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-assets-controller.php';
				$rest_assets_controller = new WC_REST_Connect_Assets_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_assets_controller( $rest_assets_controller );
				$rest_assets_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-carrier-controller.php';
				$rest_carrier_controller = new WC_REST_Connect_Shipping_Carrier_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_carrier_controller( $rest_carrier_controller );
				$rest_carrier_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-subscription-activate-controller.php';
				$rest_subscription_activate_controller = new WC_REST_Connect_Subscription_activate_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_subscription_activate_controller( $rest_subscription_activate_controller );
				$rest_subscription_activate_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-carrier-delete-controller.php';
				$rest_carrier_delete_controller = new WC_REST_Connect_Shipping_Carrier_Delete_Controller( $this->api_client, $settings_store, $logger );
				$this->set_rest_carrier_delete_controller( $rest_carrier_delete_controller );
				$rest_carrier_delete_controller->register_routes();

				require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-carrier-types-controller.php';
				$rest_carrier_types_controller = new WC_REST_Connect_Shipping_Carrier_Types_Controller( $this->api_client, $settings_store, $logger );
				$this->set_carrier_types_controller( $rest_carrier_types_controller );
				$rest_carrier_types_controller->register_routes();
			}
			require_once __DIR__ . '/classes/class-wc-rest-connect-migration-flag-controller.php';
			$rest_migration_flag_controller = new WC_REST_Connect_Migration_Flag_Controller( $this->api_client, $settings_store, $logger, $this->tracks );
			$this->set_rest_migration_flag_controller( $rest_migration_flag_controller );
			$rest_migration_flag_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-carriers-controller.php';
			$rest_carriers_controller = new WC_REST_Connect_Shipping_Carriers_Controller( $this->api_client, $settings_store, $logger );
			$this->set_rest_carriers_controller( $rest_carriers_controller );
			$rest_carriers_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-subscriptions-controller.php';
			$rest_subscriptions_controller = new WC_REST_Connect_Subscriptions_Controller( $this->api_client, $settings_store, $logger );
			$this->set_rest_subscriptions_controller( $rest_subscriptions_controller );
			$rest_subscriptions_controller->register_routes();

			require_once __DIR__ . '/classes/class-wc-rest-connect-shipping-label-eligibility-controller.php';
			$rest_shipping_label_eligibility_controller = new WC_REST_Connect_Shipping_Label_Eligibility_Controller(
				$this->api_client,
				$settings_store,
				$logger,
				$this->shipping_label,
				$this->payment_methods_store,
				$this->has_only_tax_functionality()
			);

			$rest_shipping_label_eligibility_controller->register_routes();

			/**
			 * We need 4 objects instantiated in `WC_Connect_Loader` to construct WC_REST_Connect_WCShipping_Compatibility_Packages_Controller.
			 *
			 * Since these objects are created here but the call to
			 * WC_Connect_Compatibility_WCShipping_Packages::maybe_enable() happens earlier,
			 * to keep the REST controller instantiation within that class this hook is introduced,
			 * passing the `WC_Connect_Loader` as an argument.
			 *
			 * @see WC_Connect_Compatibility_WCShipping_Packages::register_rest_controller_hooks
			 */
			do_action( 'wcservices_rest_api_init', $this );

			add_filter( 'rest_request_before_callbacks', array( $this, 'log_rest_api_errors' ), 10, 3 );
		}

		/**
		 * If the required v3 REST API endpoints haven't been loaded at this point, load the local copies of said endpoints.
		 * Delete this when the "v3" REST API is included in all the WC versions we support.
		 */
		public function wc_api_dev_init() {
			$rest_server     = rest_get_server();
			$existing_routes = $rest_server->get_routes();
			if ( ! isset( $existing_routes['/wc/v3/data/continents'] ) ) {
				require_once __DIR__ . '/classes/wc-api-dev/class-wc-rest-dev-data-controller.php';
				require_once __DIR__ . '/classes/wc-api-dev/class-wc-rest-dev-data-continents-controller.php';
				$continents = new WC_REST_Dev_Data_Continents_Controller();
				$continents->register_routes();
			}
		}

		/**
		 * Log any WP_Errors encountered before our REST API callbacks
		 *
		 * Note: intended to be hooked into 'rest_request_before_callbacks'
		 *
		 * @param WP_HTTP_Response $response Result to send to the client. Usually a WP_REST_Response.
		 * @param WP_REST_Server   $handler  ResponseHandler instance (usually WP_REST_Server).
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 *
		 * @return mixed - pass through value of $response.
		 */
		public function log_rest_api_errors( $response, $handler, $request ) {
			if ( ! is_wp_error( $response ) ) {
				return $response;
			}

			if ( 0 === strpos( $request->get_route(), '/wc/v1/connect/' ) ) {
				$route_info = $request->get_method() . ' ' . $request->get_route();

				$this->get_logger()->error( $response, $route_info );
				$this->get_logger()->error( $route_info, $request->get_body() );
			}

			return $response;
		}

		/**
		 * Added to the wc_connect_shipping_service_settings filter, returns service settings
		 *
		 * @param $settings
		 * @param $method_id
		 * @param $instance_id
		 *
		 * @return array
		 */
		public function shipping_service_settings( $settings, $method_id, $instance_id ) {
			$settings_store = $this->get_service_settings_store();
			$schemas_store  = $this->get_service_schemas_store();
			$service_schema = $schemas_store->get_service_schema_by_id_or_instance_id( $instance_id ? $instance_id : $method_id );
			if ( ! $service_schema ) {
				return array_merge(
					$settings,
					array(
						'formType'   => 'services',
						'methodId'   => $method_id,
						'instanceId' => $instance_id,
					)
				);
			}

			return array_merge(
				$settings,
				array(
					'storeOptions' => $settings_store->get_store_options(),
					'formSchema'   => $service_schema->service_settings,
					'formLayout'   => $service_schema->form_layout,
					'formData'     => $settings_store->get_service_settings( $method_id, $instance_id ),
					'formType'     => 'services',
					'methodId'     => $method_id,
					'instanceId'   => $instance_id,
				)
			);
		}

		/**
		 * Load shipping method settings.
		 *
		 * This function is added to the wc_connect_service_admin_options action by this class
		 * (see attach_hooks) and then that action is fired by WC_Connect_Shipping_Method::admin_options
		 * to get the service instance form layout and settings bundled inside wcConnectData
		 * as the form container is emitted into the body's HTML
		 */
		public function localize_and_enqueue_service_script( $method_id, $instance_id = false ) {
			if ( ! function_exists( 'get_rest_url' ) ) {
				return;
			}

			do_action(
				'enqueue_wc_connect_script',
				'wc-connect-service-settings',
				array(
					'methodId'   => $method_id,
					'instanceId' => $instance_id,
				)
			);
		}

		/**
		 * Filter function for adding the report tabs
		 *
		 * @param array $reports - report tabs meta.
		 * @return array report tabs with WCS tabs added.
		 */
		public function reports_tabs( $reports ) {
			$reports['wcs_labels'] = array(
				'title'   => __( 'Shipping Labels', 'woocommerce-services' ),
				'reports' => array(
					'connect_labels' => array(
						'title'       => __( 'Shipping Labels', 'woocommerce-services' ),
						'description' => '',
						'hide_title'  => true,
						'callback'    => array( $this->label_reports, 'output_report' ),
					),
				),
			);

			return $reports;
		}

		/**
		 * Send completed order details to Connect Server to check live rates usage.
		 * Attached to the woocommerce_order_details_after_order_table hook
		 *
		 * @param int   $order_id int for completed order ID.
		 * @param array $data Post data for order.
		 * @param array $order WC_Order object containing completed order details.
		 */
		public function track_completed_order( $order_id, $data, $order ) {
			$logger   = $this->get_logger();
			$services = array();
			$packages = WC()->cart->get_shipping_packages();

			foreach ( $packages as $package ) {
				$shipping_methods  = WC()->shipping()->load_shipping_methods( $package );
				$shipping_packages = WC()->shipping()->calculate_shipping_for_package( $package );

				// Only process the valid shipping package.
				if ( empty( $shipping_packages['rates'] ) || ! is_array( $shipping_packages['rates'] ) ) {
					continue;
				}

				$shipping_rates = $shipping_packages['rates'];

				$shipping_method_ids = array();
				foreach ( $shipping_rates as $rate ) {
					if ( ! in_array( $rate->method_id, $shipping_method_ids, true ) ) {
						$shipping_method_ids[] = $rate->method_id;
					}
				}

				foreach ( $shipping_methods as $method ) {
					if ( $method instanceof WC_Connect_Shipping_Method ) {
						$service_settings = $method->get_service_settings();
						$service_schema   = $method->get_service_schema();
						$instance_id      = $method->get_instance_id();
						$service_id       = $service_schema->id;

						if ( is_null( $instance_id ) || ! in_array( "wc_services_$service_id", $shipping_method_ids, true ) ) {
							continue;
						}

						$services[] = array(
							'id'               => $service_id,
							'instance'         => $instance_id,
							'service_settings' => $service_settings,
						);
					}
				}
			}

			if ( empty( $services ) ) {
				return;
			}

			$api_client = $this->get_api_client();
			$response   = $api_client->track_subscription_event( $services );
			if ( is_wp_error( $response ) ) {
				if ( is_a( $logger, 'WC_Connect_Logger' ) ) {
					$logger->error(
						sprintf(
							'Unable to send subscription event: %s',
							$response->get_error_message()
						),
						__FUNCTION__
					);
				}
			}
		}

		/**
		 * Add tracking info (if available) to completed emails using the woocommerce_email_after_order_table hook
		 *
		 * @param bool|\WC_Order|\WC_Order_Refund $order
		 * @param $sent_to_admin
		 * @param $plain_text
		 */
		public function add_tracking_info_to_emails( $order, $sent_to_admin, $plain_text ) {

			// Abort if no $order was passed, if the order is not marked as 'completed' or if another extension is handling the emailing.
			if ( ! $order
				|| ! $order->has_status( 'completed' )
				|| ! WC_Connect_Extension_Compatibility::should_email_tracking_details( $order->get_id() ) ) {
				return;
			}

			$labels = $this->service_settings_store->get_label_order_meta_data( $order->get_id() );

			// Abort if there are no labels.
			if ( empty( $labels ) ) {
				return;
			}

			$markup     = '';
			$link_color = get_option( 'woocommerce_email_text_color' );

			// Generate a table row for each label.
			foreach ( $labels as $label ) {
				$carrier         = $label['carrier_id'];
				$carrier_service = $this->get_service_schemas_store()->get_service_schema_by_id( $carrier );
				$carrier_label   = ( ! $carrier_service || empty( $carrier_service->carrier_name ) ) ? strtoupper( $carrier ) : $carrier_service->carrier_name;
				$tracking        = $label['tracking'];
				$error           = array_key_exists( 'error', $label );
				$refunded        = array_key_exists( 'refund', $label );

				// If the label has an error or is refunded, move to the next label.
				if ( $error || $refunded ) {
					continue;
				}

				if ( $plain_text ) {
					// Should look like '- USPS: 9405536897846173912345' in plain text mode.
					$markup .= '- ' . $carrier_label . ': ' . $tracking . "\n";
					continue;
				}

				$markup .= '<tr>';
				$markup .= '<td class="td" scope="col">' . esc_html( $carrier_label ) . '</td>';

				switch ( $carrier ) {
					case 'fedex':
						$tracking_url = 'https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=' . $tracking;
						break;
					case 'usps':
						$tracking_url = 'https://tools.usps.com/go/TrackConfirmAction.action?tLabels=' . $tracking;
						break;
					case 'ups':
						$tracking_url = 'https://www.ups.com/track?tracknum=' . $tracking;
						break;
					case 'dhlexpress':
						$tracking_url = 'https://www.dhl.com/en/express/tracking.html?AWB=' . $tracking . '&brand=DHL';
						break;
				}

				$markup .= '<td class="td" scope="col">';
				$markup .= '<a href="' . esc_url( $tracking_url ) . '" style="color: ' . esc_attr( $link_color ) . '">' . esc_html( $tracking ) . '</a>';
				$markup .= '</td>';
				$markup .= '</tr>';
			}

			// Abort if all labels are refunded.
			if ( empty( $markup ) ) {
				return;
			}

			if ( $plain_text ) {
				echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
				echo esc_html( mb_strtoupper( __( 'Tracking', 'woocommerce-services' ), 'UTF-8' ) ) . "\n\n";
				echo wp_kses( $markup, array() );
				return;
			}

			?>
				<div style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;">
					<h2><?php esc_html_e( 'Tracking', 'woocommerce-services' ); ?></h2>
					<table class="td" cellspacing="0" cellpadding="6" style="margin-top: 10px; width: 100%;">
						<thead>
							<tr>
								<th class="td" scope="col"><?php esc_html_e( 'Provider', 'woocommerce-services' ); ?></th>
								<th class="td" scope="col"><?php esc_html_e( 'Tracking number', 'woocommerce-services' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php echo wp_kses_post( $markup ); ?>
						</tbody>
					</table>
				</div>
			<?php
		}

		/**
		 * Hook fetching the available services from the connect server
		 */
		public function schedule_service_schemas_fetch() {
			$schemas_store     = $this->get_service_schemas_store();
			$schemas           = $schemas_store->get_service_schemas();
			$last_fetch_result = $schemas_store->get_last_fetch_result_code();

			if ( ! $schemas && '401' !== $last_fetch_result ) { // Don't retry auth failures wait for next scheduled time.
				$schemas_store->fetch_service_schemas_from_connect_server();
			} elseif ( defined( 'WOOCOMMERCE_CONNECT_FREQUENT_FETCH' ) && WOOCOMMERCE_CONNECT_FREQUENT_FETCH ) {
				$schemas_store->fetch_service_schemas_from_connect_server();
			} elseif ( ! wp_next_scheduled( 'wc_connect_fetch_service_schemas' ) ) {
				wp_schedule_event( time(), 'daily', 'wc_connect_fetch_service_schemas' );
			}
		}

		/**
		 * Inject API Client and Logger into WC Connect shipping method instances.
		 *
		 * @param WC_Connect_Shipping_Method $method
		 * @param int|string                 $id_or_instance_id
		 */
		public function init_service( WC_Connect_Shipping_Method $method, $id_or_instance_id ) {

			// TODO - make more generic - allow things other than WC_Connect_Shipping_Method to work here

			$method->set_api_client( $this->get_api_client() );
			$method->set_logger( $this->get_shipping_logger() );
			$method->set_service_settings_store( $this->get_service_settings_store() );

			$service_schema = $this->get_service_schemas_store()->get_service_schema_by_id_or_instance_id( $id_or_instance_id );

			if ( $service_schema ) {
				$method->set_service_schema( $service_schema );
			}
		}

		/**
		 * Returns a reference to a service (e.g. WC_Connect_Shipping_Method) of
		 * a particular id so we can avoid instantiating them multiple times
		 *
		 * @param string $class_name Class name of service to create (e.g. WC_Connect_Shipping_Method)
		 * @param string $service_id Service id of service to create (e.g. usps)
		 * @return mixed
		 */
		protected function get_service_object_by_id( $class_name, $service_id ) {
			if ( ! array_key_exists( $service_id, $this->service_object_cache ) ) {
				$this->service_object_cache[ $service_id ] = new $class_name( $service_id );
			}

			return $this->service_object_cache[ $service_id ];
		}

		/**
		 * Filters in shipping methods for things like WC_Shipping::get_shipping_method_class_names
		 *
		 * @param $shipping_methods
		 * @return mixed
		 */
		public function woocommerce_shipping_methods( $shipping_methods ) {
			$shipping_service_ids = $this->get_service_schemas_store()->get_all_shipping_method_ids();

			foreach ( $shipping_service_ids as $shipping_service_id ) {
				$shipping_methods[ $shipping_service_id ] = $this->get_service_object_by_id( 'WC_Connect_Shipping_Method', $shipping_service_id );
			}

			return $shipping_methods;
		}

		/**
		 * Registers shipping methods for use in things like the Add Shipping Method dialog
		 * on the Shipping Zones view
		 */
		public function woocommerce_load_shipping_methods() {
			$shipping_service_ids = $this->get_service_schemas_store()->get_all_shipping_method_ids();

			foreach ( $shipping_service_ids as $shipping_service_id ) {
				$shipping_method = $this->get_service_object_by_id( 'WC_Connect_Shipping_Method', $shipping_service_id );
				WC_Shipping::instance()->register_shipping_method( $shipping_method );
			}
		}

		public function woocommerce_payment_gateways( $payment_gateways ) {
			return $payment_gateways;
		}

		private function get_i18n_json() {
			$i18n_json = plugin_dir_path( __FILE__ ) . 'i18n/languages/woocommerce-services-' . get_locale() . '.json';
			if ( is_file( $i18n_json ) && is_readable( $i18n_json ) ) {
				$locale_data = @file_get_contents( $i18n_json );
				if ( $locale_data ) {
					return $locale_data;
				}
			}
			// Return empty if we have nothing to return so it doesn't fail when parsed in JS.
			return '{}';
		}

		/**
		 * Return true if we are on the order list page wp-admin/edit.php?post_type=shop_order.
		 *
		 * @return boolean
		 */
		public function should_render_upgrade_banner() {
			if ( ! is_admin() ) {
				return false;
			}

			$screen = get_current_screen();
			if ( ! $screen || ! isset( $screen->id ) ) {
				return false;
			}

			if ( ! function_exists( 'wc_get_page_screen_id' ) ) {
				return false;
			}

			$wc_order_screen_id = wc_get_page_screen_id( 'shop_order' );
			if ( ! $wc_order_screen_id ) {
				return false;
			}

			// Order list page doesn't have the action parameter in the querystring.
			if ( isset( $_GET['action'] ) ) {
				return false;
			}

			// All WC settings pages
			if ( $screen->id === 'woocommerce_page_wc-settings' ) {
				return true;
			}

			/*
			* Non-HPOS:
			*   $screen->id = "edit-shop_order"
			*   $wc_order_screen_id = "shop_order"
			*
			* HPOS:
			*   $screen->id = "woocommerce_page_wc-orders"
			*   $$wc_order_screen_id = "woocommerce_page_wc-orders"
			*/
			if ( $screen->id !== 'edit-shop_order' && $screen->id !== 'woocommerce_page_wc-orders' ) {
				return false;
			}

			return true;
		}

		public function maybe_render_upgrade_banner() {
			if ( ! $this->should_render_upgrade_banner() ) {
				// If this is not on the order list page, then don't add any action.
				return;
			}

			// Add the WCS&T to WCShipping migration notice, creating a button to update.
			$settings_store = $this->get_service_settings_store();
			if ( $settings_store->is_eligible_for_migration() && $this->shipping_label->is_store_eligible_for_shipping_label_creation() ) {
				add_action( 'admin_notices', array( $this, 'display_wcst_to_wcshipping_migration_notice' ) );
				add_action( 'admin_notices', array( $this, 'register_wcshipping_migration_modal' ) );
			}
		}

		/**
		 * Registers the React UI bundle
		 */
		public function admin_enqueue_scripts() {
			$plugin_version = self::get_wcs_version();

			wp_register_script( 'wc_connect_admin', self::get_wcs_admin_script_url(), array( 'lodash', 'moment', 'react', 'react-dom' ), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			// Dev mode will handle loading the css itself to support HMR.
			$stylesheet_url = self::get_wcs_admin_style_url();
			if ( ! empty( $stylesheet_url ) ) {
				// Load CSS async to prevent blocking rendering since this css is non-essential.
				wp_add_inline_script(
					'wc_connect_admin',
					"var link = document.createElement('link');link.rel = 'stylesheet';link.type = 'text/css';link.href = '" . esc_js( $stylesheet_url ) . "';document.getElementsByTagName('HEAD')[0].appendChild(link);"
				);
			}

			wp_register_script( 'wc_services_admin_pointers', $this->wc_connect_base_url . 'woocommerce-services-admin-pointers-' . $plugin_version . '.js', array( 'wp-pointer', 'jquery' ), null );
			wp_register_style( 'wc_connect_banner', $this->wc_connect_base_url . 'woocommerce-services-banner-' . $plugin_version . '.css', array(), null );
			wp_register_script( 'wc_connect_banner', $this->wc_connect_base_url . 'woocommerce-services-banner-' . $plugin_version . '.js', array(), null );

			$i18n_json = $this->get_i18n_json();
			/** @var array $i18nStrings defined in i18n/strings.php */
			wp_localize_script(
				'wc_connect_admin',
				'i18nLocale',
				array(
					'json'       => $i18n_json,
					'localeSlug' => join( '-', explode( '_', get_locale() ) ),
				)
			);
			wp_localize_script(
				'wc_connect_admin',
				'wcsPluginData',
				array(
					'assetPath'       => self::get_wc_connect_base_url(),
					'adminPluginPath' => admin_url( 'plugins.php' ),
				)
			);
		}

		public function get_active_shipping_services() {
			global $wpdb;
			$active_shipping_services = array();
			$shipping_service_ids     = $this->get_service_schemas_store()->get_all_shipping_method_ids();

			foreach ( $shipping_service_ids as $shipping_service_id ) {
				$is_active = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE is_enabled = 1 AND method_id = %s LIMIT 1;",
						$shipping_service_id
					)
				);

				if ( $is_active ) {
					$active_shipping_services[] = $shipping_service_id;
				}
			}

			return $active_shipping_services;
		}

		public function get_active_services() {
			return $this->get_active_shipping_services();
		}

		public function is_wc_connect_shipping_service( $service_id ) {
			$shipping_service_ids = $this->get_service_schemas_store()->get_all_shipping_method_ids();
			return in_array( $service_id, $shipping_service_ids );
		}

		public function shipping_zone_method_added( $instance_id, $service_id, $zone_id ) {
			if ( $this->is_wc_connect_shipping_service( $service_id ) ) {
				do_action( 'wc_connect_shipping_zone_method_added', $instance_id, $service_id, $zone_id );
			}
		}

		public function shipping_zone_method_deleted( $instance_id, $service_id, $zone_id ) {
			if ( $this->is_wc_connect_shipping_service( $service_id ) ) {
				WC_Connect_Options::delete_shipping_method_options( $service_id, $instance_id );
				do_action( 'wc_connect_shipping_zone_method_deleted', $instance_id, $service_id, $zone_id );
			}
		}

		public function shipping_zone_method_status_toggled( $instance_id, $service_id, $zone_id, $enabled ) {
			if ( $this->is_wc_connect_shipping_service( $service_id ) ) {
				do_action( 'wc_connect_shipping_zone_method_status_toggled', $instance_id, $service_id, $zone_id, $enabled );
			}
		}

		public function should_show_shipping_debug_meta_box( $post ) {
			$order = WC_Connect_Compatibility::instance()->init_theorder_object( $post );

			if ( false === $order ) {
				return false;
			}

			$shipping_methods = $order->get_shipping_methods();

			foreach ( $shipping_methods as $method ) {
				if ( ! empty( $method['wc_connect_packing_log'] ) ) {
					return true;
				}
			}

			return false;
		}

		public function add_meta_boxes( $screen_id, $post ) {

			if ( $this->shipping_label->should_show_meta_box( $post ) ) {
				add_meta_box( 'woocommerce-order-shipment-tracking', __( 'Shipment Tracking', 'woocommerce-services' ), array( $this->shipping_label, 'meta_box' ), null, 'side', 'default', array( 'context' => 'shipment_tracking' ) );

				add_meta_box( 'woocommerce-order-label', __( 'Shipping Label', 'woocommerce-services' ), array( $this->shipping_label, 'meta_box' ), null, 'normal', 'high', array( 'context' => 'shipping_label' ) );
			}

			if ( $this->should_show_shipping_debug_meta_box( $post ) ) {
				$screen = WC_Connect_Compatibility::instance()->get_order_admin_screen();
				add_meta_box( 'woocommerce-services-shipping-debug', __( 'Shipping Debug', 'woocommerce-services' ), array( $this, 'shipping_rate_packaging_debug_log_meta_box' ), $screen, 'normal', 'default' );
			}
		}

		public function shipping_rate_packaging_debug_log_meta_box( $post ) {
			$order            = wc_get_order( $post );
			$shipping_methods = $order->get_shipping_methods();

			foreach ( $shipping_methods as $method ) {
				if ( empty( $method['wc_connect_packing_log'] ) ) {
					continue;
				}

				$method_instance = new WC_Connect_Shipping_Method( $method->get_instance_id() );
				?>
				<p>
					<strong><?php esc_html_e( 'Shipping Method Name:', 'woocommerce-services' ); ?></strong>
					<?php echo esc_html( $method_instance->get_method_title() ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Shipping Method ID:', 'woocommerce-services' ); ?> </strong>
					<?php echo esc_html( $method->get_method_id() . ':' . $method->get_instance_id() ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Chosen Rate:', 'woocommerce-services' ); ?> </strong>
					<?php
					echo esc_html(
						printf(
							'%s (%s%s)',
							$method->get_name(),
							get_woocommerce_currency_symbol(),
							$method->get_total()
						)
					);
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Packing Log:', 'woocommerce-services' ); ?> </strong>
				</p>
				<pre class="packing-log"><?php echo esc_html( implode( "\n", $method['wc_connect_packing_log'] ) ); ?></pre>
				<?php
			}
		}

		public function hide_wc_connect_package_meta_data( $hidden_keys ) {
			$hidden_keys[] = 'wc_connect_packages';
			$hidden_keys[] = 'wc_connect_packing_log';
			return $hidden_keys;
		}

		public function hide_wc_connect_order_meta_data( $protected, $meta_key, $meta_type ) {
			if ( in_array( $meta_key, array( 'wc_connect_labels', 'wc_connect_destination_normalized' ), true ) ) {
				$protected = true;
			}

			return $protected;
		}

		public function add_shipping_phone_to_checkout( $fields ) {
			$defaults = array(
				'label'        => __( 'Phone', 'woocommerce-services' ),
				'type'         => 'tel',
				'required'     => false,
				'class'        => array( 'form-row-wide' ),
				'clear'        => true,
				'validate'     => array( 'phone' ),
				'autocomplete' => 'tel',
			);

			// Use existing settings if the field exists.
			$field = isset( $fields['shipping_phone'] )
				? array_merge( $defaults, $fields['shipping_phone'] )
				: $defaults;

			// Enforce phone type, autocomplete, and validation.
			$field['type']         = 'tel';
			$field['autocomplete'] = 'tel';
			if ( ! in_array( 'tel', $field['validate'], true ) ) {
				$field['validate'][] = 'tel';
			}

			// Add to the list.
			$fields['shipping_phone'] = $field;
			return $fields;
		}

		public function add_shipping_phone_to_order_fields( $fields ) {
			$fields['phone'] = array(
				'label' => __( 'Phone', 'woocommerce-services' ),
			);
			return $fields;
		}

		public function get_shipping_or_billing_phone_from_order( $fields, $address_type, WC_Order $order ) {
			if ( 'shipping' !== $address_type ) {
				return $fields;
			}

			$fields['phone'] = $order->get_shipping_phone() ? $order->get_shipping_phone() : $order->get_billing_phone();

			return $fields;
		}

		public function add_plugin_action_links( $links ) {
			$links[] = sprintf(
				wp_kses(
					/* translators: %s Support url */
					__( '<a href="%s">Support</a>', 'woocommerce-services' ),
					array( 'a' => array( 'href' => array() ) )
				),
				esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' )
			);
			return $links;
		}

		/**
		 * Performs a direct database check to see if any order has shipping labels.
		 * Static method to be callable from early hooks like 'all_plugins'.
		 *
		 * @return bool True if any labels exist, false otherwise.
		 */
		private static function _has_any_labels_db_check() {
			global $wpdb;

			// If $wpdb is not available yet, assume no labels (safer default).
			if ( ! is_object( $wpdb ) ) {
				return false;
			}

			$meta_table_to_check = OrderUtil::get_table_for_order_meta();

			return (bool) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT EXISTS (
						SELECT 1
						FROM %i
						WHERE meta_key = %s
						LIMIT 1
					)',
					$meta_table_to_check,
					'wc_connect_labels'
				)
			);
		}

		/**
		 * Determines if the store is configured for tax-only functionality.
		 *
		 * This method checks two conditions:
		 * 1. If Jetpack is connected and the 'only_tax' option is set to '1', it returns true.
		 * 2. If Jetpack is not connected and there are no legacy shipping labels in the database, it returns true.
		 *
		 * @return bool True if the store is configured for tax-only functionality, false otherwise.
		 */
		private function has_only_tax_functionality() {
			return ( WC_Connect_Jetpack::is_connected() && '1' === WC_Connect_Options::get_option( 'only_tax' ) ) ||
			( ! WC_Connect_Jetpack::is_connected() && ! self::_has_any_labels_db_check() );
		}

		public function maybe_rename_plugin( $plugins ) {
			$plugin_basename = 'woocommerce-services/woocommerce-services.php';

			if ( isset( $plugins[ $plugin_basename ] ) ) {
				if ( $this->has_only_tax_functionality() ) {
					$plugins[ $plugin_basename ]['Name']        = $this->get_plugin_name_for_new_sites();
					$plugins[ $plugin_basename ]['Description'] = $this->get_plugin_description_for_new_sites();
				} else {
					$plugins[ $plugin_basename ]['Name']        = $this->get_plugin_name_for_legacy_sites();
					$plugins[ $plugin_basename ]['Description'] = $this->get_plugin_description_for_legacy_sites();
				}
			}
			return $plugins;
		}

		private function get_plugin_name_for_legacy_sites() {
			return __( 'WooCommerce Tax (previously WooCommerce Shipping & Tax)', 'woocommerce-services' );
		}

		private function get_plugin_description_for_legacy_sites() {
			return __( 'Hosted services for WooCommerce: automated tax calculation, shipping label printing, and smoother payment setup.', 'woocommerce-services' );
		}

		private function get_plugin_name_for_new_sites() {
			return __( 'WooCommerce Tax', 'woocommerce-services' );
		}

		private function get_plugin_description_for_new_sites() {
			return __( 'Automated tax calculation for WooCommerce.', 'woocommerce-services' );
		}

		/**
		 * Adds the Sift JS page tracker if needed. See the comments for the detailed logic.
		 *
		 * @return  void
		 */
		public function add_sift_js_tracker() {
			$sift_configurations = $this->api_client->get_sift_configuration();

			$connected_data = WC_Connect_Jetpack::get_connection_owner_wpcom_data();

			if ( is_wp_error( $sift_configurations ) || empty( $sift_configurations->beacon_key ) || empty( $connected_data['ID'] ) ) {
				// Don't add sift tracking if we can't have the parameters to initialize Sift
				return;
			}

			$fraud_config = array(
				'beacon_key' => $sift_configurations->beacon_key,
				'user_id'    => $connected_data['ID'],
			);

			?>
			<script type="text/javascript">
				var src = 'https://cdn.sift.com/s.js';

				var _sift = ( window._sift = window._sift || [] );
				_sift.push( [ '_setAccount', '<?php echo esc_attr( $fraud_config['beacon_key'] ); ?>' ] );
				_sift.push( [ '_setUserId', '<?php echo esc_attr( $fraud_config['user_id'] ); ?>' ] );
				_sift.push( [ '_trackPageview' ] );

				if ( ! document.querySelector( '[src="' + src + '"]' ) ) {
					var script = document.createElement( 'script' );
					script.src = src;
					script.async = true;
					document.body.appendChild( script );
				}
			</script>
			<?php
		}

		public function enqueue_wc_connect_script( $root_view, $extra_args = array() ) {
			$is_alive = $this->api_client->is_alive_cached();

			$account_settings  = new WC_Connect_Account_Settings(
				$this->service_settings_store,
				$this->payment_methods_store
			);
			$packages_settings = new WC_Connect_Package_Settings(
				$this->service_settings_store,
				$this->service_schemas_store
			);
			$payload           = array(
				'nonce'                 => wp_create_nonce( 'wp_rest' ),
				'baseURL'               => get_rest_url(),
				'wcs_server_connection' => $is_alive,
				'accountSettings'       => $account_settings->get(),
				'packagesSettings'      => $packages_settings->get(),
				'is_wcshipping_active'  => self::is_wc_shipping_activated(),
			);

			wp_localize_script( 'wc_connect_admin', 'wcConnectData', $payload );
			wp_enqueue_script( 'wc_connect_admin' );

			$debug_page_uri = add_query_arg(
				array(
					'page' => 'wc-status',
					'tab'  => 'connect',
				),
				admin_url( 'admin.php' )
			);

			$encoded_arguments = wp_json_encode( $extra_args );
			?>
				<div class="wcc-root woocommerce <?php echo esc_attr( $root_view ); ?>" data-args="<?php echo wc_esc_json( $encoded_arguments ); ?>">
					<span class="form-troubles" style="opacity: 0">
						<?php printf( esc_html__( 'Section not loading? Visit the <a href="%s">status page</a> for troubleshooting steps.', 'woocommerce-services' ), esc_url( $debug_page_uri ) ); ?>
					</span>
				</div>
			<?php
		}

		public function render_schema_notices() {
			$schemas = $this->get_service_schemas_store()->get_service_schemas();
			if ( empty( $schemas ) || ! property_exists( $schemas, 'notices' ) || empty( $schemas->notices ) ) {
				return;
			}
			$allowed_html = array(
				'a'      => array( 'href' => array() ),
				'strong' => array(),
				'br'     => array(),
			);
			foreach ( $schemas->notices as $notice ) {
				$dismissible = false;
				// check if the notice is dismissible.
				if ( property_exists( $notice, 'id' ) && ! empty( $notice->id ) && property_exists( $notice, 'dismissible' ) && $notice->dismissible ) {
					// check if the notice is being dismissed right now.
					if ( isset( $_GET['wc-connect-dismiss-server-notice'] ) && $_GET['wc-connect-dismiss-server-notice'] === $notice->id ) {
						set_transient( 'wcc_notice_dismissed_' . $notice->id, true, MONTH_IN_SECONDS );
						continue;
					}
					// check if the notice has already been dismissed.
					if ( false !== get_transient( 'wcc_notice_dismissed_' . $notice->id ) ) {
						continue;
					}

					$dismissible  = true;
					$link_dismiss = add_query_arg( array( 'wc-connect-dismiss-server-notice' => $notice->id ) );
				}
				?>
				<div class='<?php echo esc_attr( 'notice notice-' . $notice->type ); ?>' style="position: relative;">
					<?php if ( $dismissible ) : ?>
					<a href="<?php echo esc_url( $link_dismiss ); ?>" style="text-decoration: none;" class="notice-dismiss" title="<?php esc_attr_e( 'Dismiss this notice', 'woocommerce-services' ); ?>"></a>
					<?php endif; ?>
					<p><?php echo wp_kses( $notice->message, $allowed_html ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Check if WooCommerce Shipping has been activated.
		 *
		 * @return bool
		 */
		public static function is_wc_shipping_activated() {
			return in_array( 'woocommerce-shipping/woocommerce-shipping.php', get_option( 'active_plugins' ) );
		}

		/**
		 * Returns if both Woo Shipping and Woo Tax are active.
		 *
		 * @return bool
		 */
		public function are_woo_shipping_and_woo_tax_active() {
			$is_woo_tax_active = in_array( 'woocommerce-tax/woocommerce-tax.php', get_option( 'active_plugins' ) );

			return self::is_wc_shipping_activated() && $is_woo_tax_active;
		}

		/**
		 * Echoes an admin notice informing of Woo Shipping and Woo Tax being active.
		 *
		 * To be used in the `admin_notices` hook.
		 *
		 * @return void
		 */
		public function display_woo_shipping_and_woo_tax_are_active_notice() {
			echo '<div class="error"><p><strong>' . esc_html__( 'WC Shipping and WC Tax plugins are already active. Please deactivate WooCommerce Tax.', 'woocommerce-services' ) . '</strong></p></div>';
		}

		/**
		 * Returns the notice for migrating from WCST to WC Shipping.
		 *
		 * @return bool
		 */
		public function display_wcst_to_wcshipping_migration_notice() {
			if ( isset( $_COOKIE[ self::MIGRATION_DISMISSAL_COOKIE_KEY ] ) && (int) $_COOKIE[ self::MIGRATION_DISMISSAL_COOKIE_KEY ] === 1 ) {
				return;
			}
			$schema = $this->get_service_schemas_store();
			$banner = $schema->get_wcship_wctax_upgrade_banner();
			if ( empty( $banner ) ) {
				return;
			}

			$account_settings  = new WC_Connect_Account_Settings(
				$this->service_settings_store,
				$this->payment_methods_store
			);
			$packages_settings = new WC_Connect_Package_Settings(
				$this->service_settings_store,
				$this->service_schemas_store
			);
			$encoded_arguments = wp_json_encode(
				array(
					'nonce'            => wp_create_nonce( 'wp_rest' ),
					'baseURL'          => get_rest_url(),
					'accountSettings'  => $account_settings->get(),
					'packagesSettings' => $packages_settings->get(),
				)
			);

			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-%s wcst-wcshipping-migration-notice">
					<div class="wcst-wcshipping-migration-notice__content">
					<div id="wcst_wcshipping_migration_admin_notice_feature_announcement" data-args="%s"></div>
					<p>',
					$banner->type,
					wc_esc_json( $encoded_arguments )
				) .
				sprintf(
					/* translators: %s: documentation URL */
					__( $banner->message, 'woocommerce-services' ),
					'https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-shipping/#how-do-i-migrate-from-wcst'
				) .
				sprintf(
					'</p>
					<button type="button" class="notice-dismiss %s"><span class="screen-reader-text">Dismiss this notice.</span></button>
					</div>
					<div class="notice-action"><button id="wcst-wcshipping-migration-notice__click" type="button" class="action-button">%s</button></div>
					</div>',
					$banner->dismissible ? '' : 'hide-dismissible',
					$banner->action
				)
			);
		}

		public function register_wcshipping_migration_modal() {
			$plugin_version = self::get_wcs_version();
			wp_register_style( 'wcst_wcshipping_migration_admin_notice', $this->wc_connect_base_url . 'woocommerce-services-wcshipping-migration-admin-notice-' . $plugin_version . '.css', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_register_script( 'wcst_wcshipping_migration_admin_notice', $this->wc_connect_base_url . 'woocommerce-services-wcshipping-migration-admin-notice-' . $plugin_version . '.js', array(), null );
			wp_localize_script(
				'wcst_wcshipping_migration_admin_notice',
				'wcsPluginData',
				array(
					'assetPath'       => self::get_wc_connect_base_url(),
					'adminPluginPath' => admin_url( 'plugins.php' ),
				)
			);
			wp_enqueue_script( 'wc_connect_admin' );
			wp_enqueue_script( 'wcst_wcshipping_migration_admin_notice' );
			wp_enqueue_style( 'wcst_wcshipping_migration_admin_notice' );
		}
	}
}

register_deactivation_hook( __FILE__, array( 'WC_Connect_Loader', 'plugin_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WC_Connect_Loader', 'plugin_uninstall' ) );

// Jetpack's Rest_Authentication needs to be initialized even before plugins_loaded.
Automattic\Jetpack\Connection\Rest_Authentication::init();

if ( ! defined( 'WC_UNIT_TESTING' ) ) {
	new WC_Connect_Loader();
}
