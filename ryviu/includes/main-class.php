<?php
/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled
 */
class RyviuMain {

    public static function check_connect_ryviu($check = false) {
        $mes = 'Connection failed, please contact Ryviu support for help.';
        $mes_eor = 'Success';
        delete_option( 'ryviu_client_settings' );
        $settings = get_option( 'ryviu_client_settings' );
        if(!$settings || $settings == new \stdClass()){
            $url = 'https://app.ryviu.io/client/sts/'.base64_encode(constant( 'RYVIU_SHOP_DOMAIN' ));
            try{
                $settings_info = wp_remote_get( $url, ['timeout' => 3] );
                $mes_eor = $settings_info;
                if ( is_wp_error( $settings_info ) ) {
                    $settings = '';
                    $mes = 'Request failed: ' . $settings_info->get_error_message();
                } else {
	                $response_code = wp_remote_retrieve_response_code($settings_info);
                    if ($response_code == 502) {
			            $mes = 'Bad Gateway: The server received an invalid response. Please try again later or contact Ryviu support.';
			        } else if (isset($settings_info['body'])) {
                        $settings_body = json_decode($settings_info['body']);
                        
                        if ($settings_body->status != 'error') {
                            $updated_settings = json_decode($settings_body->settings);
                            if ($updated_settings) {
                                // Update the Ryviu client settings
                                update_option('ryviu_client_settings', $updated_settings);
                               
                                // Update the frontend version if it exists
                                if (!empty($settings_body->ryviu_frontend_version)) {
                                    update_option('ryviu_version', $settings_body->ryviu_frontend_version);

                                    $options = get_option('ryviu_settings_reviews');
                                    $options['ryviu_frontend_version'] = $settings_body->ryviu_frontend_version;
                                    update_option('ryviu_settings_reviews', $options);
                                }

                                // Check if we need to send a success response
                                if (isset($check) && $check === true) {
                                    wp_send_json([
                                        'status' => 'success',
                                        'mes' => 'Your store has been successfully reconnected.'
                                    ]);
                                }
                            } else {
                                // Handle case where settings could not be decoded
                                $mes = 'Failed to update settings. Please try again.';
                            }
                        } else {
                            // Error message if the status is 'error' or if settings_body is null
                            $mes = 'Your store has not been linked to Ryviu, or the connected WordPress address is incorrect. Please login to your Ryviu account and check the settings page.';
                        }
                    }
                }
            }catch(Exception $e){
                $mes_eor = $e->getMessage();
				$mes = 'An error occurred: ' . $mes_eor;
            }
        }
        if(isset($check) && $check == true){
            wp_send_json([
                'status' => 'error',
                'mes_eor' => $mes_eor,
                'mes' => $mes
            ]);
        }
	}

    public static function update_product_slug($slug) {
        $update_link = 'https://app.ryviu.io/update-client-settings';
        wp_remote_post($update_link , array( 'body' => array('domain' => site_url(), 'product_slug' => $slug)));
        $settings = get_option( 'ryviu_client_settings' );
        $settings->design_settings->product_slug = 'products';
        if($settings && $settings != new \stdClass()){
            $settings->design_settings->product_slug = $slug;
        }
        update_option( 'ryviu_client_settings', $settings );
    }

    public static function ryviu_update_frontend($ryviu_version = 2, $res = true) {
        // Update the Ryviu version option
        update_option('ryviu_version', $ryviu_version);
    
        // Retrieve and update the Ryviu settings
        $ryviu_settings_reviews = get_option('ryviu_settings_reviews');
        if (is_array($ryviu_settings_reviews)) {
            $ryviu_settings_reviews['ryviu_frontend_version'] = $ryviu_version;
            update_option('ryviu_settings_reviews', $ryviu_settings_reviews);
        }
    
        // Prepare and send the POST request to update client settings
        $update_link = 'https://app.ryviu.io/update-client-settings';
        $response = wp_remote_post($update_link, [
            'body' => [
                'domain' => site_url(),
                'ryviu_version' => $ryviu_version
            ]
        ]);
        if($res){
            // Check if the POST request was successful
            if (is_wp_error($response)) {
                wp_send_json_error([
                    'status' => 'error',
                    'mes' => 'There was an issue updating the frontend. Please try again.'
                ]);
            } else {
                wp_send_json_success([
                    'status' => 'success',
                    'mes' => 'The frontend has been updated with the new design. Please navigate to your store to check.'
                ]);
            }
        }
        
    }

    public static function check_product_slug() {
        $settings = get_option( 'ryviu_client_settings' );
        if(isset($settings) && $settings != ''){
            if(isset($settings->design_settings)){
                $wc_options = get_option('woocommerce_permalinks');
                $product_base = $wc_options['product_base'];
                if($product_base[0] == '/'){
                    $product_base = substr($product_base, 1, strlen($product_base)-1);
                }
                if(strpos($product_base, '%') > -1){
                    $product_base = 'products';
                }
                if (isset($settings->design_settings->product_slug) && $settings->design_settings->product_slug != $product_base) {
                    self::update_product_slug($product_base);
                } elseif (!isset($settings->design_settings->product_slug)) {
                    self::update_product_slug($product_base);
                }
            }
        }
    }

    // Active Function
	public static function activate() {
		$options = get_option( 'ryviu_settings_reviews' );

        $default_opt = array(
            'ryviu_frontend_version' => 1,
            'position_display' => 1,
            'priority_position_display' => 6,
            'position_display_widget' => 2,
            'priority_position_display_widget' => 6,
            'position_display_widget_in_loop' => 6,
            'priority_position_display_widget_in_loop' => 50,
            'active_reviews_tab' => 0,
            'wordpress_theme' => 'default',
            'question_and_answer' => 0
        );

        if(isset($options) && is_array($options)){
            update_option('ryviu_settings_reviews', $options);
        }else{
            add_option('ryviu_settings_reviews', $default_opt);
        }
        // Check connect with Ryviu
        self::check_connect_ryviu();
        self::check_product_slug();

        add_rewrite_rule('^ryviu-json/([^/]*)/?$','index.php?ryviu=port&type=ryviu-action&endpoint=$matches[1]','top');
        add_rewrite_rule('^products/([^/]*).json/?$','index.php?ryviu=json&type=product_detail&handle=$matches[1]','top');
        flush_rewrite_rules();
	}

    // Uninstall Function
    public static function uninstall(){
        delete_option( 'ryviu_client_settings' );
        delete_option( 'ryviu_settings_reviews' );
        wp_remote_post( RYVIU_APP_HOOK_URL.'uninstall-woo', array( 'body' => array('domain' => site_url())));
    }

    // Sort Ryviu run after Woo
    public static function re_order_plugin(){
        $active_plugins = get_option( 'active_plugins' );
        $ryviuStatus = false;
        $rorder_plugin = true;
        $ryviu_index = 0;
        $wc_index = 0;
        
        foreach($active_plugins as $key => $plugin){
            if($plugin == 'screets-lcx/screets-lcx.php'){
                $rorder_plugin = false;
            }else{
                if($plugin == 'ryviu/ryviu.php'){
                    $ryviu_index = $key;
                    $ryviuStatus = true;
                }
                if($plugin == 'woocommerce/woocommerce.php'){
                    $wc_index = $key;
                } 
            }
        }
        if($rorder_plugin && $ryviuStatus && $wc_index > $ryviu_index){
            $active_plugins[$ryviu_index] = 'woocommerce/woocommerce.php';
            $active_plugins[$wc_index] = 'ryviu/ryviu.php';
            update_option( 'active_plugins',  $active_plugins);
        }
    }

}
