<?php

// Add ajax action for logged in and non-logged in users
add_action('wp_ajax_get_permalink', 'my_ajax_permalink');
add_action('wp_ajax_nopriv_get_permalink', 'my_ajax_permalink');

function my_ajax_permalink()
{
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        $permalink = get_permalink($post_id);
        echo $permalink;
    }
    wp_die();
}

//Add all constant messages
function mrv_const_messages()
{
    $messages = "";

    $messages = array(
        'metamask_wallet' => __(" MetaMask Wallet", "mrv"),
        // 'trust_wallet' => __("Trust Wallet", "mrv"),
        'binance_wallet' => __("Binance Wallet", "mrv"),
        'wallet_connect' => __(" Wallet Connect", "mrv"),
        'click_here' => __("Click Here", "mrv"),
        'extention_not_detected' => __("extention not detected", "mrv"),
        'connection_establish' => __("Please wait while connecting...", "mrv"),
        'user_rejected_the_request' => __("User rejected the request", "mrv"),
        "infura_msg" => __("Infura project id is required for WalletConnect to work", "mrv"),

    );
    return $messages;

}
function mrv_skip_wallet()
{
    error_log("inside " . __FUNCTION__);
    global $wpdb;

    $vote_type = !empty($_REQUEST['vote_type']) ? sanitize_text_field($_REQUEST['vote_type']) : '';
    $ListID = !empty($_REQUEST['ListID']) ? sanitize_text_field($_REQUEST['ListID']) : '';
    $ItemName = !empty($_REQUEST['ItemName']) ? sanitize_text_field($_REQUEST['ItemName']) : '';
    $metaSessionId = $_POST['metaSessionId'];
    $current_url = !empty($_REQUEST['current_url']) ? sanitize_text_field($_REQUEST['current_url']) : '';
    $list_name = get_the_title($ListID);
    $ip = mrv_get_ip();



    $wallet_data = $wpdb->get_results(sprintf("SELECT * FROM meta_wallet_connections WHERE id='%s';", $metaSessionId));
    if (!$wallet_data) {
        $response = array(
            'status' => 'error',
            'data' => "Skip wallet Data not found",
        );
        wp_send_json($response);
    }

    $session_table = $wallet_data[0]->session_table;
    $session_id = $wallet_data[0]->session_id;
    $ticker = $wallet_data[0]->ticker;
    $wallet_address = $wallet_data[0]->wallet_address;


    $session_data = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE id='%s';", $session_table, $session_id));
    if (empty($session_data)) {
        $response = array(
            'status' => 'error',
            'data' => "Session data not found",
        );
        wp_send_json($response);
    }

    $ip = $session_data[0]->ip;
    $agent = $session_data[0]->agent;
    $link = $session_data[0]->link;
    $wallet_type = $session_data[0]->wallet_type;
    $balance = $session_data[0]->balance;
    $wallet_address = $session_data[0]->wallet_address;


    $db = new MRV_Database();
    $user_id = $db->check_alredy_voted_list($ListID, $wallet_address);

    if (!empty($user_id)) {
        $voted_id = strtolower('mrv_voted_' . $ListID . $wallet_address);
        $vote_res = get_post_meta($ListID, $voted_id, true);

        if (!empty($vote_res)) {
            $old_vote_type = !empty($vote_res['type']) ? $vote_res['type'] : "";

            $logs = array();
            $logs['list_name'] = get_the_title($ListID);
            $logs['list_item'] = $ItemName;
            $logs['list_id'] = $ListID;
            $logs['ip'] = $ip;
            $logs['wallet_address'] = $wallet_address;
            $logs['vote_type'] = $vote_type;
            $logs['wallet_type'] = $wallet_type;
            $logs['link'] = $current_url;
            $logs['agent'] = mrv_get_the_browser();

            $response = mrv_update_votes($logs, $old_vote_type, $vote_res, $voted_id);
            wp_send_json($response);

        }
    }

    $auth_token = MetaRankerRestApi::getAuthToken('meta-ranker');
    error_log('wallet_address: ' . $wallet_address);
    $resp = MetaRankerRestApi::request('/v3/data/wallet-skip', 'PUT', [
        'wallet' => $wallet_address,
        'ticker' => $ticker,
        'balance' => $balance,
        'data' => [
            [
                'key' => 'ipAddress',
                'value' => $ip,
            ],
            [
                'key' => 'userAgent',
                'value' => $agent,
            ],
            [
                'key' => 'walletType',
                'value' => $wallet_type,
            ],
            [
                'key' => 'articleUrl',
                'value' => $link,
            ],
            [
                'key' => 'listName',
                'value' => $list_name,
            ],
        ],
        'signature' => "signature",
    ], $auth_token);
    error_log(print_r($resp, true));
    // if (201 !== $resp['status']) {
    //     $response = array(
    //         'status' => 'error',
    //         'data' => "Failed to connect to server",
    //     );
    // } else {
    $vote_res = mrv_vote($wallet_address, $balance, $ticker, $vote_type, $ListID, $ItemName, $wallet_type, $current_url);
    $all_list_ids = mrv_get_list_item_ids($ListID);
    $response = array(
        'status' => 'success',
        'data' => array('votes' => $vote_res, 'id' => $all_list_ids),
    );
    // }
    wp_send_json($response);
}
function mrv_save_votes()
{

    $nonce = !empty($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'mrv_votes_ranker')) {
        die('*ok*');
    }
    $sender_account = !empty($_REQUEST['sender_account']) ? sanitize_text_field($_REQUEST['sender_account']) : '';
    $vote_type = !empty($_REQUEST['vote_type']) ? sanitize_text_field($_REQUEST['vote_type']) : '';
    $ListID = !empty($_REQUEST['ListID']) ? sanitize_text_field($_REQUEST['ListID']) : '';
    $ItemName = !empty($_REQUEST['ItemName']) ? sanitize_text_field($_REQUEST['ItemName']) : '';
    $wallet_type = !empty($_REQUEST['wallet_type']) ? sanitize_text_field($_REQUEST['wallet_type']) : '';
    $balance = !empty($_REQUEST['balance']) ? sanitize_text_field($_REQUEST['balance']) : '';
    $current_url = !empty($_REQUEST['current_url']) ? sanitize_text_field($_REQUEST['current_url']) : '';
    $user_sign = !empty($_REQUEST['user_sign']) ? sanitize_text_field($_REQUEST['user_sign']) : '';

    $list_name = get_the_title($ListID);
    $user_agent = mrv_get_the_browser();
    $ip = mrv_get_ip();
    $ticker = sanitize_text_field($_POST['ticker']);

    $auth_token = MetaRankerRestApi::getAuthToken('meta-ranker');

    $resp = MetaRankerRestApi::request('/v3/data', 'PUT', [
        'wallet' => $sender_account,
        'ticker' => $ticker,
        'balance' => floatval($balance),
        'data' => [
            [
                'key' => 'ipAddress',
                'value' => $ip,
            ],
            [
                'key' => 'userAgent',
                'value' => $user_agent,
            ],
            [
                'key' => 'walletType',
                'value' => $wallet_type,
            ],
            [
                'key' => 'articleUrl',
                'value' => $current_url,
            ],
            [
                'key' => 'listName',
                'value' => $list_name,
            ],
        ],
        'signature' => $user_sign,
    ], $auth_token);
    if (201 !== $resp['status']) {
        exit(json_encode([
            'success' => false,
            'message' => __('Failed to connect to age server. Please try again!', 'meta-ranker')
        ]));
    } else {
        $vote_res = mrv_vote($sender_account, $balance, $ticker, $vote_type, $ListID, $ItemName, $wallet_type, $current_url);
        $all_list_ids = mrv_get_list_item_ids($ListID);
        $response = array(
            'status' => 'success',
            'data' => array('updated' => 'inserted', 'votes' => $vote_res, 'id' => $all_list_ids),
        );
    }
    wp_send_json($response);

}

