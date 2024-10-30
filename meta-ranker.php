<?php
/**
 * Plugin Name:Meta Ranker
 * Description:Meta Ranker
 * Author: adastracrypto.com
 * Author URI: https://adastracrypto.com
 * Version: 1.3.1
 * License: GPL2
 * Text Domain: mrv
 * Domain Path: /languages
 *
 * @package MetaRanker
 */

/*

 */

if (!defined('ABSPATH')) {
    exit;
}
define('MRV_VERSION', '1.3.1');
define('MRV_FILE', __FILE__);
define('MRV_PATH', plugin_dir_path(MRV_FILE));
define('MRV_URL', plugin_dir_url(MRV_FILE));
define('RANKER_PLUGIN', "meta-ranker");
define('RANKER_TABLE', "mrv_rank_logs");


//for testing locally comment out the lines below
// add_filter( 'wp_headers', 'disable_cors' );

// function disable_cors( $headers ) {
//     $headers['Access-Control-Allow-Origin'] = '*';
//     $headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS';
//     $headers['Access-Control-Allow-Headers'] = 'Content-Type';
//     return $headers;
// }
/*** MRV_final_class main class  */
if (!class_exists('MRV_final_class')) {
    final class MRV_final_class
    {

        /**
         * The unique instance of the plugin.
         *
         */
        private static $instance;

        /**
         * Gets an instance of our plugin.
         *
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct()
        {
            /*** Installation and uninstallation hooks */
            //  register_activation_hook(MRV_FILE, array($this, 'activate'));
            //  register_deactivation_hook(MRV_FILE, array($this, 'deactivate'));

        }

        // register all hooks
        public function registers()
        {
            register_activation_hook(MRV_FILE, array($this, 'activate'));
            register_deactivation_hook(MRV_FILE, array($this, 'deactivate'));
            add_action('admin_init', array($this, 'mrv_do_activation_redirect'));
            add_action('plugins_loaded', array($this, 'load_files'));
            add_action('admin_menu', array($this, 'create_submenu_pages'), 10);
            //add_action('plugins_loaded', array($this, 'register_post_type'), 50);

            // Update the columns shown on the custom post type edit.php view - so we also have custom columns
            add_filter('manage_meta-ranker_posts_columns', array($this, 'cptb_custom_columns'));
            // this fills in the columns that were created with each individual post's value
            add_action('manage_meta-ranker_posts_custom_column', array($this, 'cptb_custom_columns_data'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'enque_admin_scripts'));
            add_action('wp_ajax_mrv_save_votes', 'mrv_save_votes');
            add_action('wp_ajax_nopriv_mrv_save_votes', 'mrv_save_votes');
            add_action('wp_ajax_mrv_skip_wallet', 'mrv_skip_wallet');
            add_action('wp_ajax_nopriv_mrv_skip_wallet', 'mrv_skip_wallet');
            add_action('wp_ajax_mrv_check_voted_alredy', 'mrv_check_voted_alredy');
            add_action('wp_ajax_mrv_generate_nonce', 'mrv_generate_nonce');
            add_action('wp_ajax_nopriv_mrv_check_voted_alredy', 'mrv_check_voted_alredy');
            add_action('wp_ajax_nopriv_mrv_generate_nonce', 'mrv_generate_nonce');
            add_filter('single_template', array($this, 'mrv_custom_template'), 99);
            add_action('admin_notices', array($this, 'mrv_admin_notice_warn'));

        }

        /**
         * Create a custom template override for meta ranker post
         * @param string $original This is the default template path provided by current theme
         * @return string $template Return a custom template for specific (meta ranker) post
         *  -- OR --
         * @return string $original Return the original template path for the rest of the post(s)
         */
        public function mrv_custom_template($original)
        {
            global $post;

            if (is_object($post) && $post->post_type == 'meta-ranker') {
                $base_name = 'single-meta-ranker-template.php';
                $template = MRV_PATH . '/includes/' . $base_name;
                return $template;
            }

            return $original;
        }

        public function create_submenu_pages()
        {
            add_submenu_page('edit.php?post_type=meta-ranker', __('Votes Log', 'cptbx'), __('Votes Log', 'cptbx'), 'manage_options', 'meta-ranker-logs', array('MRV_log_TABLE', 'mrv_log_table'), 10);
        }

        public function enque_admin_scripts($hook)
        {

            wp_enqueue_script('mrv-admin', MRV_URL . '/assets/js/mrv-admin.js', array('jquery'), MRV_VERSION);
            wp_enqueue_style('mrv-admin-style', MRV_URL . '/assets/css/mrv-admin.css', array(), MRV_VERSION);

        }

        public function mrv_admin_notice_warn()
        {

            $settings = get_option('mrv_option_settings');
            $quickSetup = get_option('mrv_quick_setup');
            $supported_wallets = isset($quickSetup['supported_wallets']) ? $quickSetup['supported_wallets'] : "";
            $wallet_connect = (isset($supported_wallets['wallet_connect']) && !empty($supported_wallets['wallet_connect'])) ? $supported_wallets['wallet_connect'] : "1";
            if (empty($quickSetup['mrv-infura-key']) && $wallet_connect == "1") {
                echo '<div class="notice notice-error is-dismissible">
        <p>Important:Please enter an infura project id for Meta Ranker Plugin  to work WalletConnect <a style="font-weight:bold" href="' . esc_url(get_admin_url(null, 'edit.php?post_type=meta-ranker&page=meta-ranker-settings#tab=settings')) . '">Link</a></p>
        </div>';
            }
        }

        /**
         *  Set the value(s) in the column. This function is specifically for meta ranker post
         *
         * @param string $column Name of the column
         * @param int $id WP_post id of the current post in the row
         *
         */
        public function cptb_custom_columns_data($column, $id)
        {
            switch ($column) {
                case 'shortcode':
                    echo '<code>[meta-ranker id="' . esc_attr($id) . '"]</code>';
                    break;
                case 'list-preview':
                    if (get_post_status($id) != 'trash') {
                        echo '<a href="' . get_post_permalink($id) . '" target="_new">Preview</a>';
                    }
                    break;
                case 'voteLog':
                    echo '<a href="' . get_admin_url() . 'edit.php?post_type=meta-ranker&page=meta-ranker-logs" target="_self">Vote Log</a>';
                    break;

            }
        }
        /**
         * Create custom columns for meta ranker post
         * @param array $column An array contains all the name of columns
         * @return array $column Modified array of the received columns
         *
         */
        public function cptb_custom_columns($columns)
        {

            $date = $columns['date'];
            unset($columns['date']);
            $columns['shortcode'] = __('Shortcode', 'cptbx');
            $columns['voteLog'] = __('Vote Log', 'cptbx');
            $columns['list-preview'] = __('List Preview', 'cptbx');

            $columns['date'] = $date;

            return $columns;
        }

        /*** Load required files */
        public function load_files()
        {
            require_once MRV_PATH . 'includes/codestar-framework-ranker/codestar-framework.php';
            require_once MRV_PATH . 'admin/post-type-ranker/mrv-post-type.php';
            require_once MRV_PATH . 'admin/mrv-post-type-settings-ranker.php';
            require_once MRV_PATH . 'admin/mrv-quick-setup-ranker.php';
            require_once MRV_PATH . 'admin/mrv-option-settings-ranker.php';


            require_once MRV_PATH . 'includes/db/mrv-db.php';
            if ($this->isHttps() != false) {
                require_once MRV_PATH . 'includes/JWTAuth/include-jwt.php';
                require_once MRV_PATH . 'includes/plugin-activation/class-rest-api.php';
            }

            require_once MRV_PATH . 'includes/mrv-shortcode.php';
            require_once MRV_PATH . 'includes/functions.php';
            require_once MRV_PATH . 'admin/table-ranker/mrv-transaction-table.php';
            require_once MRV_PATH . 'admin/table-ranker/mrv-list-table.php';
            require_once MRV_PATH . 'admin/mrv-copy-styles.php';
            if ($this->isHttps() != false) {
                require_once MRV_PATH . 'includes/plugin-activation/class-license-manager.php';
                require_once MRV_PATH . 'includes/plugin-activation/activate_site.php';
                require_once MRV_PATH . 'includes/plugin-activation/hooks.php';

            }

        }

        public function isHttps()
        {
            if (array_key_exists("HTTPS", $_SERVER) && 'on' === $_SERVER["HTTPS"]) {
                return true;
            }
            if (array_key_exists("SERVER_PORT", $_SERVER) && 443 === (int) $_SERVER["SERVER_PORT"]) {
                return true;
            }
            if (array_key_exists("HTTP_X_FORWARDED_SSL", $_SERVER) && 'on' === $_SERVER["HTTP_X_FORWARDED_SSL"]) {
                return true;
            }
            if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && 'https' === $_SERVER["HTTP_X_FORWARDED_PROTO"]) {
                return true;
            }
            return false;
        }

        // set settings on plugin activation
        public function activate()
        {
            require_once MRV_PATH . 'includes/db/mrv-db.php';
            add_option('mrv_do_activation_redirect', true);
            $db = new MRV_Database();
            $db->create_table();
            if ($this->isHttps() != false) {
                require_once MRV_PATH . 'includes/JWTAuth/include-jwt.php';
                require_once MRV_PATH . 'includes/plugin-activation/class-rest-api.php';
                MetaRankerRestApi::setupKeypair();
                if (!wp_next_scheduled('metaranker_sync_data')) {
                    $time = strtotime('12:00:00') + rand(0, 60);
                    if (!wp_schedule_event($time, 'hourly', 'metaranker_sync_data')) {
                        throw new Exception(__('Failed to connect to remote server!', 'meta-ranker'));
                    }
                }
            }

        }

        public function mrv_do_activation_redirect()
        {
            $activated = get_option('metaRankerActivated');

            if (get_option('mrv_do_activation_redirect', false) && !$activated) {
                delete_option('mrv_do_activation_redirect');
                if (!isset($_GET['activate-multi'])) {
                    wp_redirect(admin_url('edit.php?post_type=meta-ranker&page=metaranker-activation'));
                }
            }
        }
        public static function deactivate()
        {
            //    $db= new MRV_Database();
            //   $db->drop_table();

        }

    }

}
/*** MRV_final_class main class - END */

$cpmwp = MRV_final_class::get_instance();
$cpmwp->registers();