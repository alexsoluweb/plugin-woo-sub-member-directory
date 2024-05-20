<?php

namespace WSMD;

/**
 * User settings for the Member Directory
 */
class WSMD_Users
{

    public function __construct()
    {

        // Register fields for the user
        add_action('edit_user_profile', array($this, 'add_user_fields'), 100);
        add_action('show_user_profile', array($this, 'add_user_fields'), 100);

        // Save the fields
        // Adress, geolocation can only be sat in the frontend for now.
        // This is to prevent bad geo-coordinates from being saved with the actual mecanism in place.
        add_action('edit_user_profile_update', array(__CLASS__, 'save_user_settings'));
        add_action('personal_options_update', array(__CLASS__, 'save_user_settings'));

        // Add a new column in the user list: Member Directory
        add_filter('manage_users_columns', function ($columns) {
            $columns['wsmd_active'] = __('Member Directory', 'wsmd');
            return $columns;
        });

        add_action('manage_users_custom_column', array($this, 'add_user_column_content'), 10, 3);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('pre_get_users', array($this, 'filter_users_by_taxonomy'));
    }

    /**
     * Enqueue scripts and styles.
     * @param string $hook The current admin page.
     * @return void
     */
    public function enqueue_scripts($hook)
    {
        // Only load on user profile and edit user pages
        if ('user-edit.php' != $hook && 'profile.php' != $hook) {
            return;
        }

        $script_version = filemtime(WSMD_PATH . 'assets/js/admin-users.js');
        $style_version = filemtime(WSMD_PATH . 'assets/css/admin-users.css');
        wp_enqueue_script('wsmd-admin-users', WSMD_URL . 'assets/js/admin-users.js', array(), $script_version, true);
        wp_enqueue_style('wsmd-admin-users', WSMD_URL . 'assets/css/admin-users.css', array(), $style_version);
    }

    /**
     * Filter users by custom taxonomy term.
     *
     * @param WP_User_Query $query The WP_User_Query instance.
     */
    public function filter_users_by_taxonomy($query)
    {
        global $pagenow;

        if (is_admin() && 'users.php' === $pagenow && isset($_GET['wsmd-taxonomy'])) {
            $term_id = intval($_GET['wsmd-taxonomy']);
            $term = get_term($term_id, 'wsmd-taxonomy');
            if ($term && !is_wp_error($term)) {
                $user_ids = get_objects_in_term($term_id, 'wsmd-taxonomy', array('fields' => 'ids'));

                // If there are users with the term, include them in the query
                if (!empty($user_ids)) {
                    $query->set('include', $user_ids);
                } else {
                    // If there are no users with the term, include an impossible ID
                    $query->set('include', array(0));
                }
            }
        }
    }

    /**
     * Add the content to the new column.
     * @param string $value The column value.
     * @param string $column_name The column name.
     * @param int $user_id The user ID.
     * @return string The modified column value.
     */
    public function add_user_column_content($value, $column_name, $user_id)
    {
        if ($column_name === 'wsmd_active') {
            // Retrieve the user meta value or default to 'default' if it's not set
            $is_admin_allowed = self::get_user_settings($user_id, 'wsmd_is_admin_allowed');

            echo '<div style="display: flex; align-items: center; line-height: 1;">';

            // Check if the user is a Member Directory
            $is_member_directory = WSMD_Helpers::is_member_directory($user_id);
            if ($is_member_directory) {
                $value = '<span class="dashicons dashicons-yes"></span>';
            } else {
                $value = '<span class="dashicons dashicons-no"></span>';
            }

            // Add the is_admin_allowed
            $value .= ' | <span> ' . esc_html($is_admin_allowed) . '</span>';

            echo '</div>';
        }
        return $value;
    }

