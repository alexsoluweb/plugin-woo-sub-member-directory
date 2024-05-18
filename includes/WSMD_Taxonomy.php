<?php

namespace WSMD;

class WSMD_Taxonomy {
    public function __construct() {
        add_action('init', array($this, 'register_taxonomy'));
    }

    public function register_taxonomy() {
        $labels = array(
            'name'              => __('Member Directory taxonomy', 'wsmd'),
            'singular_name'     => __('Member Directory taxonomy', 'wsmd'),
            'search_items'      => __('Search Member Directory taxonomy', 'wsmd'),
            'all_items'         => __('All Member Directory taxonomy', 'wsmd'),
            'parent_item'       => __('Parent Member Directory taxonomy', 'wsmd'),
            'parent_item_colon' => __('Parent Member Directory taxonomy:', 'wsmd'),
            'edit_item'         => __('Edit Member Directory taxonomy', 'wsmd'),
            'update_item'       => __('Update Member Directory taxonomy', 'wsmd'),
            'add_new_item'      => __('Add New Member Directory taxonomy', 'wsmd'),
            'new_item_name'     => __('New Member Directory taxonomy Name', 'wsmd'),
            'menu_name'         => __('Member Directory taxonomy', 'wsmd'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => false,
            'rewrite'           => false
        );

        register_taxonomy('wsmd-taxonomy', null, $args);
    }

    /**
     * Get all terms
     * @return array $terms Array of WP_Term objects
     */
    public static function get_terms() {
        $terms = get_terms(array(
            'taxonomy' => 'wsmd-taxonomy',
            'hide_empty' => false
        ));

        return $terms;
    }

    /**
     * Get number of members assiated with each term
     * @return array<int, int> $terms_count Array of term ID and count
     */
    public static function get_terms_count() {
        $terms_count = array();
        $terms = self::get_terms();
        foreach ($terms as $term) {
            $term_id = $term->term_id;
            $term_count = count(get_objects_in_term($term_id, 'wsmd-taxonomy'));
            $terms_count[$term_id] = $term_count;
        }

        return $terms_count;
    }
}
