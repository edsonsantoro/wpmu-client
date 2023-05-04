<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/screen.php');
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Show_Clients extends WP_List_Table
{

    protected $table_data;

    public function prepare_items()
    {
        //data
        if (isset($_POST['s'])) {
            $this->table_data = $this->table_data($_POST['s']);
        } else {
            $this->table_data = $this->table_data();
        }

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort($data, array($this, 'sort_data'));

        $perPage = 5;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable, 'client');

        usort($this->table_data, array(&$this, 'usort_reorder'));

        $this->items = $data;
    }

    // To show bulk action dropdown
    protected function get_bulk_actions()
    {
        $actions = array(
            'delete_all'    => __('Delete', 'supporthost-admin-table'),
            'draft_all' => __('Move to Draft', 'supporthost-admin-table')
        );
        return $actions;
    }

    // Sorting function
    private function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'client';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    // add screen options
    function wpmu_screen_options()
    {

        global $wpmu_clients_page;
        global $table;

        $screen = get_current_screen();

        // get out of here if we are not on our settings page
        if (!is_object($screen) || $screen->id != $wpmu_clients_page)
            return;

        $args = array(
            'label' => __('Elements per page', 'supporthost-admin-table'),
            'default' => 2,
            'option' => 'elements_per_page'
        );
        add_screen_option('per_page', $args);

        $table = new Supporthost_List_Table();
    }
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'client'    => 'Cliente',
        );

        return $columns;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['ID']
        );
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    // Define sortable column
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'client'  => array('client', false),
        );
        return $sortable_columns;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data(string $search = '')
    {

        $data = array();

        //do the query of Vehicle Info for all the vehicles available
        $args = array(
            'numberposts'    => -1,
            'post_type'        => 'clients',
            'order'                => 'ASC'
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Do your stuff, e.g.
        $the_query = new \WP_Query($args);

        if ($the_query->have_posts()) {

            while ($the_query->have_posts()) {
                $the_query->the_post();

                $id = get_the_ID();

                $client_field = get_the_title();

                $data[] = [
                    'ID' => $id,
                    'client' => $client_field
                ];
            }
        }

        return $data;
    }

    // Adding action links to column
    function column_client($item)
    {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Editar', 'wpmu-client') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Excluir', 'wpmu-client') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );

        return sprintf('%1$s %2$s', $item['client'], $this->row_actions($actions));
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'client':
                return $item[$column_name];

            default:
                $item;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'client';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }


        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}

add_action('network_admin_menu', 'add_menu_item');

function add_menu_item()
{
    //global $wpmu_clients_page;

    // add settings page
    $wpmu_clients_page = add_menu_page(
        __('Custom Menu Title', 'textdomain'),
        'Clients',
        'manage_network_options',
        'wpmu-clients',
        'init',
        'dashicons-businessman',
        6
    );

    add_action("load-$wpmu_clients_page", "wpmu_screen_options");
}

function init()
{
    $table = new Show_Clients();

    echo '<div class="wrap"><h2>Clientes DRB.MKT</h2>';
    echo '<a href="' . admin_url('post-new.php?post_type=clients') . '" class="button btn-primary">Novo Cliente</a>';
    // Prepare table
    $table->prepare_items();
    // Display table
    $table->search_box('Search', 'all-clients');
    $table->display();
    echo '</div>';
}

// add screen options
function wpmu_screen_options()
{

    global $wpmu_clients_page;
    global $table;

    $screen = get_current_screen();

    // get out of here if we are not on our settings page
    if (!is_object($screen) || $screen->id != $wpmu_clients_page)
        return;

    $args = array(
        'label' => __('Elements per page', 'supporthost-admin-table'),
        'default' => 2,
        'option' => 'elements_per_page'
    );
    add_screen_option('per_page', $args);

    $table = new Supporthost_List_Table();
}

add_filter('set-screen-option', 'test_table_set_option', 10, 3);
function test_table_set_option($status, $option, $value)
{
    return $value;
}

function create_table() {

}