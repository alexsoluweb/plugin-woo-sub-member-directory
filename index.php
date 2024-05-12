<?php
/**
 * Plugin Name: Woo Sub Member Directory
 * Description: A plugin to create a member directory for Woo Subscriptions
 * Version: 1.0.0
 * Author: Alexsoluweb
 * Author URI: https://alexsoluweb.ca
 * Text Domain: wsmd
 * Domain Path: /languages
 * License: UNLICENSED
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WSMD_PATH', plugin_dir_path( __FILE__ ) );
define( 'WSMD_URL', plugin_dir_url( __FILE__ ) );

// Load text domain
add_action( 'plugins_loaded', 'wsmd_load_textdomain' );
function wsmd_load_textdomain() {
    load_plugin_textdomain( 'wsmd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Add settings action links to the plugins page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wsmd' ) . '">' . __( 'Settings', 'wsmd' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
} );

// Activation hook, code to run on activation.
register_activation_hook( __FILE__, function() {
    WSMD\WSMD_Dashboard::add_endpoint();
    flush_rewrite_rules();
});

// Deactivation hook, code to run on deactivation.
register_deactivation_hook( __FILE__, function() {
    WSMD\WSMD_Dashboard::remove_endpoint();
    flush_rewrite_rules();
});

// Composer autoload
require_once WSMD_PATH . 'vendor/autoload.php';

// Initialize the plugin
WSMD\WSMD::init();