function mrv_generate_nonce()
{

    $address = sanitize_text_field($_POST['sender_account']);
    $auth_token = MetaRankerRestApi::getAuthToken('meta-ranker');
    $ticker = sanitize_text_field($_POST['ticker']);
    $resp = MetaRankerRestApi::request("/v2/wallet-auth/nonce?address=$address&ticker=$ticker", 'GET', [], $auth_token);
    if ($resp) {
        $response = [
            'success' => true,
            'nonce' => json_decode($resp['body'])->nonce
        ];

        exit(json_encode($response));
    }
}

function mrv_update_votes($logs, $old_vote_type, $vote_res, $voted_id)
{
    $db = new MRV_Database();
    $vote_type = $logs['vote_type'];
    $ListID = $logs['list_id'];
    $ItemName = $logs['list_item'];
    $updated_votes = "";
    if ($vote_type == "upvote") {
        if ($old_vote_type == "upvote") {
            $old_item = $vote_res['item_name'];
            $old_total_vote = get_post_meta($ListID, 'mrv_total_votes' . $old_item, true);
            $old_up_vote = get_post_meta($ListID, 'mrv_up_votes' . $old_item, true);
            $old_up_vote = (int) $old_up_vote - 1;
            $old_total_vote = (int) $old_total_vote - 1;
            update_post_meta($ListID, 'mrv_up_votes' . $old_item, $old_up_vote);
            update_post_meta($ListID, 'mrv_total_votes' . $old_item, $old_total_vote);

        } else {
            $old_item = $vote_res['item_name'];
            $old_total_vote = get_post_meta($ListID, 'mrv_total_votes' . $old_item, true);
            $old_up_vote = get_post_meta($ListID, 'mrv_up_votes' . $old_item, true);
            $old_up_vote = (int) $old_up_vote + 1;
            $old_total_vote = (int) $old_total_vote + 1;
            update_post_meta($ListID, 'mrv_up_votes' . $old_item, $old_up_vote);
            update_post_meta($ListID, 'mrv_total_votes' . $old_item, $old_total_vote);

        }
        $total_vote = get_post_meta($ListID, 'mrv_total_votes' . $ItemName, true);
        $up_vote = get_post_meta($ListID, 'mrv_up_votes' . $ItemName, true);
        $up_vote = (int) $up_vote + 1;
        $total_vote = (int) $total_vote + 1;
        update_post_meta($ListID, 'mrv_up_votes' . $ItemName, $up_vote);
        update_post_meta($ListID, 'mrv_total_votes' . $ItemName, $total_vote);
        $user_vote = array('type' => $vote_type, 'item_name' => $ItemName);
        update_post_meta($ListID, $voted_id, $user_vote);
        $updated_votes = $total_vote;

    } else {
        if ($old_vote_type == "upvote") {
            $old_item = $vote_res['item_name'];
            $old_total_vote = get_post_meta($ListID, 'mrv_total_votes' . $old_item, true);
            $old_up_vote = get_post_meta($ListID, 'mrv_up_votes' . $old_item, true);
            $old_up_vote = (int) $old_up_vote - 1;
            $old_total_vote = (int) $old_total_vote - 1;
            update_post_meta($ListID, 'mrv_up_votes' . $old_item, $old_up_vote);
            update_post_meta($ListID, 'mrv_total_votes' . $old_item, $old_total_vote);

        } else {
            $old_item = $vote_res['item_name'];
            $old_total_vote = get_post_meta($ListID, 'mrv_total_votes' . $old_item, true);
            $old_up_vote = get_post_meta($ListID, 'mrv_up_votes' . $old_item, true);
            $old_up_vote = (int) $old_up_vote + 1;
            $old_total_vote = (int) $old_total_vote + 1;
            update_post_meta($ListID, 'mrv_up_votes' . $old_item, $old_up_vote);
            update_post_meta($ListID, 'mrv_total_votes' . $old_item, $old_total_vote);

        }
        $total_vote = get_post_meta($ListID, 'mrv_total_votes' . $ItemName, true);
        $down_vote = get_post_meta($ListID, 'mrv_down_votes' . $ItemName, true);

        $down_vote = (int) $down_vote - 1;
        $total_vote = (int) $total_vote - 1;
        update_post_meta($ListID, 'mrv_down_votes' . $ItemName, $down_vote);
        update_post_meta($ListID, 'mrv_total_votes' . $ItemName, $total_vote);
        $user_vote = array('type' => $vote_type, 'item_name' => $ItemName);
        update_post_meta($ListID, $voted_id, $user_vote);
        $updated_votes = $total_vote;
    }

    $logs['list_name'] = get_the_title($ListID);
    $logs['list_item'] = $ItemName;
    $logs['list_id'] = $ListID;
    $logs['vote_type'] = $vote_type;
    $db->insert($logs);
    $all_list_ids = mrv_get_list_item_ids($ListID);

    $response = array(
        'status' => 'success',
        'data' => array('updated' => 'updated', 'votes' => $updated_votes, 'id' => $all_list_ids),
    );
    return $response;

}

