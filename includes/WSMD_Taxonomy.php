<?php

namespace WSMD;

class WSMD_Taxonomy
{
    public function __construct()
    {
        add_action('init', array($this, 'register_taxonomy'));
        add_action('created_wsmd-taxonomy', array($this, 'limit_term_hierarchy'), 10, 2);
        add_action('edited_wsmd-taxonomy', array($this, 'limit_term_hierarchy'), 10, 2);
        add_action('delete_wsmd-taxonomy', array($this, 'delete_user_terms'), 10, 2);
        add_filter('manage_edit-wsmd-taxonomy_columns', array($this, 'add_custom_columns'));
        add_filter('manage_wsmd-taxonomy_custom_column', array($this, 'manage_custom_columns'), 10, 3);
        add_filter('manage_edit-wsmd-taxonomy_sortable_columns', array($this, 'add_sortable_columns'));
        add_filter('get_terms', array($this, 'order_terms_by_count'), 10, 3);
        add_action('set_object_terms', array($this, 'update_user_term_count'), 10, 6);
    }

    /**
     * Update the user count meta value for the term.
     *
     * @param int $object_id The object ID.
     * @param array $terms The term IDs.
     * @param array $tt_ids The term taxonomy IDs.
     * @param string $taxonomy The taxonomy.
     * @param bool $append Whether to append the terms.
     * @param array $old_tt_ids The old term taxonomy IDs.
     */
    public function update_user_term_count($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
    {
        if ($taxonomy !== 'wsmd-taxonomy') {
            return;
        }

        // Get the term IDs
        $term_ids = array_unique(array_merge($terms, $old_tt_ids));

        // Update the user count for each term
        foreach ($term_ids as $term_id) {
            $user_count = self::get_user_term_count($term_id);
            update_term_meta($term_id, 'user_count_wsmd-taxonomy', $user_count);
        }
    }

    /**
     * Make custom columns sortable.
     *
     * @param array $columns The existing sortable columns.
     * @return array Modified sortable columns.
     */
    public function add_sortable_columns($columns)
    {
        $columns['count'] = 'count';
        return $columns;
    }

    /**
     * Order terms by user count custom column.
     *
     * @param array $terms The terms.
     * @param array $taxonomies The taxonomy array.
     * @param array $args The terms query arguments.
     * @return array Modified terms.
     */
    public function order_terms_by_count($terms, $taxonomies, $args)
    {
        if (is_admin() && in_array('wsmd-taxonomy', $taxonomies) && isset($args['orderby']) && $args['orderby'] === 'count') {
            usort($terms, function ($a, $b) use ($args) {
                $count_a = (int) get_term_meta($a->term_id, 'user_count_wsmd-taxonomy', true);
                $count_b = (int) get_term_meta($b->term_id, 'user_count_wsmd-taxonomy', true);
                if ($args['order'] === 'asc') {
                    return $count_a - $count_b;
                } else {
                    return $count_b - $count_a;
                }
            });
        }

        return $terms;
    }

    /**
     * Register the custom taxonomy.
     * @return void
     */
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
     * Add custom columns to the taxonomy list table.
     *
     * @param array $columns The existing columns.
     * @return array Modified columns.
     */
    public function add_custom_columns($columns)
    {
        // Remove posts column
        unset($columns['posts']);

        // Add a count column
        $columns['count'] = __('User Count', 'wsmd');
        return $columns;
    }

    /**
     * Manage custom columns content.
     *
     * @param string $content The column content.
     * @param string $column_name The column name.
     * @param int $term_id The term ID.
     * @return string The modified column content.
     */
    public function manage_custom_columns($content, $column_name, $term_id)
    {
        // Display the count of users associated with the term
        if ($column_name === 'count') {
            $user_count = $this->get_user_term_count($term_id);
            $term_link = add_query_arg(array(
                'wsmd-taxonomy' => $term_id
            ), admin_url('users.php'));

            // Display the count as a link to the users list if the user count is greater than 0
            $content = $user_count === 0 ? $user_count : sprintf('<a href="%s">%d</a>', esc_url($term_link), $user_count);
        }
        return $content;
    }

    /**
     * Limit term hierarchy to only one level of hierarchy.
     * @param int $term_id The term ID.
     * @param int $tt_id The term taxonomy ID.
     */
    public function limit_term_hierarchy($term_id, $tt_id)
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
     * Remove associated terms for users when a term is deleted
     * @param int $term_id The term ID.
     * @param int $tt_id The term taxonomy ID.
     */
    public function delete_user_terms($term_id, $tt_id)
    {
        // Get all users
        $users = get_users(array('fields' => 'ID'));

        // Remove the term for each user
        foreach ($users as $user_id) {
            wp_remove_object_terms($user_id, $term_id, 'wsmd-taxonomy');
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
     * Get available terms that include parent terms with no children
     * and exclude parent terms that have children.
     * 
     * @return array $available_terms Array of WP_Term objects
     */
    public static function get_available_terms()
    {
        $terms = self::get_terms();

        // Get term IDs of parent terms that have children
        $parent_term_ids_with_children = array_map(function ($term) {
            return $term->parent;
        }, array_filter($terms, function ($term) {
            return $term->parent !== 0;
        }));

        // Filter terms to include parent terms with no children and child terms
        $available_terms = array_filter($terms, function ($term) use ($parent_term_ids_with_children) {
            return ($term->parent !== 0) || !in_array($term->term_id, $parent_term_ids_with_children);
        });

        return $available_terms;
    }

    /**
     * Get the count of users associated with a term.
     *
     * @param int $term_id The term ID.
     * @return int The count of users.
     */
    public static function get_user_term_count($term_id)
    {
        $args = array(
            'taxonomy' => 'wsmd-taxonomy',
            'term_id' => $term_id,
            'fields' => 'count'
        );
        return count(get_objects_in_term($term_id, 'wsmd-taxonomy', $args));
    }
}
