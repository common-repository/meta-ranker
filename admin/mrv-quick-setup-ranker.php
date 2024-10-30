<?php

// Control core classes for avoid errors
if (class_exists('CSF')) {

    //
    // Set a unique slug-like ID
    $prefix = 'mrv_quick_setup';

    /**
     *
     * @menu_parent argument examples.
     *
     * For Dashboard: 'index.php'
     * For Posts: 'edit.php'
     * For Media: 'upload.php'
     * For Pages: 'edit.php?post_type=page'
     * For Comments: 'edit-comments.php'
     * For Custom Post Types: 'edit.php?post_type=your_post_type'
     * For Appearance: 'themes.php'
     * For Plugins: 'plugins.php'
     * For Users: 'users.php'
     * For Tools: 'tools.php'
     * For Settings: 'options-general.php'
     *
     */
    CSF::createOptions($prefix, array(
        'menu_title' => 'Quick Setup',
        'menu_slug' => 'meta-ranker-settings',
        'menu_type' => 'submenu',
        'menu_parent' => 'edit.php?post_type=meta-ranker',
         'framework_title'         => 'Quick Setup',
    'menu_capability'         => 'manage_options',
    'menu_icon'               => null,
    'menu_position'           => null,
    'menu_hidden'             => true,
    'show_reset_all'          => false,
    'show_reset_section'      => false,
    'show_footer'             => false,
    'show_search'             => false,
    'show_all_options'        => false,


    // theme and wrapper classname
    'nav'                     => 'inline',
    'theme'                   => 'light',
    'class'                   => '',
    ));

    
// Create a section
CSF::createSection($prefix, array(
    'title' => 'Settings',
    'fields' => array(

        // A textarea field
        array(
            'id' => 'mrv-infura-key',
            'type' => 'text',
            'title' => 'Infura Project Id',
             'desc' => __('Get infura project key by visiting given  <a href="https://infura.io/register" target="_blank"> link</a> ', 'mrv'),
        ),

      
                array(
                    'id' => 'supported_wallets',
                    'title' => esc_html__('Enable/Disable Wallets', 'mrv'),
                    'type' => 'fieldset',
                    'fields' => array(
                        array(
                            'id' => 'metamask_wallet',
                            'title' => esc_html__('Enable MetaMask Wallet', 'mrv'),
                            'type' => 'switcher',                            
                            'default' => true,
                        ),
                        array(
                            'id' => 'binance_wallet',
                            'title' => esc_html__('Enable Binance Wallet', 'mrv'),
                            'type' => 'switcher',
                            'default' => true,
                        ),
                        // array(
                        //     'id' => 'trust_wallet',
                        //     'title' => esc_html__('Enable Trust Wallet', 'mrv'),
                        //     'type' => 'switcher',
                        //     'default' => true,
                        // ),
                        array(
                            'id' => 'wallet_connect',
                            'title' => esc_html__('Enable Wallet Connect', 'mrv'),
                            'type' => 'switcher',
                            'default' => true,
                        ),

                    ),

                ),

    ),
));


}