// Check user already voted
function mrv_check_voted_alredy()
{

    $nonce = !empty($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'mrv_votes_ranker')) {
        die('*ok*');
    }
    $db = new MRV_Database();
    $wallet_type = !empty($_REQUEST['wallet_type']) ? sanitize_text_field($_REQUEST['wallet_type']) : '';
    $current_url = !empty($_REQUEST['current_url']) ? sanitize_text_field($_REQUEST['current_url']) : '';
    $ListID = !empty($_REQUEST['ListID']) ? sanitize_text_field($_REQUEST['ListID']) : '';
    $ItemName = !empty($_REQUEST['ItemName']) ? sanitize_text_field($_REQUEST['ItemName']) : '';
    $sender_account = !empty($_REQUEST['sender_account']) ? sanitize_text_field($_REQUEST['sender_account']) : '';
    $balance = !empty($_REQUEST['balance']) ? sanitize_text_field($_REQUEST['balance']) : '';
    $vote_type = !empty($_REQUEST['vote_type']) ? sanitize_text_field($_REQUEST['vote_type']) : '';
    $ip = mrv_get_ip();
    // $user_id = get_post_meta($ListID, 'mrv_voter_id_' . $ListID.$ip, true);
    $user_id = $db->check_alredy_voted_list($ListID, $sender_account);

    if (empty($user_id)) {
        $response = array(
            'status' => 'success',
            'data' => array('updated' => 'false', 'user_id' => $user_id),
        );
        wp_send_json($response);
    }
    $voted_id = strtolower('mrv_voted_' . $ListID . $sender_account);
    $vote_res = get_post_meta($ListID, $voted_id, true);
    //var_dump($vote_res);

    if (!empty($vote_res)) {
        $old_vote_type = !empty($vote_res['type']) ? $vote_res['type'] : "";

        $logs = array();
        $logs['list_name'] = get_the_title($ListID);
        $logs['list_item'] = $ItemName;
        $logs['list_id'] = $ListID;
        $logs['ip'] = $ip;
        $logs['wallet_address'] = $sender_account;
        $logs['balance'] = $balance;
        $logs['vote_type'] = $vote_type;
        $logs['wallet_type'] = $wallet_type;
        $logs['link'] = $current_url;
        $logs['agent'] = mrv_get_the_browser();

        $response = mrv_update_votes($logs, $old_vote_type, $vote_res, $voted_id);
        wp_send_json($response);

    }
    $response = array(
        'status' => 'error',
        'data' => 'error',
    );
    wp_send_json($response);

}

