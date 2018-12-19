<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (! class_exists('Wk_Mu_Manage_Profile')) {
    /**
     * Manage uploaded profiles
     */
    class Wk_Mu_Manage_Profile extends WP_List_Table
    {

        function __construct()
        {
            parent::__construct(array(
                'singular'  => 'profile_list',
                'plural'    => 'all_profile_list',
                'ajax'      => false
            ));
        }

        public function prepare_items()
        {
            global $wpdb;
            $columns = $this->get_columns();
            $sortable = $this->get_sortable_columns();
            $hidden = $this->get_hidden_columns();
            $this->process_bulk_action();
            $data = ($this->table_data()) ? $this->table_data() : array();
            $totalitems = count($data);
            $user = get_current_user_id();
            $screen = get_current_screen();
            $perpage = $this->get_items_per_page('profile_per_page', 20);
            $this->_column_headers = array( $columns, $hidden, $sortable );
            usort($data, array($this, 'wk_usort_reorder'));
            $totalpages = ceil($totalitems/$perpage);
            $currentPage = $this->get_pagenum();
            $data = array_slice($data, (($currentPage-1) * $perpage), $perpage);

            $this->set_pagination_args(array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page"    => $perpage,
            ));
            $this->items = $data;
        }

        function wk_usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ( $order === 'asc' ) ? $result : -$result; //Send final sort direction to usort
        }

        public function get_hidden_columns()
        {
            return array();
        }

        function column_cb($item)
        {
            return sprintf('<input type="checkbox" id="profile_%s" name="profile[]" value="%s" />', $item['id'], $item['id']);
        }

        function get_columns()
        {
            $columns = array(
                'cb'         => '<input type="checkbox" />', //Render a checkbox instead of text
                'id'         => __('ID'),
                'csv'        => __('CSV'),
                'zip'        => __('ZIP'),
            );
            return $columns;
        }

        public function get_sortable_columns()
        {
            $sortable_columns = array(
                'id'       => array( 'id', true ),
                'csv'     => array( 'csv', true ),
                'zip'     => array( 'zip', true )
            );
            return $sortable_columns;
        }

        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'id':
                case 'csv':
                case 'zip':
                    return $item[$column_name];
                default:
                    return print_r($item, true);
            }
        }

        private function table_data()
        {
            global $wpdb;
            $data = array();

            $wk_data = get_user_meta(get_current_user_id(), 'csv_profile_path', true);

            if ($wk_data) {
                foreach ($wk_data as $key => $value) {
                    $data[] = array(
                        'id'  => ++$key,
                        'csv' => $value['csv'],
                        'zip' => $value['zip']
                    );
                }
            }
            return $data;
        }

        function get_bulk_actions()
        {
            $actions = array(
                'delete'    => 'Delete'
            );
            return $actions;
        }

        function column_id( $item )
        {
            $actions = array(
                'delete'    => sprintf('<a href="?page=manage-mass-upload-profile&action=delete&profile=%d" class="wkmu-confirm-delete-profile">Delete</a>', $item['id']),
            );
            return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions));
        }

        public function process_bulk_action()
        {
            global $wpdb;

            $url = wp_upload_dir();
            $user_folder = wp_get_current_user()->user_login;
            $user_id = get_current_user_id();

            $wk_data = get_user_meta($user_id, 'csv_profile_path', true);

            if ($this->current_action() && $this->current_action() === 'delete') {
                if (isset($_POST['profile']) && is_array($_POST['profile']) && $_POST['profile']) {
                    foreach ($_POST['profile'] as $key) {
                        $key = --$key;
                        $file = $wk_data[$key];
                        $target_file_csv = $url['basedir'].'/' . $user_folder .'/' . $file['csv'];
                        unlink($target_file_csv);
                        unset($wk_data[$key]);
                    }
                    $true = update_user_meta($user_id, 'csv_profile_path', $wk_data);
                    if ($true) {
                        wp_redirect($_SERVER['HTTP_REFERER'] . '&message=success');
                        exit;
                    }
                } else if (isset($_GET['profile'])) {
                    $key = sanitize_key($_GET['profile']);
                    $key = --$key;
                    $file = $wk_data[$key];
                    $target_file_csv = $url['basedir'].'/' . $user_folder .'/' . $file['csv'];
                    unlink($target_file_csv);
                    unset($wk_data[$key]);
                    $true = update_user_meta($user_id, 'csv_profile_path', $wk_data);
                    if ($true) {
                        wp_redirect($_SERVER['HTTP_REFERER'] . '&message=success');
                        exit;
                    }
                }
            }
        }
    }

    $profile_table = new Wk_Mu_Manage_Profile();
    $profile_table->prepare_items();

    ?>

    <div class="wrap">

        <h1 class="wp-heading-inline">Profile Information</h1>

        <?php if (isset($_GET['message']) && $_GET['message'] == 'success') : ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php echo __('Profile(s) deleted successfully.', 'marketplace'); ?>
            </div>
        <?php endif; ?>

        <hr>

        <form method="POST">

            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

            <?php

            $profile_table->display();

            ?>

        </form>

    </div>

    <?php
}
