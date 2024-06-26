<?php

namespace WSMD;

/**
 * Static helper functions for the plugin
 */
class WSMD_Helpers
{

    /**
     * Get the products that are subscriptions
     * 
     * @return array The subscriptions products
     */
    public static function get_woo_subscriptions_products()
    {
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
     * Check if a user has an active subscription for the Member Directory
     * 
     * @param int $user_id The user ID to check
     * 
     * @return bool True if the user has an active subscription, false otherwise
     */
    public static function is_active_member_subscription($user_id)
    {
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
     * Check if a user is a member of the Member Directory
     * 
     * @param int $user_id The user ID to check
     * @param bool $check_profile Whether to check if the user has hidden their profile
     * 
     * @return bool True if the user is a member of the Member Directory, false otherwise
     */
    public static function is_member_directory_user($user_id, $check_profile = true)
    {
        // Check if the user has hidden their profile
        if (WSMD_Users::get_user_settings($user_id, 'wsmd_hide_profile') && $check_profile) {
            return false;
        }
        // Check if the user is forced_in by the admin
        elseif (WSMD_Users::get_user_settings($user_id, 'wsmd_is_admin_allowed') === 'force_in') {
            return true;
        }
        // Check if the user is forced_out by the admin
        elseif (WSMD_Users::get_user_settings($user_id, 'wsmd_is_admin_allowed') === 'force_out') {
            return false;
        }
        // Else, check if the user has an active subscription
        elseif (self::is_active_member_subscription($user_id)) {
            return true;
        }
        // The user is not a member directory at this point
        else {
            return false;
        }
    }

    /**
     * Get the members for the Member Directory
     * 
     * @return array The members
     */
    public static function get_members()
    {
        // Attempt to retrieve members from the cache
        $members = wp_cache_get('wsmd_members', 'wsmd');

        // If the cache is empty, perform the query and cache the results
        if ($members === false) {


            // Get all users
            $users = get_users([
                // 'role__in' => ['subscriber', 'customer'],
                'fields' => ['ID'],
                'number' => -1,
                // Get only the user that has the wsmd_geocode meta key set and not empty
                'meta_query' => [
                    'relation' => 'AND', // AND condition
                    [
                        'key' => 'wsmd_geocode',
                        'compare' => 'EXISTS'
                    ],
                    [
                        'key' => 'wsmd_geocode',
                        'value' => '',
                        'compare' => '!='
                    ],
                ]
            ]);

            // If no users are found, return an empty array
            if (empty($users)) {
                return [];
            }

            // Loop through users
            $members = [];
            foreach ($users as $user) {
                $user_id = $user->ID;

                // Check if the user is a member of the Member Directory
                if (self::is_member_directory_user($user_id)) {
                    $members[$user_id] = WSMD_Users::get_user_settings($user_id);
                }
            }

            // Cache the members
            wp_cache_set('wsmd_members', $members, 'wsmd', HOUR_IN_SECONDS); // Cache for 1 hour
        }

        // Return the members
        return $members;
    }

    /**
     * Format terms for grouped select options (for tom-select).
     * This function groups terms by parent term and returns an array of formatted terms.
     * It only supports one level of hierarchy.
     * 
     * @param array $terms Array of WP_Term objects
     * @return array $formatted_terms Array of formatted terms
     */
    public static function format_terms_for_grouped_select_options($terms)
    {
        $grouped_terms = [];

        foreach ($terms as $term) {
            if ($term->parent) {
                $parent_term_id = $term->parent;
                $parent_term = get_term($parent_term_id);
                if (!isset($grouped_terms[$parent_term_id])) {
                    $grouped_terms[$parent_term_id] = [
                        'label' => $parent_term->name,
                        'terms' => []
                    ];
                }
                $grouped_terms[$parent_term_id]['terms'][] = $term;
            } else {
                if (!isset($grouped_terms[$term->term_id])) {
                    $grouped_terms[$term->term_id] = [
                        'label' => $term->name,
                        'terms' => []
                    ];
                }
            }
        }

        return $grouped_terms;
    }

    /**
     * Get the current site language
     * Support: WPML, POLYLANG or fallback to default WP language
     * 
     * @return string The site language
     */
    public static function get_current_site_language()
    {
        if (defined('ICL_LANGUAGE_CODE')) {
            return apply_filters('wpml_current_language', NULL);
        } elseif (function_exists('pll_current_language')) {
            return pll_current_language();
        } else {
            return substr(get_locale(), 0, 2);
        }
    }

    /**
     * Get default site language
     * Support: WPML, POLYLANG or fallback to default WP language
     */
    public static function get_default_site_language()
    {
        if (defined('ICL_LANGUAGE_CODE')) {
            return apply_filters('wpml_default_language', NULL);;
        } elseif (function_exists('pll_default_language')) {
            return pll_default_language();
        } else {
            return substr(get_locale(), 0, 2);
        }
    }
}
