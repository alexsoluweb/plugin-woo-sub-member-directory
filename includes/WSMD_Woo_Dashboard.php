<?php

namespace WSMD;

/**
 * WooCommerce Member Directory Dashboard
 */
class WSMD_Woo_Dashboard
{
    public function __construct()
    {
        add_action('init', array(__CLASS__, 'add_endpoint'));
        add_filter('woocommerce_get_query_vars', array($this, 'add_query_vars'));
        add_filter('query_vars', array($this, 'add_query_vars'), 0);
        add_filter('woocommerce_account_menu_items', array($this, 'add_member_directory_tab'));
        add_filter('the_title', array($this, 'change_endpoint_title'));
        add_action('woocommerce_account_wsmd_woo_dashboard_endpoint', array($this, 'wsmd_woo_dashboard_page'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add the Member Directory endpoint
     */
    public static function add_endpoint()
    {
        add_rewrite_endpoint('wsmd_woo_dashboard', EP_ROOT | EP_PAGES);
    }

    /**
     * Remove the Member Directory endpoint
     */
    public static function remove_endpoint()
    {
        add_rewrite_endpoint('wsmd_woo_dashboard', EP_NONE);
    }

    /**
     * Add the Member Directory query var
     */
    public function add_query_vars($vars)
    {
        $vars['wsmd_woo_dashboard'] = 'wsmd_woo_dashboard';
        return $vars;
    }

    /**
     * Add the Member Directory tab to the My Account page
     */
    public function add_member_directory_tab($items)
    {
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);
        $items['wsmd_woo_dashboard'] = __('Member Directory', 'wsmd');
        $items['customer-logout'] = $logout;
        return $items;
    }

    /**
     * Change the title of the Member Directory page
     */
    public function change_endpoint_title($title)
    {
        global $wp_query;
        $is_member_directory = isset($wp_query->query_vars['wsmd_woo_dashboard']);
        if ($is_member_directory && is_main_query() && in_the_loop() && is_account_page()) {
            $title = __('Member Directory', 'wsmd');
        }
        return $title;
    }

    /**
     * Display the Member Directory page. 
     * User is safeley logged in at this point. 
     * No need to check for user capabilities.
     */
    public function wsmd_woo_dashboard_page()
    {

        $current_user = wp_get_current_user();

        if (WSMD_Helpers::is_member_directory_user($current_user->ID, false)) {
            load_template(
                WSMD_PATH . 'templates/dashboard.php',
                true,
                array(
                    'current_user' => $current_user,
                    'user_settings' => WSMD_Users::get_user_settings($current_user->ID),
                    'grouped_terms' => WSMD_Helpers::format_terms_for_grouped_select_options(
                        WSMD_Taxonomy::get_terms(),
                    ),
                ),
            );
        } else {
            echo '<div id="wsmd-dashboard" class="not-member-directory">';
            echo '<p>' . __('You are not a Member Directory.', 'wsmd') . '<br>';
            echo __('Please subscribe to a membership plan to access the member directory.', 'wsmd') . '</p>';

            $product_ids = WSMD_Woo_Settings::get_settings('wsmd_subscription_products');

            // Display the subscription products
            if ($product_ids) {
                echo '<h3>' . esc_html__('Available membership plans:', 'wsmd') . '</h3>';
                echo '<ul>';
                foreach ($product_ids as $product_id) {
                    $product = wc_get_product($product_id);
                    echo '<li><a href="' . esc_url($product->add_to_cart_url()) . '">' . esc_html($product->get_name()) . '</a></li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {
        global $wp;

        if (!is_a($wp, 'WP')) {
            return;
        }

        if (is_account_page() && is_user_logged_in() && isset($wp->query_vars['wsmd_woo_dashboard'])) {
            $style_version = filemtime(WSMD_PATH . 'assets/css/dashboard.css');
            $script_version = filemtime(WSMD_PATH . 'assets/js/dashboard.js');
            $google_map_params = array(
                'loading' => 'async',
                'libraries' => 'places,geometry',
                'key' => WSMD_Woo_Settings::get_settings('wsmd_google_maps_api_key'),
                'language' => WSMD_Helpers::get_current_site_language(),
                'callback' => 'WSMD.initApp',
            );
            wp_enqueue_style('wsmd-dashboard', WSMD_URL . 'assets/css/dashboard.css', array(), $style_version);
            wp_enqueue_script('wsmd-dashboard', WSMD_URL . 'assets/js/dashboard.js', array(), $script_version, true);
            wp_enqueue_script('wsmd-google-maps', add_query_arg($google_map_params, 'https://maps.googleapis.com/maps/api/js'), array('wsmd-dashboard'), 'v3', true);
        }
    }
}
