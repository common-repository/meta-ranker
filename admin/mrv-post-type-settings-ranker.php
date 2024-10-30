<?php

/**
 *
 * This file is responsible for creating all admin settings in Timeline Builder (post)
 */
if (!defined("ABSPATH")) {
    exit('Can not load script outside of WordPress Enviornment!');
}
$activated = get_option('metaRankerActivated');
//$activated = "gg";

if(!$activated ){

    //
// Create a metabox
$plugin_not_active="mrv_not_active_notice";
    CSF::createMetabox($plugin_not_active, array(
        'title' => 'Plugin Not Activated',
        'post_type' => 'meta-ranker',
        'context' => 'advanced', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
    ));
    //
    // Create a section
    CSF::createSection($plugin_not_active, array(
        'title' => '',
        'fields' => array(
                    array(
            'type'    => 'notice',
            'style'   => 'warning',
            'content' => 'Please activate the plugin to use these features <a href="'.admin_url('edit.php?post_type=meta-ranker&page=metaranker-activation').'">Link</a>',
            ),
        ),
    ));



}
else{
$post_array = ["meta-ranker"];

if (class_exists('CSF_Setup') && array(mrv_get_cpt(), $post_array)):



//
// Metabox of the PAGE
// Set a unique slug-like ID
//
    $prefix_page_opts = 'mrv_post_settinga';
    // require_once MRV_PATH . 'includes/db/mrv-db.php';
    require_once MRV_PATH . 'includes/functions.php';

    // $dbobj = new MRV_Database();
    $post_idd = isset($_GET['post']) ? sanitize_text_field($_GET['post']) : '';
    $list_data = get_post_meta($post_idd, 'mrv_post_settinga', true);
    $items = !empty($list_data['mrv-item']) ? $list_data['mrv-item'] : '';

    $tabel_body = "";
    if (is_array($items)) {
        foreach ($items as $key => $value) {
            $itemtitle = $value['item-multiple-settings']['item-title'];

            $uniq_id = mrv_clean_sc($itemtitle);
            $total_votes = get_post_meta($post_idd, 'mrv_total_votes' . $uniq_id, true);
            $total_votes = !empty($total_votes) ? $total_votes : '--';

            $up_vote = get_post_meta($post_idd, 'mrv_up_votes' . $uniq_id, true);
            $up_vote = !empty($up_vote) ? $up_vote : '--';

            $down_vote = get_post_meta($post_idd, 'mrv_down_votes' . $uniq_id, true);
            $down_vote = !empty($down_vote) ? $down_vote : '--';

            $tabel_body .= ' <tr>
		    <td>' . $key . '</td>
		      <td>' . $itemtitle . '</td>
		    <td>' . $up_vote . '</td>
		    <td>' . $down_vote . '</td>
		    <td>' . $total_votes . '</td>
		  </tr>';

        }
    }

//
// Create a metabox
//

    CSF::createMetabox($prefix_page_opts, array(
        'title' => __('Meta Ranker', 'cptbx'),
        'post_type' => 'meta-ranker',
        'data_type' => 'serialize',
        'output_css' => true,
        'nav' => 'inline',
        'context' => 'normal', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
        'show_restore' => false,
    ));
// Item Section
//
    global $image_url;
    global $selected_option;
    $selected_option = 'option-1';
    $styles = 'style-1';
    $font_size_g_desc = '30';
    $media = 'image';
    $current_url = $_SERVER['REQUEST_URI'];
    $media_position = 'mrv-media-bottom';
    $upvote_type = 'upvote_only';
    $icon_vote = 'dashicons-up';
    $icon_vote_both = 'dashicons-up';
    $vote_size = '30';
    $count_size = '15';
    $icon_position = 'mrv-list-icon-left';
    $up_color = '#000000';
    $image_size = 'medium';
    // $image_url1 = 'assets/images/default_image.png';
    $font_size_list_item = '32';
    $font_size_list_title = '38';

    if (strpos($current_url, '/post-new.php?post_type=meta-ranker') !== false) {
        if (isset($_POST['default_template'])) {
            switch ($_POST['default_template']) {
                case 'option-1':
                    $selected_option = $_POST['default_template'];
                    $styles = 'style-1';
                    $font_size_g_desc = '30';
                    $media = 'image';
                    $media_position = 'mrv-media-left';
                    $upvote_type = 'upvote_only';
                    $icon_vote = 'dashicons-up';
                    $vote_size = '30';
                    $count_size = '15';
                    $icon_position = 'mrv-list-icon-left';
                    $up_color = '#1e73be';
                    $font_size_list_item = '32';
                    $font_size_list_title = '38';
                    $image_size = 'small';
                    break;
                case 'option-2':
                    $selected_option = $_POST['default_template'];
                    $styles = 'style-2';
                    $font_size_g_desc = '30';
                    $media = 'none';
                    $upvote_type = 'both_vote';
                    $icon_vote_both = 'dashicons-up3';
                    $media_position = 'mrv-media-left';
                    $vote_size = '25';
                    $count_size = '15';
                    $icon_position = 'mrv-list-icon-right2';
                    $up_color = '#22871f';
                    $font_size_list_item = '32';
                    $font_size_list_title = '38';            
                    break;
                // case 'option-3':
                //     $selected_option = $_POST['default_template'];
                //     $styles = 'style-3';
                //     $font_size = '16';
                //     $media = 'youtube';
                //     break;
                // case 'option-4':
                //     $selected_option = $_POST['default_template'];
                //     $styles = 'style-3';
                //     $font_size = '18';
                //     $media = 'youtube';
                //     break;
            }
        }
        function display_form(){
            $image_url1 = 'assets/images/template_1.png';
            $image_url2 = 'assets/images/template_2.png';
            $images = [$image_url1, $image_url2];
            global $selected_option;
            echo '
            <style>

                .top-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .container {
                    display: flex;
                    flex-wrap: wrap;
                    position: relative;
                    justify-content: center;
                    align-items: center;
                }
                .button-group{
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .item {
                    margin: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                #default_template {
                    font-size: 14px;
                    min-width: 10rem;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    padding: 10px 14px;
                    gap: 8px;
                    width: 654px;
                    height: 44px;
                    background: #FFFFFF;
                    border: 1px solid #D0D5DD;
                    box-shadow: 0px 1px 2px rgba(16, 24, 40, 0.05);
                    border-radius: 8px;
                    flex: none;
                    order: 1;
                    align-self: stretch;
                    flex-grow: 0;
                }

                input[type="submit"] {
                    color: #fff;
                    cursor: pointer;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: row;
                    justify-content: center;
                    align-items: center;
                    padding: 10px 16px;
                    gap: 8px;
                    width: 127px;
                    height: 40px;
                    background: #3858e9;
                    border: 1px solid #3858e9;
                    box-shadow: 0px 1px 2px rgba(16, 24, 40, 0.05);
                    border-radius: 8px;
                    flex: none;
                    order: 2;
                    flex-grow: 0;
                }
            </style>
            <div class="top-container">
                <div class="form-container">
                    <form class="item" method="post" action="">
                        <select name="default_template" id="default_template" style="grid-row: span 2;">
                        <optgroup label="--Select Template--">    
                        <option value="option-1"';
            if ($selected_option === 'option-1') {
                echo ' selected';
            }
            echo '>Template 1</option>
                                <option value="option-2"';
            if ($selected_option === 'option-2') {
                echo ' selected';
            }
            echo '>Template 2</option>';
            // Uncomment the following lines if you want more options
            // echo '<option value="option-3"';
            // if ($selected_option === 'option-3') {
            //     echo ' selected';
            // }
            // echo '>Option 3</option>
            //         <option value="option-4"';
            // if ($selected_option === 'option-4') {
            //     echo ' selected';
            // }
            // echo '>Option 4</option>
            echo'    </select>
            </optgroup>
                            <br>
                            <input type="submit" value="Submit">
                        </form>
                        <div class="container">   
                '; 
            //     <style> 
            //     .carousel {
            //       width: 500px;
            //       height: 300px;
            //       position: relative;
            //       overflow: hidden;
            //     }
                
            //     .carousel-inner {
            //       width: 400%;
            //       height: 100%;
            //       display: flex;
            //       transition: transform 0.3s ease-in-out;
            //     }
                
            //     .carousel-slide {
            //       width: 25%;
            //       height: 100%;
            //     }
                
            //     #carousel-1:checked ~ .carousel-inner {
            //       transform: translateX(0);
            //     }
                
            //     #carousel-2:checked ~ .carousel-inner {
            //       transform: translateX(-25%);
            //     }
                
            //     #carousel-3:checked ~ .carousel-inner {
            //       transform: translateX(-50%);
            //     }
                
            //     #carousel-4:checked ~ .carousel-inner {
            //       transform: translateX(-75%);
            //     }
                
            //     .carousel-slide {
            //       transition: opacity 0.3s ease-in-out;
            //     }
                
            //     #carousel-1:checked ~ .carousel-indicators .carousel-indicator-1,
            //     #carousel-2:checked ~ .carousel-indicators .carousel-indicator-2,
            //     #carousel-3:checked ~ .carousel-indicators .carousel-indicator-3,
            //     #carousel-4:checked ~ .carousel-indicators .carousel-indicator-4 {
            //       background-color: #333;
            //     }
                
            //     .carousel-indicators {
            //       position: absolute;
            //       bottom: 10px;
            //       left: 50%;
            //       transform: translateX(-50%);
            //       display: flex;
            //     }
                
            //     .carousel-indicator {
            //       width: 10px;
            //       height: 10px;
            //       background-color: #999;
            //       border-radius: 50%;
            //       margin: 0 5px;
            //       cursor: pointer;
            //     }
                
            //     .carousel-inputs {
            //       display: none;
            //     }
            //     .hidden {
            //       display: none; }
            //     </style>
            //   <div class="carousel">
            //   <input type="radio" name="carousel" id="carousel-1" class="hidden carousel-inputs" checked>
            //   <input type="radio" name="carousel" id="carousel-2" class="hidden carousel-inputs">
            //   <div class="carousel-inner">
            //   <div class="carousel-slide""><img class="item" src="' . esc_url(MRV_URL . $images[0]) . '" alt="Image" width="350" height="185" style="grid-row: span 2;"></div>
            //   <div class="carousel-slide""><img class="item" src="' . esc_url(MRV_URL . $images[1]) . '" alt="Image" width="350" height="185" style="grid-row: span 2;"></div>
            //   </div>
            //   <div class="carousel-indicators">
            //     <label class="carousel-indicator carousel-indicator-1" for="carousel-1"></label>
            //     <label class="carousel-indicator carousel-indicator-2" for="carousel-2"></label>
            //   </div>
            // </div>

                foreach ($images as $image) {
                echo '<img class="item" src="' . esc_url(MRV_URL . $image) . '" alt="Image" width="475" height="220" style="grid-row: span 2;">';
                };            
            echo '</div></div></div>';
        }
        add_action('admin_notices', 'display_form');
    }
    
    
    CSF::createSection($prefix_page_opts, array(
        'title' => __('List Item', 'cptbx'),
        'icon' => 'fas fa-rocket',
        'fields' => array(
            array(
                'id' => 'mrv-item',
                'type' => 'group',
                'title' => '',
                'accordion_title_by' => array('item-title'),

                'accordion_title_number' => true,
                'fields' => array(
                    // Content
                    array(
                        'id' => 'item-multiple-settings',
                        'type' => 'tabbed',
                        'tabs' => array(
                            array(
                                'id' => 'cptb-item-tab-content',
                                'title' => __('Content', 'cptbx'),
                                'fields' => array(
                                    array(
                                        'id' => 'item-title',
                                        'type' => 'text',
                                        'class' => 'mrv_custom_item',
                                        'title' => __('Title', 'cptbx'),
                                        'placeholder' => 'Write your title',
                                    ),

                                    // item media
                                    array(
                                        'id' => 'item-media-type',
                                        'type' => 'button_set',
                                        'title' => __('Media Type', 'cptbx'),
                                        'default' => $media,
                                        'options' => array(
                                            'image' => 'Image',
                                            'youtube' => 'Youtube Video',
                                            'none' => 'None',
                                        ),
                                    ),
                                    // item media
                                    array(
                                        'id' => 'item-media-image',
                                        'type' => 'media',
                                        'title' => __('Choose image', 'cptbx'),
                                        'library' => 'image',
                                        'url' => false,
                                        'dependency' => array(
                                            'item-media-type',
                                            '==',
                                            'image',
                                        ),
                                    ),
                                    array(
                                    'id'=>'item-image-size',
                                    'type'=>'select',
                                    'title'=> __('Image Size','cptbx'),
                                    'default'=>$image_size,
                                    'desc'=>'This settings only work with media type = image.',
                                    'options'=>'mrv_available_featured_image_size',
                                    'dependency'=>array(
                                        'item-media-type',
                                        '==',
                                        'image'
                                    ) 
                                    ),

                                    array(
                                        'id' => 'item-media-youtube',
                                        'type' => 'text',
                                        'title' => __('Add Youtube URL', 'cptbx'),
                                        'dependency' => array(
                                            'item-media-type',
                                            '==',
                                            'youtube',
                                        ),
                                        //'validate'=>'itb_validate_youtubeUrl'
                                    ),
                                    // item media

                                    // item Description
                                    array(
                                        'id' => 'item-desc',
                                        'type' => 'wp_editor',
                                        'title' => __('Description', 'cptbx'),
                                        'media_buttons' => false,
                                    ),

                                ),
                            ),

                            // array(
                            //     'title' => __('Default Option', 'cptbx'),
                            //     'fields' => array(
                            //         array(
                            //     'placeholder' => 'Select an option',
                            //     'id' => 'item-default-options',
                            //     'title' => 'Deafult Style',
                            //     'type' => 'select',
                            //     'options' => array(
                            //             'option_a' => 'Option A',
                            //             'option_b' => 'Option B',
                            //         ),
                            //     ),
                            //     ),
                            // ),

                            // Advanced Settings Tab fields
                            array(
                                'title' => __('Advanced Settings', 'cptbx'),
                                'fields' => array(
                                    array(
                                        'id' => 'item_title_style',
                                        'title' => __('Item Title Style', 'cptbx'),
                                        'type' => 'typography', // Do not add unnecessary typography settings
                                       'font_weight' => false,
                                        //'font_style'=>false,
                                        'text_align' => false,
                                        
                                        'text_transform' => false,
                                        'subset' => false,
                                        'letter_spacing' => false,
                                        'preview' => false,
                                        'default' => array(                                               
                                            'font-family'        => '',
                                            'font-style'         => '',
                                            'font-size'          => '',
                                            'line-height'        => '',
                                            'font-weight'        => '',
                                            'color'              => '',
                                            ),
                                    ),
                                    array(
                                        'id' => 'desc_style',
                                        'title' => __('Description  Style', 'cptbx'),
                                        'type' => 'typography', // Do not add unnecessary typography settings
                                       'font_weight' => false,
                                        //'font_style'=>false,
                                        'text_align' => false,
                                        
                                        'text_transform' => false,
                                        'subset' => false,
                                        'letter_spacing' => false,
                                        'preview' => false,
                                        'default' => array(                                               
                                            'font-family'        => '',
                                            'font-style'         => '',
                                            'font-size'          => '',
                                            'line-height'        => '',
                                            'font-weight'        => '',
                                            'color'              => '',
                                            ),
                                    ),

                                    array(
                                        'id' => 'item-bg-color',
                                        'type' => 'color',
                                        'title' => __('Item Background Color', 'cptbx'),
                                        'default' => 'white',
                                    ),
                                    array(
                                        'id' => 'item-border',
                                        'type' => 'border',
                                        'title' => 'Item Border',
                                        'default' => array(
                                            'color'      => 'black',                                              
                                            'style'      => 'solid',
                                            'top'        => '0',
                                            'right'      => '0',
                                            'bottom'     => '0',
                                            'left'       => '0',                                           
                                        ),
                                    ),
                                ),
                            ),

                        ),
                    ),
                ),
                /*
                Default items to show when new timeline is created.
                Callback function to render default stories
                 */
                // 'default'   =>
                //  cptb_default_stories()

            ),
        ),
    ));

    CSF::createSection($prefix_page_opts, array(
        'title' => __('Design & Settings', 'cptbx'),
        'icon' => 'fas fa-palette',
        'fields' => array(
            array(
                'id' => 'mrvt-list-style',
                'type' => 'select',
                'title' => 'List Style',
                'placeholder' => 'Select List Style',
                'options' => array(
                    'style-1' => 'Simple List',
                    'style-2' => 'Bullet List',
                    'style-3' => 'Number List',
                ),
                'default' => $styles,

            ),
                        array(
                'id' => 'mrvt-media-position',
                'type' => 'image_select',
                'title' => 'Media Position',
                'placeholder' => 'Select Media Position',
                'options' => array(                 
                    'mrv-media-top' => MRV_URL . 'assets/images/above.png',
                    'mrv-media-bottom' => MRV_URL . 'assets/images/below.png',                  
                    'mrv-media-right' => MRV_URL . 'assets/images/right.png',
                    'mrv-media-left' => MRV_URL . 'assets/images/left.png',
                ),
                'default' => $media_position,
            ),

         
            array(
                'id'     => 'vote_settings',
                'type'   => 'fieldset',
                'title'  => 'Vote Settings',
                'fields' => array(
                      array(
                'id' => 'mrv-vote-type',
                'type' => 'radio',
                'inline' => true,
                'title' => 'Vote Type',
                'options' => array(
                    'upvote_only' => 'Up Vote Only',
                    'both_vote' => 'Both Up/Down',

                ),
                'default' => $upvote_type,
            ),
            array(
                'id' => 'mrv-image-select-up',
                'type' => 'image_select',
                'title' => 'Vote Icon',
                'options' => array(
                    'dashicons-up' => MRV_URL . 'assets/images/up2.png',
                    'dashicons-up3' => MRV_URL . 'assets/images/up4.png',
                    'dashicons-up4' => MRV_URL . 'assets/images/up5.png',
                    'dashicons-up5' => MRV_URL . 'assets/images/up6.png',
                    'dashicons-up6' => MRV_URL . 'assets/images/up7.png',
                    'dashicons-heart' => MRV_URL . 'assets/images/heart.png',
                ),
                'dependency' => array(
                    'mrv-vote-type',
                    '==',
                    'upvote_only',
                ),
                'default' => $icon_vote,
            ),
            array(
                'id' => 'mrv-image-select-up-dowwn',
                'type' => 'image_select',
                'title' => 'Vote Icon',
                'options' => array(
                    'dashicons-up' => MRV_URL . 'assets/images/up-down-1.png',
                    'dashicons-up3' => MRV_URL . 'assets/images/up-down-4.png',
                    'dashicons-up4' => MRV_URL . 'assets/images/up-down-5.png',
                    'dashicons-up5' => MRV_URL . 'assets/images/up-down-7.png',
                    'dashicons-up6' => MRV_URL . 'assets/images/up-down-8.png',
                ),
                'dependency' => array(
                    'mrv-vote-type',
                    '==',
                    'both_vote',
                ),
                'default' => $icon_vote_both,
            ),
            array(
                'id'          => 'vote_icon_size',
                'type'        => 'number',
                'title'       => 'Icon Size',
                'unit'        => 'px',
                'output'      => '.heading', 
                'default' => $vote_size,              
                
                ),
             array(
                'id'          => 'vote_count_size',
                'type'        => 'number',
                'title'       => 'Count Size',
                'unit'        => 'px',
                'output'      => '.heading',               
                'default' => $count_size,
                ),
            array(
                'id' => 'mrvt-icon-position',
                'type' => 'select',
                'title' => 'Icon Position',
                'placeholder' => 'Select Icon Position',
                'options' => array(
                    'mrv-list-icon-left' => 'Before Title',
                    'mrv-list-icon-right2' => 'After Title Begin',
                    'mrv-list-icon-right' => 'After Title Last',
                    'mrv-icon-before-list' => 'Before list Item',
                    'mrv-icon-after-list' => 'After list Item',
                ),
                'default' => $icon_position,
            ),
            array(
                'id' => 'mrvt-vote-count-position',
                'type' => 'image_select',
                'title' => 'Vote Count Position',
                'placeholder' => 'Select Vote Count Position',
                'options' => array(
                    '-left' => MRV_URL . 'assets/images/left.png',
                    '-right' => MRV_URL . 'assets/images/right.png',
                    '-up' => MRV_URL . 'assets/images/above.png',
                    '-down' => MRV_URL . 'assets/images/below.png',
                ),
                'default' => '-left',
            ),

                        array(
                'id' => 'mrv-show-votes',
                'type' => 'switcher',
                'text_on' => 'Yes',
                'text_off' => 'No',
                'title' => 'Show Votes',
                'default' => false,
            ),
            array(
                'id' => 'mrv-show-votes-after-vote',
                'type' => 'switcher',
                'text_on' => 'Yes',
                'text_off' => 'No',
                'dependency' => array('mrv-show-votes', '==', false),
                'title' => 'Show Votes after Voting',
                'default' => true,
            ),
                  array(
                'id' => 'mrv-sort-vote',
                'type' => 'switcher',
                'text_on' => 'Yes',
                'text_off' => 'No',
                'title' => 'Sort List Based On Votes',
                'default' => false,
            ),
            array(
                'id' => 'mrvt-sort-order',
                'type' => 'select',
                'title' => 'Sort Order',
                'options' => array(
                    'asc' => 'ASC',
                    'desc' => 'DESC',
                ),
                'dependency' => array('mrv-sort-vote', '==', true),

            ),

                )),
                array(
                    'id'     => 'vote_icon_style',
                    'type'   => 'fieldset',
                    'title'  => 'Vote Icon',
                    'fields' => array(
                                array(
                    'id' => 'vote_up_color',
                    'type' => 'color',
                    'title' => __('Up Color ', 'cptbx'),
                    'default' => $up_color,
    
                ),
                        array(
                    'id' => 'vote_down_color',
                    'type' => 'color',
                    'title' => __('Down Color', 'cptbx'),
                    'default' => '#d52121',
                ),
                        array(
                    'id' => 'vote_count_color',
                    'type' => 'color',
                    'title' => __('Count Color ', 'cptbx'),
                    'default' => 'black',
                ),
    
    
                    )),
            array(
                'id' => 'mrv-g-list-title-style',
                'title' => __('List Title Style', 'cptbx'),
                'type' => 'typography',
                'font_weight' => false,
                //'font_style'=>false,
                'text_align' => false,
                
                'text_transform' => false,
                'subset' => false,
                'letter_spacing' => false,
                'preview' => true,
                'default' => array(                                               
                    'font-family'        => '',
                    'font-style'         => 'Bold 700',
                    'font-size'          => $font_size_list_title,
                    'line-height'        => '2',
                    'font-weight'        => 'bold',
                    'color'              => 'black',
                    ),
            ),
          
      

            array(
                'id' => 'mrv_list_settings',
                'type' => 'submessage',
                'content' => 'These settings will be overriden by specific item\'s settings',
                'style' => 'warning',
            ),
            array(
                'id' => 'mrv-g-item-title-typo',
                'title' => __('Item Title Style', 'cptbx'),
                'type' => 'typography',
                'font_weight' => false,
                //'font_style'=>false,
                'text_align' => false,
                
                'text_transform' => false,
                'subset' => false,
                'letter_spacing' => false,
                'preview' => true,
                'default' => array(                                               
                    'font-family'        => '',
                    'font-style'         => 'Bold 700',
                    'font-size'          => $font_size_list_item,
                    'line-height'        => '2',
                    'font-weight'        => 'bold',
                    'color'              => 'black',
                    ),
            ),

            array(
                'id' => 'mrv-g-desc-typo',
                'title' => __('Description Style', 'cptbx'),
                'type' => 'typography', // Do not add unnecessary typography settings
                'font_weight' => false, //   Description is added from WP Classic Editor
                //  'font_style'=>false,                          // All formatting can be done through WP Classic Editor
                'text_align' => false,
                 
                'text_transform' => false,
                'subset' => false,
                'letter_spacing' => false,
                'preview' => true,
                'default' => array(                                               
                    'font-family'        => '',
                    'font-style'         => 'Normal 400',
                    'font-size'          => $font_size_g_desc,
                    'line-height'        => '2',
                    'font-weight'        => 'normal',
                    'color'              => 'black',
                    ),
            ),
           
        
           
             
           

            array(
                'id'     => 'list_item_style',
                'type'   => 'fieldset',
                'title'  => 'List Item Style',
                'fields' => array(
                     array(
                'id' => 'item-g-bg-color',
                'type' => 'color',
                'title' => __('Background', 'cptbx'),
                'default' => 'white',

            ),
                     array(
                'id' => 'item-g-margin',
                'type' => 'border',
                'title' => 'Margin',
                'color'=>false,
                'style'=>false,  
                'default' => array(
                    'color'      => 'black',                                              
                    'style'      => 'solid',
                    'top'        => '10',
                    'right'      => '0',
                    'bottom'     => '0',
                    'left'       => '0',                                           
                ),              
            ),
                      array(
                'id' => 'item-g-padding',
                'type' => 'border',
                'title' => 'Padding',
                'color'=>false,
                'style'=>false,
                'default' => array(
                    'color'      => 'black',                                              
                    'style'      => 'solid',
                    'top'        => '10',
                    'right'      => '10',
                    'bottom'     => '10',
                    'left'       => '10',                                           
                ),                 
            ),

                     array(
                        'id' => 'item-g-border',
                        'type' => 'border',
                        'title' => 'Border',
                        'default' => array(
                            'color'      => 'black',                                              
                            'style'      => 'solid',
                            'top'        => '0',
                            'right'      => '0',
                            'bottom'     => '0',
                            'left'       => '0',                                           
                        ),
                 ),

                )),

                 array(
                'id'     => 'list_style',
                'type'   => 'fieldset',
                'title'  => 'List Style',
                'fields' => array(
                        array(
                'id' => 'item-list-g-bg-color',
                'type' => 'color',
                'title' => __('Background ', 'cptbx'),
                'default' => 'white',
            ),
           
             array(
                'id' => 'item-list-g-padding',
                'type' => 'border',
                'title' => 'Padding',
                'color'=>false,
                'style'=>false,  
                'default' => array(
                    'color'      => 'black',                                              
                    'style'      => 'solid',
                    'top'        => '0',
                    'right'      => '0',
                    'bottom'     => '0',
                    'left'       => '0',                                           
                ),                   
            ),
                      array(
                'id' => 'item-list-g-border',
                'type' => 'border',
                'title' => 'Border',       
                'default' => array(
                    'color'      => 'black',                                              
                    'style'      => 'solid',
                    'top'        => '0',
                    'right'      => '0',
                    'bottom'     => '0',
                    'left'       => '0',                                           
                ),
            ),

                )),
          
        ),
    ));

// Create a section
    CSF::createSection($prefix_page_opts, array(
        'title' => 'List Log',
        'icon' => 'fas fa-rocket',
        'fields' => array(
            // A text field
            array(
                'type' => 'content',
                'content' => '

		      <table class="mrv_table_wrap">
		      <thead class="mrv_table_head">
		  <tr>
		    <th>#</th>
		    <th>Item Name</th>
		    <th>Up Vote</th>
		    <th>Down Vote</th>
		    <th>Total Vote</th>
		  </tr>
		  </thead>
		<tbody class="mrv_table_body">
		   ' . $tabel_body . '
		  </tbody>
		</table>',
            ),

        ),
    ));

    $shortcode_box = $prefix_page_opts . '_shortcode_bar';

//
// Create a metabox
    CSF::createMetabox($shortcode_box, array(
        'title' => 'Shortcode',
        'post_type' => 'meta-ranker',
        'context' => 'side', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
    ));
    //
    // Create a section
    CSF::createSection($shortcode_box, array(
        'title' => '',
        'fields' => array(
            //
            // A text field
            array(
                'id' => 'cptb-shortcode-box',
                'type' => 'callback',
                'function' => 'mrv_get_shortcode_field',
            ),

        ),
    ));
    $iframe_box = $prefix_page_opts . '_iframe_bar';
    //
    // Create a metabox
    CSF::createMetabox($iframe_box, array(
        'title' => 'Post viewer',
        'post_type' => 'meta-ranker',
        'context' => 'advanced', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
    ));
    //
    // Create a section
    CSF::createSection($iframe_box, array(
        'title' => '',
        'fields' => array(
            //
            // A text field
            array(
                'id' => 'cptb-iframe-box',
                'type' => 'callback',
                'function' => function () {
                    // Assume that $id has already been set to the post ID you want to link to.
                    $permalink = get_post_permalink($id);
                    ?>
                    <div id="iframe-container">
                        <iframe src="<?php echo $permalink; ?>" id="dynamic-iframe"></iframe>
                    </div>
                    <script>
                        jQuery(document).ready(function ($) {
                            function adjustIframeSize() {
                                if ($(window).width() <= 1280) {
                                    $('#dynamic-iframe').css({
                                        'width': '460px',
                                        'height': '500px'
                                    });
                                } else {
                                    var windowWidth = $(window).width() * 0.55;
                                    var windowHeight = $(window).height() * 0.55;
                                    $('#dynamic-iframe').css({
                                        'width': windowWidth + 'px',
                                        'height': windowHeight + 'px'
                                    });
                                }
                            }

                            adjustIframeSize();

                            $(window).resize(function () {
                                adjustIframeSize();
                            });
                        });
                    </script>
                    <?php
                },
            ),

        ),
    ));
    // $copy_styles = $prefix_page_opts . '_copy_styles';
    // CSF::createMetabox($copy_styles, array(
    //     'title' => 'Copy Styles',
    //     'post_type' => 'meta-ranker',
    //     'context' => 'advanced', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
    // ));
    // CSF::createSection($copy_styles, array(
    //     'title' => '',
    //     'fields' => array(
    //         //
    //         // A text field
    //         array(
    //             'id' => 'cptb-iframe-box',
    //             'type' => 'callback',
    //             'function' => 'mrv_get_all_styles',
    //         ),

    //     ),
    // ));



  

endif;

}
/**
 * Create HTML for shortcode input field
 */
function mrv_get_shortcode_field()
{
    $message="";
    // create timeline builder metabox
    if (!empty($_GET['post']) && get_post_type(sanitize_text_field($_GET['post'])) == 'meta-ranker') {
        $id = sanitize_text_field($_GET['post']);
        ?>
       <input style='width:100%;padding:0 2px 0 2px;text-align:center;' type='text' value='[meta-ranker id="<?php echo esc_attr($id)?>"]' readonly onClick='this.select();'>
        <button id='mrv-copy-shortcode' class='button button-primary button-small' style='margin-top:5px;float:right;'>Copy</button>
    <?php
    } else {
        ?>
   <p>Publish this post to generate the shortcode.</p>
   <?php
    }

   
}

function mrv_get_cpt()
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
function mrv_clean_sc($string)
{
    $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}


/**
 * Get all the registered image sizes along with their dimensions
 *
 * @global array $_wp_additional_image_sizes
 *
 * @return array $image_sizes The image sizes
 */
function mrv_available_featured_image_size()
{
    global $_wp_additional_image_sizes;

    $default_image_sizes = get_intermediate_image_sizes();

    foreach ($default_image_sizes as $size) {
        $width = intval(get_option("{$size}_size_w"));
        $height = intval(get_option("{$size}_size_h"));

        $resolution = ($width != 0 && $height != 0) ? '(' . $width . 'X' . $height . ')' : '';
        $image_sizes[$size] = str_replace('_', ' ', ucwords($size)) . ' ' . $resolution;

    }

    $image_sizes['full'] = 'Full';
    $image_sizes['custom_size'] = 'Custom Size';
    return $image_sizes;
}