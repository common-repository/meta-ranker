<?php
if (!defined('ABSPATH')) {
    exit;
}

class MrvShortcodes
{

    public function __construct()
    {

        add_shortcode('meta-ranker', array($this, 'mrv_generate_sortcode'));

    }

    public function mrv_generate_sortcode($attr, $content = null)
    {
        $attr = shortcode_atts(
            array(
                'id' => null,
            ),
            $attr,
            'meta-ranker'
        );

        $meta_rankerID = (isset($attr['id']) && !empty($attr['id'])) ? $attr['id'] : null;
        $status = get_post_status($meta_rankerID);

        // make sure it is not null
        if ($meta_rankerID == null) {
            return __('An id of a Meta Ranker is missing', 'mrv');
        }

        // make sure an id of meta-ranker has been passed
        if (get_post_type($meta_rankerID) != 'meta-ranker') {
            return __('The id of a non Meta Ranker post has passed.', 'mrv');
        }

        // Preventing `draft` or `pending` post from render will also stop preview feature
        if ($status == 'trash') {
            return printf(__('This Meta Ranker (id: %d) has been trashed! Restore to view this Meta Ranker', 'mrv'), $meta_rankerID);
        }

        /** DO NOT LOAD ANY CSS / JS BEFORE THIS POINT */





        $WidgetID = 'meta-ranker-' . $meta_rankerID;
        $options = get_post_meta($meta_rankerID, 'mrv_post_settinga', true);
        $nonce = wp_create_nonce('mrv_votes_ranker');
        $quick_setup_settings = get_option('mrv_quick_setup');
        $infura_proeject_id = (isset($quick_setup_settings['mrv-infura-key']) && !empty($quick_setup_settings['mrv-infura-key'])) ? $quick_setup_settings['mrv-infura-key'] : "";
        $wallets_enable = (isset($quick_setup_settings['supported_wallets']) && !empty($quick_setup_settings['supported_wallets'])) ? $quick_setup_settings['supported_wallets'] : "";
        wp_register_style('meta_ranker_dashicons', MRV_URL . '/assets/css/meta-ranker.css');
        wp_enqueue_style('meta_ranker_dashicons');
        wp_enqueue_style('mrv-votes', MRV_URL . '/assets/css/mrv-votes.css', array(), MRV_VERSION);
        // wp_enqueue_style('dashicons');

        wp_enqueue_script('mrv-ether-js', MRV_URL . '/assets/js/ethers-5.2.umd.min.js', array('jquery'), MRV_VERSION);
        wp_enqueue_script('mrv-sweet-alert', MRV_URL . '/assets/js/sweetalert2.js', array('jquery'), MRV_VERSION);
        wp_enqueue_script('mrv-walletconnect', MRV_URL . '/assets/js/walletconnect.js', array('jquery'), MRV_VERSION);
        wp_enqueue_script('mrv-extention-handler', MRV_URL . '/assets/js/mrv-extention-handler.js', array('jquery'), MRV_VERSION);
        $symbols = require MRV_PATH . 'assets/symbols.php';
        $testnets = require MRV_PATH . 'assets/testnets.php';
        wp_localize_script('mrv-extention-handler', 'networkInfo', array('symbols' => $symbols, 'testnets' => $testnets)); // Localize the first script
        wp_localize_script(
            'mrv-extention-handler',
            "wallets_data",
            array(
                'url' => MRV_URL,
                "infura_id" => $infura_proeject_id,
                "ajax" => home_url('/wp-admin/admin-ajax.php'),
                "nonce" => $nonce,
                "current_url" => $this->mrv_get_current_url(),
                "wallets_enable" => $wallets_enable,
                "const_msg" => mrv_const_messages(),
                "wallet_logos" => array('metamask_wallet' => MRV_URL . 'assets/images/metamask.png', 'Binance_wallet' => MRV_URL . 'assets/images/binancewallet.png', 'wallet_connect' => MRV_URL . 'assets/images/walletconnect.png')
            )
        );

        // Get the original post ID
        $post_id = absint(sanitize_text_field($_GET['post']));

        // Get the original post data
        $post = get_post($post_id);
        if ($post) {
            $name = $post->post_title;
        }
        // Find the meta-ranker post ID within the shortcode content
        if (preg_match('/\[meta-ranker\s+id="(\d+)"\]/', $post->post_content, $matches)) {
            $meta_ranker_post_id = absint($matches[1]);

            // Get the meta-ranker post data
            $meta_ranker_post = get_post($meta_ranker_post_id);

            if ($meta_ranker_post) {
                // Get the meta-ranker post name
                $name = $meta_ranker_post->post_title;
            }
        }

        // $list_name = $this->mrv_get_current_url(); 
        // $parts = explode("/", $list_name);
        // $name = $parts[4];

        $items = !empty($options['mrv-item']) ? $options['mrv-item'] : '';

        $vote_type = !empty($options['vote_settings']['mrv-vote-type']) ? $options['vote_settings']['mrv-vote-type'] : '';
        $up_icon = "";
        $down_icon = "";

        $html = '';
        $upvote_html = "";
        $downvote_html = "";
        $show_votes = (!empty($options['vote_settings']['mrv-show-votes']) && $options['vote_settings']['mrv-show-votes'] == "1") ? $options['vote_settings']['mrv-show-votes'] : '';
        $show_after_vote = (!empty($options['vote_settings']['mrv-show-votes-after-vote']) && $options['vote_settings']['mrv-show-votes-after-vote'] == "1") ? $options['vote_settings']['mrv-show-votes-after-vote'] : '';
        $icon_postion = (!empty($options['vote_settings']['mrvt-icon-position'])) ? $options['vote_settings']['mrvt-icon-position'] : '';
        $count_position = (!empty($options['vote_settings']['mrvt-vote-count-position'])) ? $options['vote_settings']['mrvt-vote-count-position'] : '';
        $media_postion = (!empty($options['mrvt-media-position'])) ? $options['mrvt-media-position'] : '';
        $build_url = 'https://fonts.googleapis.com/css?family=';
        $ff_names[] = '';
        $list_g_title = (!empty($options['mrv-g-item-title-typo'])) ? $options['mrv-g-item-title-typo'] : '';
        $list_main_title = (!empty($options['mrv-g-list-title-style'])) ? $options['mrv-g-list-title-style'] : '';
        $list_g_desc = (!empty($options['mrv-g-desc-typo'])) ? $options['mrv-g-desc-typo'] : '';
        $safe_fonts = array(
            'Arial',
            'Arial+Black',
            'Helvetica',
            'Times+New+Roman',
            'Courier+New',
            'Tahoma',
            'Verdana',
            'Impact',
            'Trebuchet+MS',
            'Comic+Sans+MS',
            'Lucida+Console',
            'Lucida+Sans+Unicode',
            'Georgia',
            'Palatino+Linotype',
        );
        $list_style = (!empty($options['mrvt-list-style'])) ? $options['mrvt-list-style'] : '';
        // if(array_key_exists('font-family',$list_g_title)){
        //     $all_saved_ff[] = str_replace(" ", "+", $list_g_title['font-family']);
        // }
        $all_saved_ff[] = str_replace(" ", "+", $list_g_desc['font-family']);
        $ip = mrv_get_ip();
        $OBJ = new MRV_Database();

        $sort_votes = (!empty($options['vote_settings']['mrv-sort-vote']) && $options['vote_settings']['mrv-sort-vote'] == "1") ? $options['vote_settings']['mrv-sort-vote'] : '';
        $vote_based_sort = array();
        $item_no = 1;
        $item_specific_style = '';

        $html .= '<div class="mrv-list-container mrv-ul-list ' . $icon_postion . '' . $count_position . ' ' . $list_style . ' " id="' . $WidgetID . '"><div class="mrv-main-title">' . $name . '</div><ul class="mrv-list-item-cnt ">';

        if ($sort_votes == "1") {
            foreach ($items as $key => $value) {
                if (!empty($value['item-multiple-settings']['title_style']['font-family'])) {
                    $all_saved_ff[] = $value['item-multiple-settings']['title_style']['font-family'];
                }
                if (!empty($value['item-multiple-settings']['desc_style']['font-family'])) {
                    $all_saved_ff[] = $value['item-multiple-settings']['desc_style']['font-family'];
                }

                $itemtitle = $value['item-multiple-settings']['item-title'];
                $uniq_id = mrv_clean($itemtitle);
                $total_votes = get_post_meta($meta_rankerID, 'mrv_total_votes' . $uniq_id, true);
                $vote_based_sort[$key] = array('totlal_votes' => $total_votes, 'data' => $value);
            }
            $sort = (!empty($options['mrvt-sort-order']) && $options['mrvt-sort-order'] == "asc") ? sort($vote_based_sort) : rsort($vote_based_sort);
            $items = $vote_based_sort;
            foreach ($items as $key => $value) {

                $itemtitle = $value['data']['item-multiple-settings']['item-title'];
                $itemDescription = !empty($value['data']['item-multiple-settings']['item-desc']) ? '<div class="mrv-list-desc">
        <p class="mrv-description">' . $value['data']['item-multiple-settings']['item-desc'] . '</p></div>' : "";

                $uniq_id = mrv_clean($itemtitle);
                if (($show_votes == "1" || $show_after_vote == "1")) {
                    if ($show_after_vote == "1") {

                        // $voter_id = get_post_meta($meta_rankerID, 'mrv_voter_id_' . $meta_rankerID.$ip, true);
                        $voter_id = $OBJ->check_user_ip($meta_rankerID, $ip);
                        // $voted_already = get_post_meta($meta_rankerID, 'mrv_voted_' . $ip . $meta_rankerID . $voter_id, true);

                        $total_votes = (!empty($voter_id) && $value['totlal_votes'] != "0") ? $value['totlal_votes'] : "";

                    } else {
                        $total_votes = ($value['totlal_votes'] != "0") ? $value['totlal_votes'] : "";

                    }

                } else {
                    $total_votes = "";

                }

                $media_type = mrv_item_media($value['data']['item-multiple-settings']);

                if ($vote_type == "upvote_only") {
                    $up_icon = !empty($options['vote_settings']['mrv-image-select-up']) ? $options['vote_settings']['mrv-image-select-up'] : '';
                    $heart = "heart";
                    (strpos($up_icon, $heart) !== false) ? $replace_string2 = str_replace("heart", "heart-fill", $up_icon) : $replace_string2 = str_replace("up", "up-fill", $up_icon);


                    $total_votes != "" ? $output_up = $replace_string2 : $output_up = $up_icon;
                    $upvote_html = '<div class="mrv-vote-btn mrv-vote-btn-up" data-Vtype="upvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_up . '"></span></div><span style="padding-top: 5px;"></span>';
                    $voting_html = ' <div class="mrv-voting"><div class="mrv-voting-icon">' . $upvote_html . '' . $downvote_html . '</div>    
                     <div class="mrv-vote-count-cnt"><span id="mrv_total_votes_' . $uniq_id . $meta_rankerID . '" class="mrv_total_votes">
                    ' . (($total_votes > 0) ? '+' . $total_votes : $total_votes) . '</span> </div>
                        </div>';
                } else if ($vote_type == "both_vote") {
                    $up_icon = !empty($options['vote_settings']['mrv-image-select-up-dowwn']) ? $options['vote_settings']['mrv-image-select-up-dowwn'] : '';
                    $heart = "heart";
                    (strpos($up_icon, $heart) !== false) ? $replace_string2 = str_replace("heart", "heart-fill", $up_icon) : $replace_string2 = str_replace("up", "up-fill", $up_icon);

                    $total_votes != "" ? (intval($total_votes) > 0) ? $output_up = $replace_string2 : $output_up = $up_icon : $output_up = $up_icon;

                    $upvote_html = '<div class="mrv-vote-btn mrv-vote-btn-up" data-Vtype="upvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_up . '"></span></div><span style="padding-top: 5px;"></span>';
                    $replace_string = str_replace("up", "down", $up_icon);
                    $replace_string3 = str_replace("down", "down-fill", $replace_string);
                    $total_votes != "" ? ((intval($total_votes) < 0) ? $output_down = $replace_string3 : $output_down = $replace_string) : $output_down = $replace_string;
                    $downvote_html = '<span style="padding-top: 5px;"></span><div class="mrv-vote-btn mrv-vote-btn-down" data-Vtype="downvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_down . '"></span></div>';
                    $voting_html = ' <div class="mrv-voting"><div class="mrv-voting-icon">' . $upvote_html . '
                    <div class="mrv-vote-count-cnt"><span id="mrv_total_votes_' . $uniq_id . $meta_rankerID . '" class="mrv_total_votes">
                    ' . (($total_votes > 0) ? '+' . $total_votes : $total_votes) . '</span> </div> ' . $downvote_html . '</div>    
                        </div>';
                }


                $html .= '<li class="mrv-list-item mrv-item-' . $item_no . '"><div class="mrv-list-item-details ' . $media_postion . '">
                ' . $media_type . '
                <div class="mrv-list-content">
                    <div class="mrv-list-header">
                    <div class="mrv-list-title">' . $itemtitle . '</div>
                   ' . $voting_html . '
                    </div>
                    ' . $itemDescription . '
        </div>
	</div></li>';
                $item_specific_style .= $this->mrv_dynamic_itemspecific_styles($WidgetID, $value['data']['item-multiple-settings'], $item_no);

                $item_no++;
            }

        } else {
            //var_dump($show_votes);
            if ($items != '') {
                foreach ($items as $key => $value) {
                    if (!empty($value['item-multiple-settings']['title_style']['font-family'])) {
                        $all_saved_ff[] = $value['item-multiple-settings']['title_style']['font-family'];
                    }
                    if (!empty($value['item-multiple-settings']['desc_style']['font-family'])) {
                        $all_saved_ff[] = $value['item-multiple-settings']['desc_style']['font-family'];
                    }
                    $itemtitle = $value['item-multiple-settings']['item-title'];
                    $itemDescription = !empty($value['item-multiple-settings']['item-desc']) ? '<div class="mrv-list-desc">
                    <p class="mrv-description">' . $value['item-multiple-settings']['item-desc'] . '</p></div>' : "";

                    $uniq_id = mrv_clean($itemtitle);
                    $total_votes = get_post_meta($meta_rankerID, 'mrv_total_votes' . $uniq_id, true);
                    if (($show_votes == "1" || $show_after_vote == "1")) {
                        if ($show_after_vote == "1") {
                            // $voter_id = get_post_meta($meta_rankerID, 'mrv_voter_id_' . $meta_rankerID.$ip, true);
                            $voter_id = $OBJ->check_user_ip($meta_rankerID, $ip);
                            // $voted_already = get_post_meta($meta_rankerID, 'mrv_voted_'.$ip.$meta_rankerID.$voter_id, true);

                            $total_votes = (!empty($voter_id) && $total_votes != "0") ? $total_votes : "";

                        } else {
                            $total_votes = ($total_votes != "0") ? $total_votes : "";

                        }

                    } else {
                        $total_votes = "";

                    }

                    $media_type = mrv_item_media($value['item-multiple-settings']);
                    $media_type = !empty($media_type) ? '<div class="mrv-list-media ">' . $media_type . '</div>' : "";

                    if ($vote_type == "upvote_only") {
                        $up_icon = !empty($options['vote_settings']['mrv-image-select-up']) ? $options['vote_settings']['mrv-image-select-up'] : '';
                        $heart = "heart";
                        (strpos($up_icon, $heart) !== false) ? $replace_string2 = str_replace("heart", "heart-fill", $up_icon) : $replace_string2 = str_replace("up", "up-fill", $up_icon);
                        $total_votes != "" ? $output_up = $replace_string2 : $output_up = $up_icon;
                        $upvote_html = '<div class="mrv-vote-btn mrv-vote-btn-up" data-Vtype="upvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_up . '"></span></div><span style="padding-top: 5px;"></span>';
                        $voting_html = ' <div class="mrv-voting"><div class="mrv-voting-icon">' . $upvote_html . '' . $downvote_html . '</div>    
                     <div class="mrv-vote-count-cnt"><span id="mrv_total_votes_' . $uniq_id . $meta_rankerID . '" class="mrv_total_votes">
                    ' . (($total_votes > 0) ? '+' . $total_votes : $total_votes) . '</span> </div>
                        </div>';
                    } else if ($vote_type == "both_vote") {
                        $up_icon = !empty($options['vote_settings']['mrv-image-select-up-dowwn']) ? $options['vote_settings']['mrv-image-select-up-dowwn'] : '';
                        $heart = "heart";
                        (strpos($up_icon, $heart) !== false) ? $replace_string2 = str_replace("heart", "heart-fill", $up_icon) : $replace_string2 = str_replace("up", "up-fill", $up_icon);

                        $total_votes != "" ? (intval($total_votes) > 0) ? $output_up = $replace_string2 : $output_up = $up_icon : $output_up = $up_icon;

                        $upvote_html = '<div class="mrv-vote-btn mrv-vote-btn-up" data-Vtype="upvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_up . '"></span></div><span style="padding-top: 5px;"></span>';
                        $replace_string = str_replace("up", "down", $up_icon);
                        $replace_string3 = str_replace("down", "down-fill", $replace_string);
                        $total_votes != "" ? ((intval($total_votes) < 0) ? $output_down = $replace_string3 : $output_down = $replace_string) : $output_down = $replace_string;
                        $downvote_html = '<span style="padding-top: 5px;"></span><div class="mrv-vote-btn mrv-vote-btn-down" data-Vtype="downvote" data-ListID="' . $meta_rankerID . '" data-ItemName="' . $uniq_id . '"><span class="dashicons ' . $output_down . '"></span></div>';
                        $voting_html = ' <div class="mrv-voting"><div class="mrv-voting-icon">' . $upvote_html . ' 
                    <div class="mrv-vote-count-cnt"><span id="mrv_total_votes_' . $uniq_id . $meta_rankerID . '" class="mrv_total_votes">
                    ' . (($total_votes > 0) ? '+' . $total_votes : $total_votes) . '</span> </div> ' . $downvote_html . '</div>    
                        </div>';
                    }

                    $html .= '<li class="mrv-list-item mrv-item-' . $item_no . '"><div class="mrv-list-item-details ' . $media_postion . '">
                ' . $media_type . '
                <div class="mrv-list-content">
                    <div class="mrv-list-header">
                        <div class="mrv-list-title">' . $itemtitle . '</div>
                        ' . $voting_html . '
                        </div>
                        ' . $itemDescription . '
                    </div>
                </div></li>';
                    $item_specific_style .= $this->mrv_dynamic_itemspecific_styles($WidgetID, $value['item-multiple-settings'], $item_no);
                    $item_no++;
                }
            }
        }
        $html .= '</ul></div>';
        foreach ($all_saved_ff as $key => $val) {
            if (!in_array($val, $safe_fonts)) {
                $ff_names[] = $val;
            }
        }
        $select_ffs = implode("|", array_filter($ff_names));

        if (!empty($select_ffs)) {
            $build_url .= $select_ffs;
            wp_enqueue_style('mrv-google-font', $build_url, array(), null, null, 'all');
        }

        wp_add_inline_style('mrv-votes', $this->mrv_dynamic_styles($WidgetID, $options));
        wp_add_inline_style('mrv-votes', $item_specific_style);

        return $html;

    }

