<?php

class MRV_Database
{

    /**
     * Get things started
     *
     * @access  public
     * @since   1.0
     */
    public function __construct()
    {

        global $wpdb;

        $this->table_name =  RANKER_TABLE;
        $this->primary_key = 'id';
        $this->version = '1.0';

    }
    //Insert voter data
    public function insert($transactions)
    {
        if (is_array($transactions) && count($transactions) >= 1) {

            $result = $this->wp_insert_rows($transactions, $this->table_name, true);
            return $result;
        }
    }

    public function wp_insert_rows($row_arrays, $wp_table_name, $update = false, $primary_key = null)
    {
        global $wpdb;
        $wp_table_name = esc_sql($wp_table_name);

        $selectQuery = "SELECT * FROM {$wp_table_name} WHERE list_id = %s AND wallet_address = %s";
        $selectResult = $wpdb->get_row($wpdb->prepare($selectQuery, $row_arrays['list_id'], $row_arrays['wallet_address']));

        if ($selectResult) {
            // Matching record found, perform an update
            $updateQuery = "UPDATE {$wp_table_name} SET ";
            $updateColumns = array();
            $updateValues = array();

            foreach ($row_arrays as $key => $value) {
                $updateColumns[] = $key . " = %s";
                $updateValues[] = $value;
            }

            $updateQuery .= implode(", ", $updateColumns);
            $updateQuery .= " WHERE list_id = %s AND wallet_address = %s";

            $updateValues[] = $row_arrays['list_id'];
            $updateValues[] = $row_arrays['wallet_address'];

            $sql = $wpdb->prepare($updateQuery, $updateValues);
            if ($wpdb->query($sql)) {
                return true;
            } else {
                return false;
            }

        } else {
            // No matching record found, perform an insert
            $query = "INSERT INTO {$wp_table_name} (";

            $columns = array();
            $values = array();

            foreach ($row_arrays as $key => $value) {
                $columns[] = $key;
                $values[] = $value;
            }

            $query .= implode(", ", $columns);
            $query .= ") VALUES (";
            $query .= implode(", ", array_fill(0, count($values), "%s"));
            $query .= ")";
            $sql = $wpdb->prepare($query, $values);
            if ($wpdb->query($sql)) {
                $ranker_session_id = $wpdb->insert_id;

                return array("success" => true, "data" => $ranker_session_id);
            } else {
                return array("success" => false, "data" => null);
            }
        }
    }
    //Fetch List with not sent data
    public function get_list()
    {
        global $wpdb;
        $list = $wpdb->get_results("SELECT * FROM $this->table_name WHERE `data_status`='not_sent'", ARRAY_A);

        return $list;

    }
    //Check user if alredy voted
    public function check_alredy_voted_list($list_id, $user)
    {
        global $wpdb;
        $list_id = (int) $list_id;
        $list = $wpdb->get_results("SELECT * FROM $this->table_name WHERE `list_id`=$list_id AND `wallet_address`='$user'", ARRAY_A);

        return $list;

    }
    //Check user ip to show votes after voting

    public function check_user_ip($list_id, $ip)
    {
        global $wpdb;
        $list_id = (int) $list_id;
        $list = $wpdb->get_results("SELECT * FROM $this->table_name WHERE `list_id`=$list_id AND `ip`='$ip'", ARRAY_A);

        return $list;

    }
    //Update list after sending the data
    public function update_list()
    {
        global $wpdb;
        $list = $wpdb->get_results("UPDATE $this->table_name  SET data_status = 'sent' WHERE `data_status`='not_sent'");

        return $list;

    }
    //Create Table
    public function create_table()
    {

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        //IF NOT EXISTS - condition not required

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
        list_name varchar(100) NOT NULL,
        list_item varchar(100) NOT NULL,
        list_id bigint(20) NOT NULL,
        up_vote bigint(20) NOT NULL,
        down_vote bigint(20) NOT NULL,
        total_vote bigint(20) NOT NULL,
        wallet_address varchar(100) NOT NULL,
        vote_type varchar(50) NOT NULL,
        ip varchar(100) NOT NULL,
        balance VARCHAR(32) NOT NULL,
        wallet_type varchar(50) NOT NULL,
        link varchar(150) NOT NULL,
        agent varchar(150) NOT NULL,
        data_status varchar(20) NOT NULL DEFAULT 'not_sent',
        last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	    ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta($sql);
        $wpdb->query('CREATE TABLE IF NOT EXISTS meta_wallet_connections (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			plugin_name VARCHAR(255) NOT NULL,
			session_table VARCHAR(255) NOT NULL,
			session_id INT NOT NULL,
			wallet_address VARCHAR(126) NOT NULL,
			ticker VARCHAR(16) NOT NULL,
			wallet_type VARCHAR(16) NOT NULL
		)');

        update_option($this->table_name . '_db_version', $this->version);
    }

    /**
     * Remove table linked to this database class file
     */
    public function drop_table()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $this->table_name);
    }

}