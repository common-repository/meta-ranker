<?php
if (!defined('ABSPATH')) {
    exit;
}

class mrv_posttype
{
    /**
     * Registers our plugin with WordPress.
     */
    public static function register()
    {
        $postTypeCls = new self();

        // register hooks
        add_action('init', array($postTypeCls, 'register_post_type'));
        add_filter('post_row_actions', array($postTypeCls, 'mrv_duplicate_post_link'), 10, 2);

        /*
        * Function creates post duplicate as a draft and redirects then to the edit post screen
        */
        add_action('admin_action_mrv_duplicate_post_as_draft', array($postTypeCls, 'mrv_duplicate_post_as_draft'));

    }

    public function mrv_duplicate_post_as_draft()
    {

        // check if post ID has been provided and action
        if (empty($_GET['post'])) {
            wp_die('No post to duplicate has been provided!');
        }

        // Nonce verification
        if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
            return;
        }

        // Get the original post id
        $post_id = absint(sanitize_text_field($_GET['post']));

        // And all the original post data then
        $post = get_post($post_id);

        /*
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        // if post data exists (I am sure it is, but just in a case), create the post duplicate
        if ($post) {

            // new post data array
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order,
            );

            // insert the post by wp_insert_post() function
            $new_post_id = wp_insert_post($args);

            /*
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies(get_post_type($post)); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            if ($taxonomies) {
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }
            }

            // duplicate all post meta
            $post_meta = get_post_meta($post_id);

            $test_array = [];
            if ($post_meta) {

                foreach ($post_meta as $meta_key => $meta_values) {

                    if ('_wp_old_slug' == $meta_key) { // do nothing for this meta key
                        continue;
                    }

                    foreach ($meta_values as $meta_value) {

                        if (is_serialized($meta_value)) {
                            add_post_meta($new_post_id, $meta_key, unserialize($meta_value));
                        } 
                    }
                }

            }

            // finally, redirect to the edit post screen for the new draft
            // wp_safe_redirect(
            //     add_query_arg(
            //         array(
            //             'action' => 'edit',
            //             'post' => $new_post_id
            //         ),
            //         admin_url( 'post.php' )
            //     )
            // );
            // exit;
            // or we can redirect to all posts with a message
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'post_type' => ('post' !== get_post_type($post) ? get_post_type($post) : false),
                        'saved' => 'post_duplication_created', // just a custom slug here
                    ),
                    admin_url('edit.php')
                )
            );
            exit;

        } else {
            wp_die('Post creation failed, could not find original post.');
        }

    }

    public function mrv_duplicate_post_link($actions, $post)
    {

        if (!current_user_can('edit_posts')|| $this->mrv_get_cpt()!='meta-ranker') {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'mrv_duplicate_post_as_draft',
                    'post' => $post->ID,
                ),
                'admin.php'
            ),
            basename(__FILE__),
            'duplicate_nonce'
        );

        $actions['duplicate'] = '<a href="' . $url . '" title="Duplicate this item" rel="permalink">Duplicate List</a>';

        return $actions;
    }

    /**
     * Create custom post for timeline item's (stories)
     */
    public function register_post_type()
    {

        $labels = array(
            'name' => _x('Meta Ranker', 'Post Type General Name', 'ctbtx'),
            'singular_name' => _x('Meta Ranker', 'Post Type Singular Name', 'ctbtx'),
            'menu_name' => __('Meta Ranker', 'ctbtx'),
            'name_admin_bar' => __('Meta Ranker', 'ctbtx'),
            'archives' => __('Item Archives', 'ctbtx'),
            'attributes' => __('Item Attributes', 'ctbtx'),
            'parent_item_colon' => __('Parent Item:', 'ctbtx'),
            'all_items' => __('All List', 'ctbtx'),
            'add_new_item' => __('Add New', 'ctbtx'),
            'add_new' => __('Add New', 'ctbtx'),
            'new_item' => __('New List', 'ctbtx'),
            'edit_item' => __('Edit List', 'ctbtx'),
            'update_item' => __('Update List', 'ctbtx'),
            'view_item' => __('View List', 'ctbtx'),
            'view_items' => __('View List', 'ctbtx'),
            'search_items' => __('Search List', 'ctbtx'),
            'not_found' => __('Not found', 'ctbtx'),
            'not_found_in_trash' => __('Not found in Trash', 'ctbtx'),
            'featured_image' => __('Featured Image', 'ctbtx'),
            'set_featured_image' => __('Set featured image', 'ctbtx'),
            'remove_featured_image' => __('Remove featured image', 'ctbtx'),
            'use_featured_image' => __('Use as featured image', 'ctbtx'),
            'insert_into_item' => __('Insert into List', 'ctbtx'),
            'uploaded_to_this_item' => __('Uploaded to this List', 'ctbtx'),
            'items_list' => __('List list', 'ctbtx'),
            'items_list_navigation' => __('List list navigation', 'ctbtx'),
            'filter_items_list' => __('Filter List list', 'ctbtx'),
        );
        $args = array(
            'label' => __('Meta Ranker', 'ctbtx'),
            'description' => __('Post Type Description', 'ctbtx'),
            'labels' => $labels,
            'supports' => array('title'),
            'taxonomies' => array(''),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 15,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => false,
            'show_in_rest' => true,
            'publicly_queryable' => true,
            'menu_icon' => 'dashicons-sort',
            'capability_type' => 'post',
        );
        register_post_type('meta-ranker', $args);
        flush_rewrite_rules();
    }

        public function mrv_get_cpt()
    {
        global $post, $typenow, $current_screen;

        if ($post && $post->post_type) {
            return $post->post_type;
        } elseif ($typenow) {
            return $typenow;
        } elseif ($current_screen && $current_screen->post_type) {
            return $current_screen->post_type;
        } elseif (isset($_REQUEST['page'])) {
            return sanitize_key($_REQUEST['page']);
        } elseif (isset($_REQUEST['post_type'])) {
            return sanitize_key($_REQUEST['post_type']);
        } elseif (isset($_REQUEST['post'])) {
            return get_post_type(filter_var($_REQUEST['post'], FILTER_SANITIZE_STRING));
        }
        return null;
    }

}
mrv_posttype::register();