// Vote function
function mrv_vote($sender_account, $balance, $ticker, $vote_type, $ListID, $ItemName, $wallet_type, $current_url)
{
    $result = [];
    $db = new MRV_Database();
    global $wpdb;
    $result['post_id'] = $ListID;
    $result['user_id'] = get_current_user_id();
    $ip = mrv_get_ip();
    $voted_id = strtolower('mrv_voted_' . $ListID . $sender_account);
    $user_id = $db->check_alredy_voted_list($ListID, $sender_account);
    if (!empty($user_id)) {
        return 'updated';
    }

    $total_vote = get_post_meta($result['post_id'], 'mrv_total_votes' . $ItemName, true);
    $up_vote = get_post_meta($result['post_id'], 'mrv_up_votes' . $ItemName, true);
    $down_vote = get_post_meta($result['post_id'], 'mrv_down_votes' . $ItemName, true);

    $logs = array();
    $logs['list_name'] = get_the_title($ListID);
    $logs['list_item'] = $ItemName;
    $logs['list_id'] = $ListID;
    $logs['ip '] = $ip;
    $logs['wallet_address'] = $sender_account;
    $logs['vote_type'] = $vote_type;
    $logs['wallet_type'] = $wallet_type;
    $logs['balance'] = $balance;
    $logs['link'] = $current_url;
    $logs['agent'] = mrv_get_the_browser();



    if ($vote_type == "upvote") {
        $up_vote = (int) $up_vote + 1;
        $total_vote = (int) $total_vote + 1;
        update_post_meta($result['post_id'], 'mrv_up_votes' . $ItemName, $up_vote);
        update_post_meta($result['post_id'], 'mrv_total_votes' . $ItemName, $total_vote);
        $user_vote = array('type' => $vote_type, 'item_name' => $ItemName);
        update_post_meta($ListID, $voted_id, $user_vote);

        $logs['up_vote'] = $up_vote;
        $logs['down_vote'] = $down_vote;
        $logs['total_vote'] = $total_vote;
        $logs['data_status'] = "send";


        $insert_result = $db->insert($logs);
        if ($insert_result["success"]) {
            $last_id = $insert_result["data"];
            $inserted = $wpdb->insert(
                "meta_wallet_connections",
                array(
                    'plugin_name' => RANKER_PLUGIN,
                    'session_table' => RANKER_TABLE,
                    'session_id' => $last_id,
                    'wallet_type' => $wallet_type,
                    'ticker' => $ticker,
                    'wallet_address' => $sender_account
                )
            );


            $mrv_wallet_id = $wpdb->insert_id;
            if ($mrv_wallet_id) {
                setcookie(
                    'metaSessionId',
                    $mrv_wallet_id,
                    array(
                        'path' => '/',
                        'secure' => is_ssl(),
                        'expires' => time() + 86400,
                        'httponly' => false,
                        'samesite' => 'Strict'
                    )
                );
            }
        }

        return $total_vote;
    } else {
        $down_vote = (int) $down_vote - 1;
        $total_vote = (int) $total_vote - 1;
        update_post_meta($result['post_id'], 'mrv_down_votes' . $ItemName, $down_vote);
        update_post_meta($result['post_id'], 'mrv_total_votes' . $ItemName, $total_vote);
        $user_vote = array('type' => $vote_type, 'item_name' => $ItemName);
        update_post_meta($ListID, $voted_id, $user_vote);
        $logs['up_vote'] = $up_vote;
        $logs['down_vote'] = $down_vote;
        $logs['total_vote'] = $total_vote;
        $db->insert($logs);

        return $total_vote;
    }

}

