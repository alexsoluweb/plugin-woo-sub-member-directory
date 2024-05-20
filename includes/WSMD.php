<?php

namespace WSMD;

/**
 * Main singleton class for the plugin
 */
class WSMD
{

    /**
     * The instance of the class
     *
     * @var WSMD
     */
    private static $instance = null;

    /**
     * The constructor
     */
    private function __construct()
    {

        // Check if the plugin dependencies are met, otherwise stop the plugin
        add_action('plugins_loaded', function () {
            if (!$this->check_plugin_dependencies()) return;
        });

        // Woocommerce settings
        new WSMD_Woo_Settings();
        // User class
        new WSMD_Users();
        // Dashboard class
        new WSMD_Woo_Dashboard();
        // AJAX class
        new WSMD_AJAX();
        // Shortcodes class
        new WSMD_Shortcodes();
        // Taxonomy class
        new WSMD_Taxonomy();
    }

    /**
     * Initialize the class singleton
     */
    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    /**
     * Get the instance of the class
     *
     * @return WSMD
     */
    public static function get_instance()
    {
        return self::$instance;
    }

    /**
     * Check if the plugin dependencies are met
     * 
     * @return bool True if the dependencies are met, false otherwise
     */
    private function check_plugin_dependencies()
    {

        // WooCommerce
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function () {
?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e('Woo Sub Member Directory requires WooCommerce to be installed and activated.', 'wsmd'); ?></p>
                </div>
            <?php
            });
            return false;
        }

        // WooCommerce Subscriptions
        if (!class_exists('WC_Subscriptions')) {
            add_action('admin_notices', function () {
            ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e('Woo Sub Member Directory requires WooCommerce Subscriptions to be installed and activated.', 'wsmd'); ?></p>
                </div>
<?php
            });
            return false;
        }

        return true;
    }
}
