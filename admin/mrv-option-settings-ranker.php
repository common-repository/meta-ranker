<?php

// Control core classes for avoid errors
if (class_exists('CSF')) {

    //
    // Set a unique slug-like ID
    $prefix = 'mrv_option_settings';

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
        'menu_title' => 'About',
        'menu_slug' => 'meta-ranker-settings',
        'menu_type' => 'submenu',
        'menu_parent' => 'edit.php?post_type=meta-ranker',
         'framework_title'         => 'Welcome To Meta Ranker Plugin',
    'menu_capability'         => 'manage_options',
    'menu_icon'               => null,
    'menu_position'           => null,
    'menu_hidden'             => false,
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

    //
    // Create a section
    CSF::createSection($prefix, array(
        'title' => 'About',
        'fields' => array(

            // A Callback Field Example
            array(
            'type'     => 'callback',
            'function' => 'mrv_about_page',
            ),
        ),
    ));

    //
    // Create a section
    // CSF::createSection($prefix, array(
    //     'title' => 'Getting Started',
    //     'fields' => array(

    //          // A Callback Field Example
    //         array(
    //         'type'     => 'callback',
    //         'function' => 'mrv_getting_start',
    //         ),

    //     ),
    // ));
    // Create a section
CSF::createSection($prefix, array(
    'title' => 'Terms of Use',
    'fields' => array(

        // A Callback Field Example
            array(
            'type'     => 'callback',
            'function' => 'mrv_terms_of_use',
            ),
    ),
));
}
