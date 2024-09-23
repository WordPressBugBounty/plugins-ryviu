<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.ryviu.com
 * @since             2.0.0
 * @package           Ryviu
 *
 * @wordpress-plugin
 * Plugin Name:       Ryviu - Product Reviews for WooCommerce
 * Plugin URI:        https://www.ryviu.com
 * Description:       Display reviews in product page for woocommerce, manager data and get from https://app.ryviu.io
 * Version:           3.1.25
 * Requires at least: 4.0
 * Tested up to:      6.6
 * WC requires at least: 3.0
 * WC tested up to:   9.1
 * Author:            Ryviu
 * Author URI:        https://www.ryviu.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ryviu
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
$urlparts = wp_parse_url( site_url() );
$domain   = $urlparts['host'];

define('RYVIU_SHOP_DOMAIN', $domain);
define('RYVIU_WOO_VERSION', '3.1.25');
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
define('RYVIU_DIR_PATH', plugin_dir_path(__FILE__) );
define('RYVIU_URL_ASSETS', plugins_url( 'assets/', __FILE__ ) );
define('RYVIU_APP_HOOK_URL', 'https://app.ryviu.io/webhook/woocommerce/');
define('RYVIU_NAMESPACE', 'ryviu/v1');

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

require_once RYVIU_DIR_PATH . 'includes/main-class.php';

/**
 * The code that runs during plugin activation.
 */
function activate_ryviu() {
	RyviuMain::activate();
}

/**
 * The code that runs during plugin uninstall.
 */
function uninstall_ryviu(){
	RyviuMain::uninstall();
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'code_add_plugin_page_settings_link');
// Add Menu Ryviu to Dashboard WordPress
function code_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=ryviu-setting-admin' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}

// Show notice when do not connect store with Ryviu
function r_admin_notices() {
	
	$settings = get_option( 'ryviu_client_settings' );
	
	$image_url = RYVIU_URL_ASSETS.'images/logo-ryviu-v8.svg';

	if(!$settings || $settings == new \stdClass()){
		echo '<div class="notice notice-error is-dismissible r-cl-connect"><div class="r--notices"><img src="'.$image_url.'" alt="" /><p><b>Ryviu:</b> It looks like there is a problem connecting your store to Ryviu. Please <a class="ryviu-check-connect" href="javascript:void(0)">click here</a> to check the issue, or please <a target="_blank" href="https://www.ryviu.com/contact-us">contact us</a> for help.</p></div></div>';
		echo '<div class="notice notice-success is-dismissible r-cl-connect"><p class="r--notices"><img src="'.$image_url.'" alt="" /><b>Ryviu:</b> Successful connection.</p></div>';
	}else{
		$ryviu_settings = get_option( 'ryviu_settings_reviews' );
		$ryviu_version = get_option( 'ryviu_version' );
		if (array_key_exists('ryviu_frontend_version', $ryviu_settings)) {
			$ryviu_frontend_version = $ryviu_settings['ryviu_frontend_version'];
			
			if(!$ryviu_version){
				$ryviu_version = $ryviu_frontend_version;
				RyviuMain::ryviu_update_frontend($ryviu_frontend_version, false);
			}else{
				if($ryviu_frontend_version != $ryviu_version){
					$ryviu_version = $ryviu_frontend_version;
					RyviuMain::ryviu_update_frontend($ryviu_frontend_version, false);
				}
			}
		}

		if(!$ryviu_version || ($ryviu_version && $ryviu_version == 1)){
			echo '<div class="notice notice-info is-dismissible r-cl-update"><div class="r--notices"><img src="'.$image_url.'" alt="" /><p><b>Ryviu:</b>&nbsp; Enhancing your store\'s frontend with the new design for the reviews widget, featuring a user-friendly layout and more customizable options. <a class="ryviu-view-demo" target="_blank" href="https://version2.omazing.us/">Click here</a> to see the demo or <a class="ryviu-update-frontend" href="javascript:void(0)">update now</a>.</p></div></div>';
			echo '<div class="notice notice-success is-dismissible rpl--hide r-cl-update"><p class="r--notices"><img src="'.$image_url.'" alt="" /><b>Ryviu:</b>&nbsp; Successful update.</p></div>';
		}
	}
}


register_activation_hook( __FILE__, 'activate_ryviu' );
register_uninstall_hook( __FILE__, 'uninstall_ryviu' );
add_action( 'admin_notices','r_admin_notices');
RyviuMain::re_order_plugin();

// Include main ryviu class
require_once RYVIU_DIR_PATH . 'includes/class-ryviu.php';

// Main Class Called
function RYVIU() {
	return RyviuWoo::instance();
}

$GLOBALS['RYVIU'] = RYVIU();
