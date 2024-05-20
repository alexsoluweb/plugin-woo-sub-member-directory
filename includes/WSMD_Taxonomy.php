<?php

namespace WSMD;

class WSMD_Taxonomy
{
    public function __construct()
    {
        add_action('init', array($this, 'register_taxonomy'));
        add_action('created_wsmd-taxonomy', array($this, 'check_term_hierarchy'), 10, 2);
        add_action('edited_wsmd-taxonomy', array($this, 'check_term_hierarchy'), 10, 2);
    }

    public function register_taxonomy()
    {
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
     * Check the term hierarchy to ensure only one level of hierarchy.
     * @param int $term_id The term ID.
     * @param int $tt_id The term taxonomy ID.
     */
    public function check_term_hierarchy($term_id, $tt_id)
    {
        $term = get_term($term_id, 'wsmd-taxonomy');
        if ($term->parent) {
            $parent_term = get_term($term->parent, 'wsmd-taxonomy');
            if ($parent_term->parent) {
                // Remove the term to prevent it from being saved
                wp_delete_term($term_id, 'wsmd-taxonomy');
                // Display an error message
                wp_die(__('This taxonomy cannot have more than one level of hierarchy.', 'wsmd'), __('Term Hierarchy Error', 'wsmd'), array('back_link' => true));
            }
        }
    }

    /**
     * Get all terms
     * @return array $terms Array of WP_Term objects
     */
    public static function get_terms()
    {
        $terms = get_terms(array(
            'taxonomy' => 'wsmd-taxonomy',
            'hide_empty' => false
        ));

        return $terms;
    }

    /**
     * Get number of members associated with each term
     * @return array<int, int> $terms_count Array of term ID and count
     */
    public static function get_terms_count()
    {
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
