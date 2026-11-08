<?php
/**
 * Plugin Name:       Karnisha Power By
 * Plugin URI:        https://github.com/karnishaco/karnisha-powerby
 * Description:       Shows the Karnisha "designed & developed by" badge in site footers via an Elementor widget or the [karnisha_powerby] shortcode. All assets are served locally.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Karnisha
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       karnisha-powerby
 * Domain Path:       /languages
 * Elementor tested up to: 3.32
 *
 * @package Karnisha_PowerBy
 */

defined( 'ABSPATH' ) || exit;

define( 'KPB_VERSION', '1.0.0' );
define( 'KPB_FILE', __FILE__ );
define( 'KPB_PATH', plugin_dir_path( __FILE__ ) );
define( 'KPB_URL', plugin_dir_url( __FILE__ ) );

require_once KPB_PATH . 'includes/class-kpb-plugin.php';

KPB_Plugin::instance();
