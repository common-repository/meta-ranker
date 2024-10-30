<?php

/**
 * Handle AJAX activation request
 */
function metaranker_activate_site()
{

    
        

    if (empty($_POST['wallet']) || empty($_POST['plugin'])) {

        exit(json_encode([
            'success' => false,
            'message' => __('Please enter your email address!', 'meta-ranker'),
        ]));
    }
    MetaRankerRestApi::setupKeypair();
  
    $email = get_option('admin_email');
    $wallet = sanitize_text_field($_POST['wallet']);
    $plugin = sanitize_title($_POST['plugin']);
    $ticker = sanitize_text_field($_POST['ticker']);
    $status = MetaRankerRestApi::getActivationStatus($plugin);

    if (!$status) {
        $status = MetaRankerRestApi::registerSite($plugin, $email, $wallet, $ticker);
        sleep(1);
        if ($status) {
            if ($status === 'registered') {
                update_option('meta_age_mail_sent', 'yes');
                exit(json_encode([
                    'success' => true,
                    'message' => __('The plugin has been activated successfully!', 'meta-ranker'),
                ]));
            }
        } else {
            exit(json_encode([
                'success' => false,
                'message' => __('Failed to activate the plugin. Please try again!', 'meta-ranker'),
            ]));
        }
    } else {
        if ($status === 'registered') {

            exit(json_encode([
                'success' => true,
                'message' => __('The plugin has been activated successfully!', 'meta-ranker'),
            ]));
        }
  
    }
}
add_action('wp_ajax_metaranker_activate_site', 'metaranker_activate_site');
