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
     * @param int $user_id The user ID to check
     * 
     * @return bool True if the user is a Member Directory, false otherwise
     */
    public static function is_member_directory($user_id){
        // Retrieve settings and the specific subscription products considered for the Member Directory
        $settings = WSMD_Woo_Settings::get_settings();
        $wsmd_subscription_products = isset($settings['wsmd_subscription_products']) ? $settings['wsmd_subscription_products'] : [];

        // Return false if no products are configured
        if (empty($wsmd_subscription_products)) {
            return false;
        }

        // Check if the user has an active subscription for any of the specified products
        foreach ($wsmd_subscription_products as $product_id) {
            if (wcs_user_has_subscription($user_id, $product_id, array('active', 'pending-cancel'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the members for the Member Directory
     * 
     * @return array The members
     */
    public static function get_members(){

        $members = [];
        
        // Get all users
        $users = get_users([
            'role__in' => ['subscriber', 'customer'],
            'fields' => ['ID'],
            'number' => -1
        ]);

        // Loop through users
        foreach ($users as $user) {
            $user_id = $user->ID;
            if (self::is_member_directory($user_id)) {
                $members[$user_id] = WSMD_User_Settings::get_user_settings($user_id);
            }
        }

        // Return the members
        return $members;
    }
}
