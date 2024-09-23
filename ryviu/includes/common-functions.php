<?php
    class CommonFunctions {

        public static function updateOption($option_key, $option_value){
            update_option($option_key, $option_value);
        }

        public static function clearStoreCache() {

            global $wp_fastest_cache;
    
            if ( function_exists( 'rocket_clean_domain' ) ) { // WP Rocket
                rocket_clean_domain();
            }
    
            if ( function_exists( 'wp_cache_flush' ) ) {
                wp_cache_flush();
            }
    
            // Purge entire WP Rocket cache.
            if (class_exists('\LiteSpeed\Purge')) {
                \LiteSpeed\Purge::purge_all();
            }
    
            if ( function_exists( 'wp_cache_clear_cache' ) ) { // WP Super Cache
                wp_cache_clear_cache();
            }
    
            if ( function_exists( 'w3tc_flush_posts' ) ) { // W3 Total Cache
                w3tc_flush_posts();
            }
            if ( has_action( 'ce_clear_cache' ) ) { // Cache Enabler
                do_action( 'ce_clear_cache' );
            }
            if ( class_exists( 'Breeze_PurgeCache' ) ) { // Breeze
                if ( method_exists( 'Breeze_PurgeCache', 'breeze_cache_flush' ) ) {
                    Breeze_PurgeCache::breeze_cache_flush();
                }
            }
    
            if ( class_exists( 'Swift_Performance_Cache' ) ) {
                if ( method_exists( 'Swift_Performance_Cache', 'clear_all_cache' ) ) {
                    Swift_Performance_Cache::clear_all_cache();
                }
            }
    
            if ( method_exists( 'WpFastestCache', 'deleteCache' ) && ! empty( $wp_fastest_cache ) ) { // WP Fastest Cache
                $wp_fastest_cache->deleteCache();
            }
    
            if ( class_exists( 'WpeCommon' ) ) { // Autoptimize
                if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
                    WpeCommon::purge_memcached();
                }
                if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
                    WpeCommon::clear_maxcdn_cache();
                }
                if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
                    WpeCommon::purge_varnish_cache();
                }
            }
    
            if ( function_exists('sg_cachepress_purge_cache') ) { // SGOptimzer
                sg_cachepress_purge_cache();
            }
        }
        
    }
?>