    /**
     * Add the user fields
     */
    public function add_user_fields($user)
    {
        // Retrieve the user is_admin_allowed settings
        $is_admin_allowed = self::get_user_settings($user->ID, 'wsmd_is_admin_allowed');

        // Retrieve the custom taxonomy terms
        $grouped_terms = WSMD_Helpers::format_terms_for_grouped_select_options(WSMD_Taxonomy::get_terms());

        // Retrieve the user's selected terms
        $user_terms = wp_get_object_terms($user->ID, 'wsmd-taxonomy', array('fields' => 'ids'));

?>
        <h3><?php _e('Member Directory', 'wsmd'); ?></h3>
        <table id="wsmd-form" class="form-table">
            <!-- is_admin_allowed -->
            <tr>
                <th><label for="wsmd_is_admin_allowed"><?php _e('Force this member to be listed or removed in the Member Directory', 'wsmd'); ?></label></th>
                <td style="display: flex; flex-wrap: wrap;; flex-direction: column; gap: 10px;">
                    <label style="margin-right: 10px;">
                        <input type="radio" name="wsmd_is_admin_allowed" value="default" <?php checked($is_admin_allowed, 'default'); ?>>
                        <?php _e('Default (Let the subscription products decide)', 'wsmd'); ?>
                    </label>
                    <label style="margin-right: 10px;">
                        <input type="radio" name="wsmd_is_admin_allowed" value="force_in" <?php checked($is_admin_allowed, 'force_in'); ?>>
                        <?php _e('Force this member to be listed in the Member Directory', 'wsmd'); ?>
                    </label>
                    <label>
                        <input type="radio" name="wsmd_is_admin_allowed" value="force_out" <?php checked($is_admin_allowed, 'force_out'); ?>>
                        <?php _e('Force this member to be removed from the Member Directory', 'wsmd'); ?>
                    </label>
                </td>
            </tr>
            <!-- Taxonomies -->
            <tr>
                <th><label for="wsmd_taxonomies"><?php _e('Taxonomies', 'wsmd'); ?></label></th>
                <td>
                    <select name="wsmd_taxonomies[]" id="wsmd_taxonomies" multiple="multiple" class="regular-text" placeholder="<?php esc_attr_e('Select taxonomies', 'wsmd'); ?>">
                        <?php foreach ($grouped_terms as $parent_id => $group) { ?>
                            <?php if (empty($group['terms'])) { ?>
                                <option value="<?php echo esc_attr($parent_id); ?>" <?php echo in_array($parent_id, $user_terms) ? 'selected="selected"' : ''; ?>>
                                    <?php echo esc_html($group['label']); ?>
                                </option>
                            <?php } else { ?>
                                <optgroup label="<?php echo esc_attr($group['label']); ?>">
                                    <?php foreach ($group['terms'] as $term) { ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo in_array($term->term_id, $user_terms) ? 'selected="selected"' : ''; ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        <?php } ?>
                    </select>

                    <p class="description"><?php _e('Select taxonomies for the user.', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Occupation -->
            <tr>
                <th><label for="wsmd_occupation"><?php _e('Occupation', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_occupation" id="wsmd_occupation" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_occupation', true)); ?>" class="regular-text">
                    <p class="description"><?php _e('The occupation of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Company -->
            <tr>
                <th><label for="wsmd_company"><?php _e('Company', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_company" id="wsmd_company" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_company', true)); ?>" class="regular-text">
                    <p class="description"><?php _e('The company of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Website -->
            <tr>
                <th><label for="wsmd_website"><?php _e('Website', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_website" id="wsmd_website" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_website', true)); ?>" class="regular-text">
                    <p class="description"><?php _e('The website of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Phone -->
            <tr>
                <th><label for="wsmd_phone"><?php _e('Phone', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_phone" id="wsmd_phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_phone', true)); ?>" class="regular-text">
                    <p class="description"><?php _e('The phone number of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Email -->
            <tr>
                <th><label for="wsmd_email"><?php _e('Email', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_email" id="wsmd_email" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_email', true)); ?>" class="regular-text">
                    <p class="description"><?php _e('The email of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Geolocation coordinates -->
            <tr>
                <th><label for="wsmd_geocode"><?php _e('Geo-coordinates', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_geocode" id="wsmd_geocode" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_geocode', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The geo-coordinates of the user. Example: 45.5017, -73.5673', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Address -->
            <tr>
                <th><label for="wsmd_address"><?php _e('Address', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_address" id="wsmd_address" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_address', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The address of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- City -->
            <tr>
                <th><label for="wsmd_city"><?php _e('City', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_city" id="wsmd_city" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_city', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The city of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Province/State -->
            <tr>
                <th><label for="wsmd_province_state"><?php _e('Province/State', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_province_state" id="wsmd_province_state" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_province_state', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The province/state of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Postal Code/Zip -->
            <tr>
                <th><label for="wsmd_postal_zip_code"><?php _e('Postal/Zip code', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_postal_zip_code" id="wsmd_postal_zip_code" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_postal_zip_code', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The postal/zip code of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Country -->
            <tr>
                <th><label for="wsmd_country"><?php _e('Country', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_country" id="wsmd_country" value="<?php echo esc_attr(get_user_meta($user->ID, 'wsmd_country', true)); ?>" class="regular-text" readonly>
                    <p class="description"><?php _e('The country of the user', 'wsmd'); ?></p>
                </td>
            </tr>
        </table>
<?php
    }

    /**
     * Save the user settings.
     * 
     * @return Array Fields that are not valid
     */
    public static function save_user_settings($user_id)
    {
        // Init results
        $results = array();

        // Sanitize and save the fields is_admin_allowed
        if (isset($_POST['wsmd_is_admin_allowed'])) {
            $_POST['wsmd_is_admin_allowed'] = sanitize_text_field(wp_unslash($_POST['wsmd_is_admin_allowed']));
            // Check if the is_admin_allowed is valid
            if (!in_array($_POST['wsmd_is_admin_allowed'], array('default', 'force_in', 'force_out'))) {
                $results['wsmd_is_admin_allowed'] = __('Invalid is_admin_allowed', 'wsmd');
            }
            update_user_meta($user_id, 'wsmd_is_admin_allowed', $_POST['wsmd_is_admin_allowed']);
            $results['wsmd_is_admin_allowed']['success'] = true;
        }

        // Sanitize and save the fields geolocation
        if (isset($_POST['wsmd_geocode'])) {
            $_POST['wsmd_geocode'] = sanitize_text_field(wp_unslash($_POST['wsmd_geocode']));
            if (!empty($_POST['wsmd_geocode'])) {
                update_user_meta($user_id, 'wsmd_geocode', $_POST['wsmd_geocode']);
            } else {
                $results['wsmd_geocode'] = __('Geocode is required', 'wsmd');
            }
        }

        // Sanitize and save the fields occupation
        if (isset($_POST['wsmd_occupation'])) {
            $_POST['wsmd_occupation'] = sanitize_text_field(wp_unslash($_POST['wsmd_occupation']));
            if (!empty($_POST['wsmd_occupation'])) {
                // Limit the occupation to 32 characters
                if (strlen($_POST['wsmd_occupation']) > 32) {
                    $results['wsmd_occupation'] = __('Occupation is too long (32 characters max)', 'wsmd');
                } else {
                    update_user_meta($user_id, 'wsmd_occupation', $_POST['wsmd_occupation']);
                }
            } else {
                $results['wsmd_occupation'] = __('Occupation is required', 'wsmd');
            }
        }

        // Sanitize and save the fields company
        if (isset($_POST['wsmd_company'])) {
            $_POST['wsmd_company'] = sanitize_text_field(wp_unslash($_POST['wsmd_company']));
            if (!empty($_POST['wsmd_company'])) {
                // Limit the company to 32 characters
                if (strlen($_POST['wsmd_company']) > 32) {
                    $results['wsmd_company'] = __('Company is too long (32 characters max)', 'wsmd');
                } else {
                    update_user_meta($user_id, 'wsmd_company', $_POST['wsmd_company']);
                }
            } else {
                $results['wsmd_company'] = __('Company is required', 'wsmd');
            }
        }

        // Sanitize and save the fields address
        if (isset($_POST['wsmd_address'])) {
            $_POST['wsmd_address'] = sanitize_text_field(wp_unslash($_POST['wsmd_address']));
            if (!empty($_POST['wsmd_address'])) {
                update_user_meta($user_id, 'wsmd_address', $_POST['wsmd_address']);
            } else {
                $results['wsmd_address'] = __('Address is required', 'wsmd');
            }
        }

        // Sanitize and save the fields city
        if (isset($_POST['wsmd_city'])) {
            $_POST['wsmd_city'] = sanitize_text_field(wp_unslash($_POST['wsmd_city']));
            if (!empty($_POST['wsmd_city'])) {
                update_user_meta($user_id, 'wsmd_city', $_POST['wsmd_city']);
            } else {
                $results['wsmd_city'] = __('City is required', 'wsmd');
            }
        }

        // Sanitize and save the fields province/state
        if (isset($_POST['wsmd_province_state'])) {
            $_POST['wsmd_province_state'] = sanitize_text_field(wp_unslash($_POST['wsmd_province_state']));
            if (!empty($_POST['wsmd_province_state'])) {
                update_user_meta($user_id, 'wsmd_province_state', $_POST['wsmd_province_state']);
            } else {
                $results['wsmd_province_state'] = __('Province/State is required', 'wsmd');
            }
        }

        // Sanitize and save the fields postal code/zip
        if (isset($_POST['wsmd_postal_zip_code'])) {
            $_POST['wsmd_postal_zip_code'] = sanitize_text_field(wp_unslash($_POST['wsmd_postal_zip_code']));;
            if (!empty($_POST['wsmd_postal_zip_code'])) {
                update_user_meta($user_id, 'wsmd_postal_zip_code', $_POST['wsmd_postal_zip_code']);
            } else {
                $results['wsmd_postal_zip_code'] = __('Postal/Zip code is required', 'wsmd');
            }
        }

        // Sanitize and save the fields country
        if (isset($_POST['wsmd_country'])) {
            $_POST['wsmd_country'] = sanitize_text_field(wp_unslash($_POST['wsmd_country']));
            if (!empty($_POST['wsmd_country'])) {
                update_user_meta($user_id, 'wsmd_country', $_POST['wsmd_country']);
            } else {
                $results['wsmd_country'] = __('Country is required', 'wsmd');
            }
        }

        // Sanitize and save the fields website (optional)
        if (isset($_POST['wsmd_website'])) {
            $_POST['wsmd_website'] = sanitize_text_field(wp_unslash($_POST['wsmd_website']));

            if (empty($_POST['wsmd_website'])) {
                update_user_meta($user_id, 'wsmd_website', '');
            } elseif (filter_var($_POST['wsmd_website'], FILTER_VALIDATE_URL)) {
                // Use regex to keep only the protocol and domain
                preg_match('/^(https?:\/\/[^\/]+)/', $_POST['wsmd_website'], $matches);
                $_POST['wsmd_website'] = $matches[1];
                update_user_meta($user_id, 'wsmd_website', $_POST['wsmd_website']);
            } else {
                $results['wsmd_website'] = __('Invalid Website URL', 'wsmd');
            }
        }

        // Sanitize and save the fields phone (optional)
        if (isset($_POST['wsmd_phone'])) {
            $_POST['wsmd_phone'] = sanitize_text_field(wp_unslash($_POST['wsmd_phone']));
            if (empty($_POST['wsmd_phone'])) {
                update_user_meta($user_id, 'wsmd_phone', '');
            } elseif (preg_match('/^[0-9\-\(\)\/\+\s]*$/', $_POST['wsmd_phone'])) {
                // Replace all characters except numbers with empty string
                $_POST['wsmd_phone'] = preg_replace('/[^0-9]/', '', $_POST['wsmd_phone']);
                update_user_meta($user_id, 'wsmd_phone', $_POST['wsmd_phone']);
            } else {
                $results['wsmd_phone'] = __('Invalid phone number', 'wsmd');
            }
        }

        // Sanitize and save the fields email (optional)
        if (isset($_POST['wsmd_email'])) {
            $_POST['wsmd_email'] = sanitize_text_field(wp_unslash($_POST['wsmd_email']));
            if (empty($_POST['wsmd_email'])) {
                update_user_meta($user_id, 'wsmd_email', '');
            } elseif (filter_var($_POST['wsmd_email'], FILTER_VALIDATE_EMAIL)) {
                update_user_meta($user_id, 'wsmd_email', $_POST['wsmd_email']);
            } else {
                $results['wsmd_email'] = __('Invalid email', 'wsmd');
            }
        }

        // Sanitize and save the selected taxonomy terms (optional)
        if (isset($_POST['wsmd_taxonomies'])) {
            $term_ids = array_map('intval', $_POST['wsmd_taxonomies']);
            $valid_term_ids = self::validate_terms($term_ids, 'wsmd-taxonomy');
            wp_set_object_terms($user_id, $valid_term_ids, 'wsmd-taxonomy', false);
        } else {
            // If no terms are selected, clear the terms
            wp_set_object_terms($user_id, array(), 'wsmd-taxonomy', false);
        }

        return $results;
    }

    /**
     * Validate terms against the taxonomy.
     *
     * @param array $term_ids The term IDs to validate.
     * @param string $taxonomy The taxonomy to validate against.
     * @return array The valid term IDs.
     */
    public static function validate_terms($term_ids, $taxonomy)
    {
        $valid_terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'include' => $term_ids,
            'hide_empty' => false,
        ));

        $valid_term_ids = wp_list_pluck($valid_terms, 'term_id');
        return $valid_term_ids;
    }

    /**
     * Get the user meta for the Member Directory.
     *
     * @param int $user_id The user ID.
     * @param string $key The key of the user meta to retrieve.
     * @return mixed The value of the meta key or an array of all the user meta values.
     */
    public static function get_user_settings($userID, $key = '')
    {
        if (empty($key)) {
            return array(
                'wsmd_is_admin_allowed' => get_user_meta($userID, 'wsmd_is_admin_allowed', true),
                'wsmd_taxonomies' => wp_get_object_terms($userID, 'wsmd-taxonomy', array('fields' => 'ids')),
                'wsmd_geocode' => get_user_meta($userID, 'wsmd_geocode', true),
                'wsmd_occupation' => get_user_meta($userID, 'wsmd_occupation', true),
                'wsmd_company' => get_user_meta($userID, 'wsmd_company', true),
                'wsmd_address' => get_user_meta($userID, 'wsmd_address', true),
                'wsmd_city' => get_user_meta($userID, 'wsmd_city', true),
                'wsmd_province_state' => get_user_meta($userID, 'wsmd_province_state', true),
                'wsmd_postal_zip_code' => get_user_meta($userID, 'wsmd_postal_zip_code', true),
                'wsmd_country' => get_user_meta($userID, 'wsmd_country', true),
                'wsmd_website' => get_user_meta($userID, 'wsmd_website', true),
                'wsmd_phone' => get_user_meta($userID, 'wsmd_phone', true),
                'wsmd_email' => get_user_meta($userID, 'wsmd_email', true),
            );
        } else {
            if ($key === 'wsmd_is_admin_allowed') {
                $is_admin_allowed = get_user_meta($userID, 'wsmd_is_admin_allowed', true);
                return ($is_admin_allowed === '') ? 'default' : $is_admin_allowed;
            } else {
                return get_user_meta($userID, $key, true);
            }
        }
    }
}
