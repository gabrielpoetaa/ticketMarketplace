<?php
/**
 * Plugin Name:     Asaas Gateway for WooCommerce
 * Plugin URI:      https://www.asaas.com
 * Description:     Take transparent credit card and bank ticket payment checkouts on your store using Asaas.
 * Author:          Asaas
 * Author URI:      https://www.asaas.com
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     woo-asaas
 * Domain Path:     /languages
 * Version:         2.7.1
 *
 * @package         WooAsaas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'autoload.php';

add_action( 'plugins_loaded', array( \WC_Asaas\WC_Asaas::class, 'get_instance' ) );
