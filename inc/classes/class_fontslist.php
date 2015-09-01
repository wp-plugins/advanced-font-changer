<?php
/**
* This class is extended from a local copy of WP_List_Table. It is for generating a table of available fonts.
* Please see wp_list_table docs in wordpress.org for more information.
*/
class afc_fontsList extends AFC_WP_List_Table {
	protected $message = null;
	protected $type = null;

	/**
     * Class constructor
	 */
	function __construct(){
		parent::__construct( array(
			'singular'  => __( 'font', 'afc_textdomain' ),     //singular name of the listed records
			'plural'    => __( 'fonts', 'afc_textdomain' ),   //plural name of the listed records
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
		if( 'afc_managefonts' != $page )
			return;
		echo '<style type="text/css">';
		echo '.wp-list-table .column-name { width: 30%; }';
		echo '.wp-list-table .column-status { width: 20%; }';
		echo '.wp-list-table .column-metadata { width: 50%; }';
		echo '</style>';
	}

	/**
     * Message to be shown when no selectors exists
	 */
	function no_items() {
		_e( 'No Fonts Found.' );
	}

	/**
     * Column default
	 * @param array $item 
	 * @param string $column_name 
	 * @return mixed
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'name':
			case 'status':
			case 'metadata':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	
	/**
     * Column that must be sorted
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
		'name'  => array( 'name', false ),
		'status' => array( 'status', false )
		);
		return $sortable_columns;
	}

	/**
     * Gets columns names
	 * @return array
	 */
	function get_columns(){
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'name'     => __( 'Font Name', 'afc_textdomain' ),
			'status'   => __( 'Status', 'afc_textdomain' ),
			'metadata'   => __( 'Meta Data', 'afc_textdomain' ),
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
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'status';
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'dec';
		$result = strcmp( $a[$orderby], $b[$orderby] );
		return ( $order === 'dec' ) ? $result : -$result;
	}

	/**
     * Column checkbox contents
	 * @param array $item 
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="font[]" value="%s" />', $item['id']
		);    
	}

	/**
     * Column fontname special contents
	 * @param array $item 
	 * @return string
	 */
	function column_name( $item ){
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&action=%s&id=%d">'. __('Edit', 'afc_textdomain') . '</a>', $_REQUEST['page'], 'edit', $item['id'] )
		);
		if( $item['status'] != 'default' && $item['status'] != 'user' )
			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions($actions) );
		else
			return sprintf( '%1$s', $item['name'] );
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
			if ( isset( $_POST['_wpnonce'] ) && !empty( $_POST['_wpnonce'] ) ) {
				$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				$action = 'bulk-' . $this->_args['plural'];
				if ( !wp_verify_nonce( $nonce, $action ) )
					wp_die( 'Security problem occured.' );
			}
			
			if( isset( $_POST['font'] ) && is_array( $_POST['font'] ) ){
				$afcFonts = new afcfonts();
				$delFonts = $afcFonts->getFonts( 'id', $_POST['font'] );
				$afcFonts->updateFonts( 'remove', $delFonts );
				$delFonNames = array();
				foreach( $delFonts as $font )
					$delFonNames[] = $font['name'];
				$afcSelectors = new afcselectors();
				//Editing selectors that are using this font.
				$mustEditSelectors = $afcSelectors->getElems( 'properties', $delFonNames );
				if( count( $mustEditSelectors ) > 0 ){
					$editedSelectors = array();
					foreach( $mustEditSelectors as $selector ){
						unset($selector['properties']['fontName']);
						$editedSelectors[] = $selector;
					}
					$afcSelectors->addelems( $editedSelectors );
				}
				$this->type = 'updated';
				$this->message = count( $delFonts  ) . __(' Selected fonts has been deleted. ', 'afc_textdomain');
			}
			else{
				$this->type = 'error';
				$this->message =  __('Can not access fonts array. ', 'afc_textdomain');
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
		$afcFonts = new afcfonts();
        if( $search != NULL && trim($search) != '' ){
            $totalItems = $afcFonts->getCount( true, $search );
            $result = $afcFonts->getSearchedRows( $search, $perPage, ( $currentPage-1 )* $perPage );
        }
        else{
            $totalItems = $afcFonts->getCount();
            $result = $afcFonts->getLimitedRows( $perPage, ( $currentPage-1 )* $perPage );
        }
		$this->found_data = $afcFonts->implodeIt( $result );
		
		$this->set_pagination_args( array(
			'total_items' => $totalItems,    //Calculate the total number of items
			'per_page'    => $perPage         //Determine how many items to show on a page
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