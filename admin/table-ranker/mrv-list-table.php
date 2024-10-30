<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Mrv_Rank_list extends WP_List_Table
{

    public function get_columns()
    {
        $columns = array(

            'id' => '#',
            'list_title' => __("List Title", "cdbbc"),
            'list_item' => __("Item", "cdbbc"),
            'vote' => __("Vote", "cdbbc"),
            'wallet' => __("User Address", "cdbbc"),
            'last_updated' => __("Last Updated", "cdbbc"),

        );
        return $columns;
    }

    public function prepare_items()
    {
        global $wpdb, $_wp_column_headers;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $query = 'SELECT * FROM ' . RANKER_TABLE;

        $user_search_keyword = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

        if (isset($user_search_keyword) && !empty($user_search_keyword)) {
            $query .= ' where ( list_name LIKE "%' . $user_search_keyword . '%" OR list_item LIKE "%' . $user_search_keyword . '%" OR list_id LIKE "%' . $user_search_keyword . '%") ';
        }

        // Ordering parameters
        $orderby = !empty($_REQUEST["orderby"]) ? esc_sql($_REQUEST["orderby"]) : 'last_updated';
        $order = !empty($_REQUEST["order"]) ? esc_sql($_REQUEST["order"]) : 'DESC';
        if (!empty($orderby) & !empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }

        // Pagination parameters
        $totalitems = $wpdb->query($query);
        $perpage = 10;
        if (!is_numeric($perpage) || empty($perpage)) {
            $perpage = 10;
        }

        $paged = !empty($_REQUEST["paged"]) ? esc_sql($_REQUEST["paged"]) : false;

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }
        $totalpages = ceil($totalitems / $perpage);

        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        // Register the pagination & build link
        $this->set_pagination_args(
            array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page" => $perpage,
            )
        );

        // Get feedback data from database
        $this->items = $wpdb->get_results($query);

    }

    public function column_default($item, $column_name)
    {

        switch ($column_name) {

            case 'id':
                return $item->id;
            case 'list_title':
                return $item->list_name;
            case 'list_item':
                return $item->list_item;
            case 'vote':
                return ($item->vote_type == "upvote") ? '+1' : '-1';
            case 'wallet':
                return $item->wallet_address;
            case 'last_updated':
                return $this->timeAgo($item->last_updated);
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'last_updated' => array('last_updated', true),
        );
        return $sortable_columns;
    }

    public function timeAgo($time_ago)
    {
        $time_ago = strtotime($time_ago) ? strtotime($time_ago) : $time_ago;
        $time = time() - $time_ago + 7200;


        if ($time < 60) {
            // Seconds
            return ($time == 1) ? '1 second ago' : abs($time) . ' seconds ago';
        } elseif ($time < 3600) {
            // Minutes
            $minutes = round($time / 60);
            return ($minutes == 1) ? '1 minute ago' : abs($minutes) . ' minutes ago';
        } elseif ($time < 86400) {
            // Hours
            $hours = round($time / 3600);
            return ($hours == 1) ? '1 hour ago' : abs($hours) . ' hours ago';
        } else {
            // Days or more
            return (round($time / 86400) == 1) ? date_i18n('M j, Y', $time_ago) : date_i18n('M j, Y', $time_ago);
        }
    }


}