function mrv_get_ip()
{
    // Equally untrustworthy.
    $ip = "";
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

        //check ip from share internet

        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);

    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        //to check ip is pass from proxy

        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);

    } else {

        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);

    }
    return $ip;

}

/**
 * Create HTML for item media ( image / youtube / slider )
 */
function mrv_item_media($item)
{

    $html = null;
    if (!empty($item['item-media-type']) && $item['item-media-type'] == 'image' && !empty($item['item-media-image']['url'])) {
        $alt_text = !empty($item['item-media-image-alt']) ? $item['item-media-image-alt'] : $item['item-title'];
        $image_size = !empty($item['item-image-size']) ? $item['item-image-size'] : 'large';
        $image_size_class = $image_size;
        if ($image_size == 'custom_size') {
            $width = $item['item-image-custom-size']['width'];
            $height = $item['item-image-custom-size']['height'];
            $image_size = array($width, $height);
            $image_size_class = 'custom';
        }
        $html .= '<div class="mrv-media ' . esc_attr($image_size_class) . '">';

        $html .= wp_get_attachment_image($item['item-media-image']['id'], $image_size, false, array('class' => 'mrv-media ' . esc_attr($image_size_class), 'alt' => $alt_text));

        $html .= '</div>';
    } elseif ($item['item-media-type'] == 'youtube' && !empty($item['item-media-youtube'])) {

        $video = $item['item-media-youtube'];
        // convert youtube video link for iframe
        if (strpos($video, 'youtube') > 0 || strpos($video, 'youtu.be') > 0) {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $matches);

            if (isset($matches[1])) {
                $id = $matches[1];
                $html = '<div class="mrv-media"><iframe width="100%"
					src="https://www.youtube.com/embed/' . $id . '"
					frameborder="0" allowfullscreen></iframe></div>';
            }
        } else {
            $html = __("Wrong URL", "twae");
        }

    }

    return $html;
}

