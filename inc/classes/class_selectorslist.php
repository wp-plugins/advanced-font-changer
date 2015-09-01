<?php
/**
* This class is extended from a local copy of WP_List_Table. It is for generating a table of available selectors.
* Please see wp_list_table docs in wordpress.org for more information.
*/
class afc_selectorsList extends AFC_WP_List_Table {
	protected $message = null;
	protected $type = null;

	/**
     * Class constructor
	 */
	function __construct(){
		global $status, $page;
		parent::__construct( array(
			'singular'  => __( 'Selector', 'afc_textdomain' ),     //singular name of the listed records
			'plural'    => __( 'Selectors', 'afc_textdomain' ),   //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
			)
		);

		add_action( 'admin_head', array( &$this, 'admin_header' ) ); 

    }

	/**
     * Adds styles to admin head
	 */
	function admin_header() {
		$page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		if( 'afc_manageselectors' != $page )
			return;
		echo '<style type="text/css">';
		echo '.wp-list-table .column-selectorName { width: 25%; }';
		echo '.wp-list-table .column-pageType { width: 25%; }';
		echo '.wp-list-table .column-properties { width: 50%; }';
		echo '</style>';
	}
	
	/**
     * Message to be shown when no selectors exists
     */
	function no_items() {
		_e( 'No Selector Found.' );
	}
	
	/**
     * Column default
     * @param array $item 
     * @param string $column_name 
     * @return mixed
     */
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'selectorName':
			case 'pageType':
			case 'properties':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	
	/**
     * Gets columns names
     * @return array
     */
	function get_columns(){
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'selectorName'  => __( 'Selector', 'afc_textdomain' ),
			'pageType'      => __( 'PageType', 'afc_textdomain' ),
			'properties'    => __( 'Properties', 'afc_textdomain' )
		);
		return $columns;
	}
	
	/**
     * Sorts table items
     * @param array $a 
     * @param array $b 
     * @return int or double
     */
	function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'fontName';
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'dec';
		$result = strcmp( $a[$orderby], $b[$orderby] );
		return ( $order === 'dec' ) ? $result : -$result;
	}
	
    /**
     * Column selectorname special contents
     * @param array $item 
     * @return string
     */
	function column_selectorName($item){
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&action=%s&id=%d">Edit</a>',$_REQUEST['page'], 'edit', $item['id'] )
		);

		return sprintf( '%1$s %2$s', $item['selectorName'], $this->row_actions($actions) );
	}
	
	/**
     * Column checkbox contents
     * @param array $item 
     * @return string
     */
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="selectors[]" value="%s" />',  $item['id']
		);    
	}
	
	/**
     * Table bulk actions
     * @return array
     */
	function get_bulk_actions() {
		$actions = array(
		'delete'    => 'Delete'
		);
		return $actions;
	}
	
	/**
     * Processes table bulk actions
     */
	function process_bulk_action() {

		if( 'delete' === $this->current_action() ) {
			// security check
			if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
				$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				$action = 'bulk-' . $this->_args['plural'];
				if ( !wp_verify_nonce( $nonce, $action ) )
					wp_die( 'Security problem occured.' );
			}

			$afcSelectors = new afcselectors();
			if( isset( $_POST['selectors'] ) && is_array( $_POST['selectors'] ) ){
				$delSelectors = $afcSelectors->getelems( 'id', $_POST['selectors'] );
				$afcSelectors->updateElems( 'remove', $delSelectors );
				$this->type = 'updated';
				$this->message = count( $_POST['selectors'] ) . __(' Selector\'s has been deleted. ', 'afc_textdomain');
			}
			else{
				$this->type = 'error';
				$this->message =  __(' Unable to access selectors array. ', 'afc_textdomain');
			}
		}

    }
	
	/**
     * Prepares items
     */
	function prepare_items( $search = NULL ) {
		$this->process_bulk_action();
		$this->show_message();
		$perPage = 15;
		$currentPage = $this->get_pagenum();
		$afcSelectors = new afcselectors();
        if( $search != NULL && trim($search) != '' ){
            $totalItems = $afcSelectors->getCount( true, $search );
            $result = $afcSelectors->getSearchedRows( $search, $perPage, ( $currentPage-1 )* $perPage );
        }
        else{
            $totalItems = $afcSelectors->getCount();
            $result = $afcSelectors->getLimitedRows( $perPage, ( $currentPage-1 )* $perPage );
        }
		$this->found_data = $afcSelectors->implodeIt( $result );

		$this->set_pagination_args( array(
			'total_items' => $totalItems,                  //Calculate the total number of items
			'per_page'    => $perPage                      //Determine how many items to show on a page
			)
		);
		$this->items = $this->found_data;
	}
	
	/**
     * Shows list of errors
     */
	function show_message(){
		if($this->message != null && $this->type != null )
			echo '<div id="setting-error-afc" class="' . $this->type . ' settings-error"><p><strong>' . $this->message . '</strong></p></div>';
	}

}
?>