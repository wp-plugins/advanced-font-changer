<?php
/*
* This function outputs content of Manage Selectors admin page.
* uses wp list table and its extended class afc_selectorsList
*/
function afc_manageselectors(){
	echo '<div class="afc-main afc-table">';
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	$afc_selectorToEdit = '';
	$afcSelectors = new afcselectors();
	if( get_option('afc_selector_for_edit') == '' ){
		if( isset( $_GET['id'] ) && $_GET['id'] != '' ){
			$dbSelectors = $afcSelectors->getelems( 'id', array( $_GET['id'] ) );
			if( is_array( $dbSelectors ) )
				foreach( $dbSelectors as $key )
					if( $key['id'] == $_GET['id'] ){
						$afc_selectorToEdit = $key;
						update_option('afc_selector_for_edit', $key );
						break;
					}
		}
	}
	else{
		$afc_selectorToEdit = get_option('afc_selector_for_edit');
	}
	if( is_array( $afc_selectorToEdit ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ){
		afc_editselector();
	}
	else{
		echo '<h2 class="nav-tab-wrapper">';
		$current = 'afc_manageselectors';
		$tabs = afcStrings::getString( 'manageSelectors' );
		foreach( $tabs as $tab => $name ){
			$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";
		}
		echo '</h2>';
	
		update_option('afc_selector_for_edit', '' );
		global $afcSelectorsListTable;
        if( isset( $_POST['s'] ) ){
            $afcSelectorsListTable->prepare_items( $_POST['s'] );
        }
        else{
            $afcSelectorsListTable->prepare_items();
        }
		?>
		<form method="post">
			<input type="hidden" name="page" value="afc_manageselectors">
		<?php
        $afcSelectorsListTable->search_box('Search', 'search_id');
		

		$afcSelectorsListTable->display(); 
		echo '</form></div>';
		
	}
}

/*
* This function sets default data for our table
* we call this function when wp is loading admin menu items in main plugin file
*/
function selectors_table_options(){
	global $afcSelectorsListTable;
	$option = 'per_page';
	$args = array(
		'label' => 'Fonts',
		'default' => 15,
		'option' => 'selectors_per_page'
		);
	add_screen_option( $option, $args );
	$afcSelectorsListTable = new afc_selectorsList();
}
?>