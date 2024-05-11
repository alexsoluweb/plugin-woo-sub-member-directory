<?php

namespace WSMD;

/**
 * Static helper functions for the plugin
 */
class WSMD_Helpers{

    /**
     * Get the products that are subscriptions
     * 
     * @return array The subscriptions products
     */
    public static function get_woo_subscriptions_products(){
        $subscriptions_products = [];
        $args = [
            'status' => 'publish',
            'limit' => -1,
            'type' => ['subscription', 'variable-subscription'],  // Filter for simple and variable subscription products
            'return' => 'objects'  // Return the actual product objects
        ];
        $products = wc_get_products($args);
        foreach ($products as $product) {
            $produc_id = (string)$product->get_id();
            $subscriptions_products[$produc_id] = $product->get_name();
        }
        return $subscriptions_products;
    }

    /**
     * Check if a user is a Member Directory
     * 
     * @param bool True if the user is a Member Directory, false otherwise
     * 
     * @return bool True if the user is a Member Directory, false otherwise
     */
    public static function is_member_directory($user_id){

        // Retrieve the settings
        $settings = WSMD::get_instance()->settings->get_settings();

        // Check if the user is a Member Directory, if empty means any user can be a Member Directory
        if( !isset($settings['wsmd_subscription_products']) || !is_array($settings['wsmd_subscription_products']) ){
            return true;
        }

        // TODO: Check if the user is a Member Directory

        return true;
    }
}