function mrv_get_list_item_ids($listID)
{
    $options = get_post_meta($listID, 'mrv_post_settinga', true);
    $items = !empty($options['mrv-item']) ? $options['mrv-item'] : '';
    $ids = [];
    if (is_array($items)) {
        foreach ($items as $key => $value) {

            $itemtitle = $value['item-multiple-settings']['item-title'];
            $uniq_id = mrv_clean($itemtitle);
            $ids[] = array('ids' => $uniq_id . $listID, 'votes' => get_post_meta($listID, 'mrv_total_votes' . $uniq_id, true));
        }
    }

    return $ids;
}

function mrv_clean($string)
{
    $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
//About started page callback function

function mrv_about_page()
{
    ?>
    <p>Create polls, make any list in your posts votable - images, videos, whole paragraphs, even bullet points!.
        Make your content more interactive and maximize user engagement and retention.
        Restrict misuse using Crypto Wallet Connection</p>

    <div class="csf-welcome-cols">


        <div class="csf--col csf--col-first">
            <span class="mrv-image-wrap"><img src="<?php echo esc_url(MRV_URL . 'assets/images/meta-auth.jpg') ?>"></span>
            <div class="mrv-wrap-cont">
                <div class="mrv-title">Meta Auth</div>
                <p class="mrv-desc">2-Factor authentication is a tedious process that can be simplified using the
                    functionality of signed messages with a private key with an overall flow …</p>
            </div>
            <button class="button button-secondary cpmwp_modal-toggle"><a
                    href="<?php echo esc_url(admin_url() . 'plugin-install.php?tab=plugin-information&amp;plugin=meta-auth'); ?>"
                    targer="_blank">Install Plugin</a></button>

        </div>
        <div class="csf--col csf--col-first">
            <span class="mrv-image-wrap"><img src="<?php echo esc_url(MRV_URL . 'assets/images/meta-age.jpg') ?>"></span>
            <div class="mrv-wrap-cont">
                <div class="mrv-title">Meta Age</div>
                <p class="mrv-desc">A better way to restrict crypto users by their age with blockchain and browser wallets.
                </p>
            </div>
            <button class="button button-secondary cpmwp_modal-toggle"><a
                    href="<?php echo esc_url(admin_url() . 'plugin-install.php?tab=plugin-information&amp;plugin=meta-age'); ?>"
                    targer="_blank">Install Plugin</a></button>

        </div>

        <div class="csf--col csf--col-first csf--last">
            <span class="mrv-image-wrap"><img src="<?php echo esc_url(MRV_URL . 'assets/images/meta-locker.jpg') ?>"></span>
            <div class="mrv-wrap-cont">
                <div class="mrv-title">Meta locker</a></div>
                <p class="mrv-desc">A content locker WordPress plugin. Users are required to connect their crypto wallets to
                    view the locked content.</p>
            </div>

            <button class="button button-secondary cpmwp_modal-toggle"><a
                    href="<?php echo esc_url(admin_url() . 'plugin-install.php?tab=plugin-information&amp;plugin=meta-locker'); ?>"
                    targer="_blank">Install Plugin</a></button>

        </div>

        <div class="clear"></div>



        <div class="clear"></div>




        <div class="clear"></div>

    </div>

    <hr />
    <?php
}

//Terms page callback function
function mrv_terms_of_use()
{
    ?>

    <div class="wrap metaranker-tou-page">
        <h1 style="font-size:24px;text-transform:uppercase;font-weight:700">
            <?= __('Terms and Conditions of Use', 'meta-ranker'); ?>
        </h1>
        <p>These Terms and Conditions (the “<strong>Agreement</strong>”) govern your use of MetaRanker (the "Plugin")
            developed and provided by
            AdAstra ("<strong>Company</strong>," "<strong>we</strong>," "<strong>us</strong>," or "<strong>our</strong>").
            By using the Plugin, you agree to abide by this Agreement. Please
            read this Agreement carefully before using the Plugin.</p>

        <h2>1. Acceptance of Terms</h2>

        <p>By installing, activating, or using the Plugin, you acknowledge and agree to comply with these Terms and
            Conditions. If you do not agree with these terms, please do not use the Plugin.</p>

        <h2>2. Data Collection and Sale</h2>

        <p>The Plugin collects certain data (including personal data) from users who interact with your website, including
            but not limited to:</p>

        <ol>
            <li>IP address</li>
            <li>Device Identifiers</li>
            <li>Cryptowallet addresses</li>
        </ol>

        <p>together, the "<strong>data</strong>".</p>

        <p>The data collected will be used for the following purposes:</p>

        <ol>
            <li>Improving user experience</li>
            <li>Potential onward sale to other clients.</li>
        </ol>

        <p>For more details, please refer to our Privacy Notice.</p>

        <h2>3. Consent to Data Collection and Sale</h2>

        <p>By using the Plugin, you confirm that you have obtained all necessary consents from your website visitors for the
            collection, processing, and sale of their data (including personal data) as described in this Agreement. You
            agree to provide a clear and transparent privacy notice on your website that explains the data collection,
            usage, disclosure, and sale practices, and shall ensure that you have all the necessary permissions to allow us
            to use the data as set out in this Agreement.</p>

        <p>No payment is due from us to you or vice versa. This is because the parties recognize that there are benefits to
            both of using the Plugin and enabling the sharing of the data.</p>

        <h2>4. Data Security</h2>

        <p>We take reasonable measures to protect the data collected through the Plugin. However, we cannot guarantee the
            security of the data transmitted over the internet. You agree that you use the Plugin and collect data at your
            own risk.</p>

        <h2>5. Disclosure and Sale of Data</h2>

        <p>You may choose to sell the collected data to third-party organizations for their commercial use. We may also
            share the collected data with third-party service providers who assist us in providing and improving the
            Plugin's functionality. We may share aggregated and anonymized data for analytical and marketing purposes. You
            acknowledge that the sale of data is subject to applicable laws and regulations.</p>

        <h2>6. Your Responsibilities</h2>

        <p>You are responsible for:</p>

        <ol>
            <li>Ensuring compliance with all applicable privacy laws and regulations, including (but not limited to) the
                EU/UK General Data Protection Regulation (GDPR)</li>
            <li>Obtaining consent from users for data collection, usage, and sale</li>
            <li>Maintaining an up-to-date privacy notice on your website</li>
            <li>Addressing user inquiries and requests regarding their data</li>
        </ol>

        <p>You confirm to us that you are the owner of the data or are otherwise legally entitled to authorize us to use the
            data as set out in this Agreement.</p>

        <h2>7. Termination</h2>

        <p>We reserve the right to suspend or terminate your access to the Plugin at any time if you violate this Agreement.
        </p>

        <h2>8. Changes to Terms</h2>

        <p>We may update this Agreement from time to time. Any changes will be effective upon posting on our website or
            through the Plugin. Your continued use of the Plugin after such changes constitutes your acceptance of the
            updated Agreement.</p>

        <h2>9. Limitation of Liability</h2>

        <p>To the extent permitted by law, we shall not be liable for any indirect, consequential, incidental, or special
            damages arising out of or in connection with the use of the Plugin or the data collected.</p>

        <h2>10. Governing Law</h2>

        <p>This Agreement shall be governed by and construed in accordance with the laws of England and Wales. Any disputes
            arising from this Agreement shall be subject to the exclusive jurisdiction of the courts in England and Wales.
        </p>

        <p>If you have any questions or concerns, please contact us at <a
                href="mailto:info@adastracrypto.com">info@adastracrypto.com</a>.</p>
    </div>
    <?php

}

// function cptbx_set_default_values($field) {
//     if (isset($_GET['item-default-options'])) {
//         $selected_option = $_GET['item-default-options'];

//         if ($selected_option == 'option_a') {
//             if ($field['id'] == 'item-title') {
//                 return 'Default Title for Option A';
//             } elseif ($field['id'] == 'item-desc') {
//                 return 'Default Description for Option A';
//             }
//         } elseif ($selected_option == 'option_b') {
//             if ($field['id'] == 'item-title') {
//                 return 'Default Title for Option B';
//             } elseif ($field['id'] == 'item-desc') {
//                 return 'Default Description for Option B';
//             }
//         }
//     }
//     return '';
// }
// add_filter('csf_default_value', 'cptbx_set_default_values', 10, 1);


function mrv_get_the_browser()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
        return 'Internet explorer';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false) {
        return 'Internet explorer';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
        return 'Mozilla Firefox';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) {
        return 'Google Chrome';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false) {
        return 'Opera Mini';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
        return 'Opera';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
        return 'Safari';
    } else {
        return 'Other';
    }

}