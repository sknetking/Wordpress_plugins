<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

 class Custom_Table_List extends WP_List_Table {
    // Define the constructor to set up the list table
    public function __construct() {
        parent::__construct(array(
            'singular' => 'highlighted_text',
            'plural'   => 'highlighted_texts',
            'ajax'     => false,
        ));
   }

    // Define the columns to be displayed in the table
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID'),
            'ip_address' => __('IP Address'),
            'post_id' => __('Post ID'),
            'selected_text' => __('Selected Text'),
        );
    }
   /*array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'post_id'    => array( 'Post ID', true ),
		);

		return $sortable_columns;
	}
    // Define the data to be displayed in the table
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'highlighted_texts';
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($this->get_columns(), $sortable, array());
    
        // Process bulk actions, if any
        $this->process_bulk_action();

        
        // Get the current page number
        $current_page = $this->get_pagenum();
    
        // Define the number of items per page
        $per_page =15;
    
        // Fetch the data from the custom table with pagination
        $offset = ($current_page - 1) * $per_page;
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name LIMIT %d, %d", $offset, $per_page),
            ARRAY_A
        );
    
        // Total items count for pagination
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
       
       // $this->display();
        // Set the items and pagination properties
        $this->items = $results;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));
    }
    

    // Define the content of each column for each row
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    // Define the checkbox column
    public function column_cb($item) {
        return '<input type="checkbox" name="highlighted_texts[]" value="' . esc_attr($item['id']) . '" />';
    }

    // Add the bulk delete action
    public function get_bulk_actions() {
        return array(
            'delete' => 'Delete',
        );
    }

    // Process the bulk action
    public function process_bulk_action() {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'highlighted_texts';
    
        // Detect the bulk action
        $action = $this->current_action();
    
        if ('delete' === $this->current_action() && isset($_REQUEST['_wpnonce'])) {
            check_admin_referer('bulk-action-nonce');
            $ids = isset($_REQUEST['highlighted_texts']) ? $_REQUEST['highlighted_texts'] : array();
    
            if (!empty($ids) && is_array($ids)) {
                foreach ($ids as $id) {
                    $wpdb->delete($table_name, array('id' => $id), array('%d'));
                }
            }
        }
    }

    public function display() {
        $this->display_tablenav('top');
        ?>
        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <thead>
                <?php $this->print_column_headers(); ?>
            </thead>
            <tbody id="the-list">
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>
            <tfoot>
                <?php $this->print_column_headers('footer'); ?>
            </tfoot>
        </table>
        <?php
        $this->display_tablenav('bottom');
    }
// Function to display the custom table
       
}