    public function fetch_icon_image($icon)
    {

        $html = "";
        $html = '<img src="' . MRV_URL . '/assets/images/' . $icon . '.png' . '">';
        return $html;
    }
    public function mrv_dynamic_styles($WidgetID, $options)
    {
        $list_g_title = (!empty($options['mrv-g-item-title-typo'])) ? $options['mrv-g-item-title-typo'] : '';
        $list_g_title_font_w = (!empty($list_g_title['font-weight'])) ? '--mrv-list-title-fnt-weight: ' . $list_g_title['font-weight'] . ';' : '';
        $list_g_title_font_style = (!empty($list_g_title['font-style'])) ? ' --mrv-list-title-fnt-style:' . $list_g_title['font-style'] . ';' : '';
        $list_g_title_font_size = (!empty($list_g_title['font-size'])) ? ' --mrv-list-title-fnt-size: ' . $list_g_title['font-size'] . 'px;' : '';
        $list_g_title_line_hight = (!empty($list_g_title['line-height'])) ? '--mrv-list-title-line-height:' . $list_g_title['line-height'] . ';' : '';
        $list_g_title_font_family = (!empty($list_g_title['font-family'])) ? '--mrv-list-title-fnt-family:' . $list_g_title['font-family'] . ';' : '';
        $list_g_title_clr = (!empty($list_g_title['color'])) ? '--mrv-list-title-clr:' . $list_g_title['color'] . ';' : '';

        $list_main_title = (!empty($options['mrv-g-list-title-style'])) ? $options['mrv-g-list-title-style'] : '';
        $list_main_title_font_w = (!empty($list_main_title['font-weight'])) ? '--mrv-list-main-title-fnt-weight: ' . $list_main_title['font-weight'] . ';' : '';
        $list_main_title_font_style = (!empty($list_main_title['font-style'])) ? ' --mrv-list-main-title-fnt-style:' . $list_main_title['font-style'] . ';' : '';
        $list_main_title_font_size = (!empty($list_main_title['font-size'])) ? ' --mrv-list-main-title-fnt-size: ' . $list_main_title['font-size'] . 'px;' : '';
        $list_main_title_line_hight = (!empty($list_main_title['line-height'])) ? '--mrv-list-main-title-line-height:' . $list_main_title['line-height'] . ';' : '';
        $list_main_title_font_family = (!empty($list_main_title['font-family'])) ? '--mrv-list-main-title-fnt-family:' . $list_main_title['font-family'] . ';' : '';
        $list_main_title_clr = (!empty($list_main_title['color'])) ? '--mrv-list-main-title-clr:' . $list_main_title['color'] . ';' : '';

        $list_g_desc = (!empty($options['mrv-g-desc-typo'])) ? $options['mrv-g-desc-typo'] : '';
        $list_g_desc_fnt_wt = (!empty($list_g_desc['font-weight'])) ? ' --mrv-list-desc-fnt-weight: ' . $list_g_desc['font-weight'] . ';' : '';
        $list_g_desc_fnt_style = (!empty($list_g_desc['font-style'])) ? ' --mrv-list-desc-fnt-style: ' . $list_g_desc['font-style'] . ';' : '';
        $list_g_desc_fnt_clr = (!empty($list_g_desc['color'])) ? ' --mrv-list-desc-color: ' . $list_g_desc['color'] . ';' : '';

        $list_g_desc_fnt_size = (!empty($list_g_desc['font-size'])) ? '--mrv-list-desc-fnt-size:' . $list_g_desc['font-size'] . ';' : '';
        $list_g_desc_line_hight = (!empty($list_g_desc['line-height'])) ? '--mrv-list-desc-line-height:' . $list_g_desc['line-height'] . ';' : '';
        $list_g_desc_fnt_family = (!empty($list_g_desc['font-family'])) ? '--mrv-list-desc-fnt-family:' . $list_g_desc['font-family'] . ';' : '';



        $icon_size = (!empty($options['vote_settings']['vote_icon_size'])) ? '--mrv-icon-size:' . $options['vote_settings']['vote_icon_size'] . 'px;' : '';
        $up_icon_color = (!empty($options['vote_icon_style']['vote_up_color'])) ? '--mrv-voting-up-color:' . $options['vote_icon_style']['vote_up_color'] . ';' : '';
        $down_icon_color = (!empty($options['vote_icon_style']['vote_down_color'])) ? '--mrv-voting-down-color:' . $options['vote_icon_style']['vote_down_color'] . ';' : '';
        $count_color = (!empty($options['vote_icon_style']['vote_count_color'])) ? '--mrv-count-color:' . $options['vote_icon_style']['vote_count_color'] . ';' : '';

        $count_size = (!empty($options['vote_settings']['vote_count_size'])) ? '--mrv-count-size:' . $options['vote_settings']['vote_count_size'] . 'px;' : '';
        $item_g_border = (!empty($options['list_item_style']['item-g-border'])) ? $options['list_item_style']['item-g-border'] : '';

        $item_g_border_wd_css = (!empty($item_g_border['top']) || $item_g_border['right'] || $item_g_border['bottom'] || $item_g_border['left']) ? '  --mrv-list-item-bd-width: ' . $item_g_border['top'] . 'px ' . $item_g_border['right'] . 'px ' . $item_g_border['bottom'] . 'px ' . $item_g_border['left'] . 'px;' : '';
        $item_g_border_clr_css = (!empty($item_g_border['color'])) ? '--mrv-list-item-bd-clr:' . $item_g_border['color'] . ';' : '';
        $item_g_border_styl_css = (!empty($item_g_border['style'])) ? '--mrv-list-item-bd-style: ' . $item_g_border['style'] . ';' : '';
        $item_g_bg_color = (!empty($options['list_item_style']['item-g-bg-color'])) ? $options['list_item_style']['item-g-bg-color'] : '';
        $item_g_bg_color_css = (!empty($item_g_bg_color)) ? '--mrv-list-item-bk-color: ' . $item_g_bg_color . ';' : '';
        $item_margin = (!empty($options['list_item_style']['item-g-margin'])) ? $options['list_item_style']['item-g-margin'] : '';
        $item_margin_css = (!empty($item_margin['top']) || $item_margin['right'] || $item_margin['bottom'] || $item_margin['left']) ? '  --mrv-list-item-margin:' . $item_margin['top'] . 'px ' . $item_margin['right'] . 'px ' . $item_margin['bottom'] . 'px ' . $item_margin['left'] . 'px;' : "";
        $item_padding = (!empty($options['list_item_style']['item-g-padding'])) ? $options['list_item_style']['item-g-padding'] : '';
        $item_padding_css = (!empty($item_padding['top']) || $item_padding['right'] || $item_padding['bottom'] || $item_padding['left']) ? '--mrv-list-item-pd:' . $item_padding['top'] . 'px ' . $item_padding['right'] . 'px ' . $item_padding['bottom'] . 'px ' . $item_padding['left'] . 'px;' : "";
        $item_g_list_bg_color = (!empty($options['list_style']['item-list-g-bg-color'])) ? $options['list_style']['item-list-g-bg-color'] : '';
        $item_g_list_bg_color_css = (!empty($item_g_list_bg_color)) ? ' --mrv-list-cnt-bk-clr: ' . $item_g_list_bg_color . ';' : "";
        $list_item_g_border = (!empty($options['list_style']['item-list-g-border'])) ? $options['list_style']['item-list-g-border'] : '';
        $list_item_g_border_css = (!empty($list_item_g_border['top']) || $list_item_g_border['right'] || $list_item_g_border['bottom'] || $list_item_g_border['left']) ? ' --mrv-list-cnt-bd-width: ' . $list_item_g_border['top'] . 'px ' . $list_item_g_border['right'] . 'px ' . $list_item_g_border['bottom'] . 'px ' . $list_item_g_border['left'] . 'px;' : "";
        $list_item_g_border_clr_css = (!empty($list_item_g_border['color'])) ? ' --mrv-list-cnt-bd-clr:' . $list_item_g_border['color'] . ';' : '';
        $list_item_g_border_styl_css = (!empty($list_item_g_border['style'])) ? ' --mrv-list-cnt-bd-style: ' . $list_item_g_border['style'] . ';' : '';

        $list_padding = (!empty($options['list_style']['item-list-g-padding'])) ? $options['list_style']['item-list-g-padding'] : '';
        $list_padding_css = (!empty($list_padding['top']) || $list_padding['right'] || $list_padding['bottom'] || $list_padding['left']) ? ' --mrv-list-cnt-padding:' . $list_padding['top'] . 'px ' . $list_padding['right'] . 'px ' . $list_padding['bottom'] . 'px ' . $list_padding['left'] . 'px;' : "";

        $style = "";
        $style = '#' . $WidgetID . '.mrv-list-container{
           ' . $list_padding_css . '
            ' . $item_margin_css . '
            ' . $item_padding_css . '
            ' . $up_icon_color . '
            ' . $down_icon_color . '
            ' . $count_color . '
            ' . $icon_size . '
            ' . $count_size . '
            ' . $list_item_g_border_css . '
            ' . $list_item_g_border_clr_css . '
            ' . $list_item_g_border_styl_css . '
            ' . $item_g_border_wd_css . '
             ' . $item_g_border_clr_css . '
            ' . $item_g_border_styl_css . '
             ' . $list_g_title_clr . '
            ' . $list_g_title_font_w . '
           ' . $list_g_title_font_style . '
           ' . $list_g_title_font_size . '
            ' . $list_g_title_font_family . '
            ' . $list_g_title_line_hight . '
            ' . $list_main_title_font_w . '
           ' . $list_main_title_font_style . '
           ' . $list_main_title_font_size . '
            ' . $list_main_title_font_family . '
            ' . $list_main_title_clr . '
            ' . $list_main_title_line_hight . '
             ' . $list_g_desc_fnt_clr . '
              ' . $list_g_desc_fnt_size . '
               ' . $list_g_desc_fnt_wt . '
               ' . $list_g_desc_fnt_style . '
                ' . $list_g_desc_fnt_family . '
                ' . $list_g_desc_line_hight . '
                ' . $item_g_bg_color_css . '
                   ' . $item_g_list_bg_color_css . '

        }';
        return $style;
    }

    public function mrv_dynamic_itemspecific_styles($WidgetID, $options, $item_no)
    {
        $list_title = (!empty($options['item_title_style'])) ? $options['item_title_style'] : '';

        $list_desc = (!empty($options['desc_style'])) ? $options['desc_style'] : '';

        $item_border = (!empty($options['item-border'])) ? $options['item-border'] : '';
        $item_bg_color = (!empty($options['item-bg-color'])) ? $options['item-bg-color'] : '';

        $style = "";
        if (!empty($list_title['color']) || !empty($list_title['font-size']) || !empty($list_title['font-weight']) || !empty($list_title['font-family']) || !empty($list_title['font-style'])) {
            $style .= '#' . $WidgetID . ' .mrv-list-item.mrv-item-' . $item_no . ' .mrv-list-title{
              color: ' . $list_title['color'] . ';
            font-weight: ' . $list_title['font-weight'] . ';
            font-style:' . $list_title['font-style'] . ';
            font-size: ' . $list_title['font-size'] . 'px;
             font-family:' . $list_title['font-family'] . ';
               line-height:' . $list_title['line-height'] . ';
        }';
        }
        if (!empty($list_title['color']) || !empty($list_title['font-size']) || !empty($list_title['font-weight']) || !empty($list_title['font-family']) || !empty($list_title['font-style'])) {
            $style .= '#' . $WidgetID . ' .mrv-list-item.mrv-item-' . $item_no . ' .mrv-list-title{
              color: ' . $list_title['color'] . ';
            font-weight: ' . $list_title['font-weight'] . ';
            font-style:' . $list_title['font-style'] . ';
            font-size: ' . $list_title['font-size'] . 'px;
             font-family:' . $list_title['font-family'] . ';
               line-height:' . $list_title['line-height'] . ';
        }';
        }
        if (!empty($list_desc['color']) || !empty($list_desc['font-size']) || !empty($list_desc['font-weight']) || !empty($list_desc['font-family']) || !empty($list_desc['font-style'])) {
            $style .= '#' . $WidgetID . ' .mrv-list-item.mrv-item-' . $item_no . ' .mrv-list-desc{
             color: ' . $list_desc['color'] . ';
              font-size:' . $list_desc['font-size'] . ';
                font-weight: ' . $list_desc['font-weight'] . ';
                font-style: ' . $list_desc['font-style'] . ';
                font-family:' . $list_desc['font-family'] . ';
                  line-height:' . $list_desc['line-height'] . ';

        }';
        }
        if (!empty($item_border['color']) || !empty($item_bg_color) || $item_border['style'] != "none") {
            $style .= '#' . $WidgetID . ' .mrv-list-item.mrv-item-' . $item_no . '{
                border-color:' . $item_border['color'] . ';
            border-style: ' . $item_border['style'] . ';
             border-width: ' . $item_border['top'] . 'px ' . $item_border['right'] . 'px ' . $item_border['bottom'] . 'px ' . $item_border['left'] . 'px;
            background-color: ' . $item_bg_color . ';

        }
        ';
        }
        return $style;
    }

    //Get current url

    function mrv_get_current_url()
    {
        global $wp, $post;
        return home_url($wp->request);

    }

}

new MrvShortcodes();