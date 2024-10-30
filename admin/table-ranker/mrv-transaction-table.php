<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('MRV_log_TABLE')) {
    class MRV_log_TABLE
    {
        public function __construct()
        {

        }

        //Transaction table callback

        public static function mrv_log_table()
        {
            $lists_table = new Mrv_Rank_list();
            echo '<div class="wrap"><h2>' . __("Votes Log", "mrv") . '</h2>';

            $lists_table->prepare_items();
            ?>
            <form method="post">
        <?php
$lists_table->search_box('search', 'search_id');
            ?>
        </form>
            <?php
$lists_table->display();

            echo '</div>';

        }

    }

}
