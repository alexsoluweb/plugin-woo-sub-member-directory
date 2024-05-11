<?php

namespace WSMD;

/**
 * Settings in Woocommerce 
 */
class WSMD_Settings{

    public function __construct(){

        // Add a new tab in the Woocommerce settings
        add_filter( 'woocommerce_settings_tabs_array', function( $tabs ){
            $tabs['wsmd'] = __( 'Member Directory', 'wsmd' );
            return $tabs;
        }, 100 );

        // Add the settings to the new tab
        add_action( 'woocommerce_settings_tabs_wsmd', function(){
            woocommerce_admin_fields( $this->get_fields_settings() );
        } );

        // Save the settings
        add_action( 'woocommerce_update_options_wsmd', function(){
            woocommerce_update_options( $this->get_fields_settings() );
        } );
    }

    /**
     * Get the settings for the new tab
     *
     * @return array
     */
    private function get_fields_settings(){
        $settings = array(
            'wsmd_section_title' => array(
                'name'     => __( 'Member Directory Settings', 'wsmd' ),
                'type'     => 'title',
                'id'       => 'wsmd_section_title',
            ),
            // Select subscription products
            'wsmd_subscription_products' => array(
                'name'     => __( 'Subscription Products', 'wsmd' ),
                'type'     => 'multiselect',
                'class'    => 'wc-enhanced-select',
                'desc_tip' => true,
                'desc'     => __( 'Select the product subscription(s) that will be considered for the Member Directory. If empty, any will be considered.', 'wsmd' ),
                'id'       => 'wsmd_subscription_products',
                'options'  => WSMD_Helpers::get_woo_subscriptions_products(),
                'custom_attributes' => array(
                    'data-placeholder' => __( 'Select subscription products', 'wsmd' ),
                    'multiple' => 'multiple'
                ),

            ),
            // Google Maps API Key
            'wsmd_google_maps_api_key' => array(
                'name'     => __( 'Google Maps API Key', 'wsmd' ),
                'type'     => 'text',
                'desc_tip' => true,
                'desc'     => __( 'Enter your Google Maps API Key.', 'wsmd' ),
                'id'       => 'wsmd_google_maps_api_key'
            ),
            'section_end' => array(
                'type'     => 'sectionend',
                'id'       => 'wsmd_section_end'
            )
        );

        return apply_filters( 'wsmd_settings', $settings );
    }

    /**
     * Get the settings
     *
     * @return array
     */
    public function get_settings(){
        return array(
            'wsmd_subscription_products' => get_option( 'wsmd_subscription_products', array() ),
            'wsmd_google_maps_api_key' => get_option( 'wsmd_google_maps_api_key', '' ),
        );
    }
}