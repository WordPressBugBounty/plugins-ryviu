<?php
/**
 * Ryviu Webhook class
 *
 * @author   ryviu.com
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ryviu_Webhook {

	public function __construct() {
		$this->init();
		include_once RYVIU_DIR_PATH . 'includes/common-functions.php';
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_wp_api_endpoints' ) );
	}

    public function register_wp_api_endpoints() {
        if (!defined('RYVIU_NAMESPACE')) {
            define('RYVIU_NAMESPACE', 'ryviu/v1');
        }
        $namespace = constant('RYVIU_NAMESPACE');

        register_rest_route($namespace, '/update-settings', array(
            'methods'  => 'POST',
            'callback' => array($this, 'webhook_update_settings_callback'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/update-featured', array(
            'methods'  => 'POST',
            'callback' => array($this, 'webhook_update_featured_callback'),
            'permission_callback' => '__return_true',
        ));

    }

    public function webhook_update_featured_callback(WP_REST_Request $request) {
        $params = $request->get_params();
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Updated successfully'
        ), 200);

        if(isset($params['featured_static'])){
            $featured_static = isset($params['featured_static']) ? $params['featured_static'] : '';
            CommonFunctions::updateOption('featured_ryviu_data', $featured_static);
            CommonFunctions::clearStoreCache();
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Updated successfully'
        ), 200);

    }

    public function webhook_update_settings_callback(WP_REST_Request $request) {
        $params = $request->get_params();
        
        if(isset($params['ryviuvtest'])){
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Ryviu Update Settings'
            ), 200);
        }
        
        if(isset($params['settings'])){
            $settings_param = isset($params['settings']) ? $params['settings'] : null;
            $settings = base64_decode($settings_param);
            CommonFunctions::updateOption('ryviu_client_settings', json_decode($settings));
            CommonFunctions::clearStoreCache();
        }

        if(isset($params['api_id'])){
            $key_id = $params['api_id'];
            CommonFunctions::updateOption('ryviu_user_api_key_id', $key_id);
            CommonFunctions::clearStoreCache();
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Settings updated successfully'
        ), 200);

    }
    
}