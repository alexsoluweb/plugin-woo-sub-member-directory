<?php

namespace WSMD;

class WSMD_Shortcodes
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('wsmd_member_directory', array($this, 'render_member_directory_shortcode'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {
        global $post;
        
        if (!is_a($post, 'WP_Post')) {
            return;
        }

        if (has_shortcode($post->post_content, 'wsmd_member_directory')) {
            $current_site_language = WSMD_Helpers::get_current_site_language();
            $style_version = filemtime(WSMD_PATH . 'assets/css/member-directory.css');
            $script_version = filemtime(WSMD_PATH . 'assets/js/member-directory.js');
            wp_enqueue_style('wsmd-member-directory', WSMD_URL . 'assets/css/member-directory.css', array(), $style_version);
            wp_enqueue_script('wsmd-google-maps', 'https://maps.googleapis.com/maps/api/js?libraries=places,geometry&key=' . WSMD_Woo_Settings::get_settings('wsmd_google_maps_api_key') . '&language=' . $current_site_language, array(), null, true);
            wp_enqueue_script('wsmd-member-directory', WSMD_URL . 'assets/js/member-directory.js', array('wsmd-google-maps'), $script_version, true);
        }
    }

    /**
     * Render the member directory shortcode
     */
    public function render_member_directory_shortcode($atts)
    {
        $args = shortcode_atts(array(
            //
        ), $atts);

        ob_start();
        load_template(
            WSMD_PATH . 'templates/member-directory.php',
            true,
            array(
                'shortcode_args' => $args,
            ),
        );
        return ob_get_clean();
    }
}
