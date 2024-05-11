<?php
/**
 * Plugin Name: Woo Sub Member Directory
 * Description: A plugin to create a member directory for Woo Subscriptions
 * Version: 1.0.0
 * Author: Alexsoluweb
 * Author URI: https://alexsoluweb.ca
 * Text Domain: wsmd
 * Domain Path: /languages
 * License: Unlicense
 */

use WSMD\WSMD;
use WSMD\WSMD_Helpers;

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

// Activation hook
register_activation_hook( __FILE__, 'wsmd_activate' );
function wsmd_activate() {
    // Code to run on activation
    // For example: Create database tables, initialize options, etc.
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'wsmd_deactivate' );
function wsmd_deactivate() {
    // Code to run on deactivation
    // For example: Clean up settings, etc.
}

// Composer autoload
require_once WSMD_PATH . 'vendor/autoload.php';

// Initialize the plugin
WSMD::init();

// Add test code here
add_action('init', function(){
   //
});