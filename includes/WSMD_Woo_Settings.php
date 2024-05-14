<?php

namespace WSMD;

/**
 * Settings in Woocommerce 
 */
class WSMD_Woo_Settings{

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
                'desc'     => __( 'Select the product subscription(s) that will be considered for the Member Directory.', 'wsmd' ),
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
            // Edit taxonomies link
            'wsmd_info_edit_taxonomy' => array(
                'name'     => __( 'Taxonomies', 'wsmd' ),
                'type'     => 'info',
                'id'       => 'wsmd_info_edit_taxonomy',
                'text'     => '<a href="' . admin_url() . 'edit-tags.php?taxonomy=wsmd-taxonomy">' . __('Edit here', 'wsmd') . '</a>',
            ),
            'section_end' => array(
                'type'     => 'sectionend',
                'id'       => 'wsmd_section_end'
            )
        );

        return apply_filters( 'wsmd_settings', $settings );
    }

    /**
     * Get the custom settings in Woocommerce
     * 
     * @param string $key The key of the setting to retrieve: \
     * wsmd_subscription_products, \
     * wsmd_google_maps_api_key
     *
     * @return Mixed If the key is empty, return all the settings, \
     * otherwise return the setting value, or an empty string if the key does not exist.
     */
    public static function get_settings($key = ''){

        if( empty($key) ){
            return array(
                'wsmd_subscription_products' => get_option( 'wsmd_subscription_products', '' ),
                'wsmd_google_maps_api_key' => get_option( 'wsmd_google_maps_api_key', '' ),
            );
        }else{
            return get_option( $key, '' );
        }
    }
}