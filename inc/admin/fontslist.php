<?php
/*
* This function outputs content of Manage Fonts admin page.
* uses wp list table and its extended class afc_fontsList
*/
function afc_managefonts(){
    ?>
	<div class="afc-main afc-table">
    <?php
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	$fontToEdit = '';
	$afcFonts = new afcfonts();
	if( get_option('afc_font_for_edit') == '' ){
		if( isset( $_GET['id'] ) && $_GET['id'] != '' ){
			$dbFonts = $afcFonts->getFonts( 'id', array( $_GET['id'] ) );
			if( is_array( $dbFonts ) )
				foreach( $dbFonts as $key )
					if( $key['id'] == $_GET['id'] ){
						$fontToEdit = $key;
						update_option('afc_font_for_edit', $key );
						break;
					}
		}
	}
	else{
		$fontToEdit = get_option('afc_font_for_edit');
	}
	if( is_array( $fontToEdit ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ){
		afc_editfont();
	}
	else{
		echo '<h2 class="nav-tab-wrapper">';
		$current = 'afc_managefonts';
		$tabs = afcStrings::getString( 'manageFonts' );
		foreach( $tabs as $tab => $name ){
			$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";

		}
		echo '</h2>';
		update_option('afc_font_for_edit', '' );
        global $afcFontListTable;
        if( isset( $_POST['s'] ) ){
            $afcFontListTable->prepare_items( $_POST['s'] );
        }
        else{
            $afcFontListTable->prepare_items();
        }
		?>
		<form method="post">
			<input type="hidden" name="page" value="afc_managefonts">
		<?php
        $afcFontListTable->search_box('Search', 'search_id');
        $afcFontListTable->display(); 
        ?>
		</form></div>
        <?php
	}

}

/*
* This function sets default data for our table
* we call this function when wp is loading admin menu items in main plugin file
*/
function fonts_table_options(){
	global $afcFontListTable;
	$option = 'per_page';
	$args = array(
		'label' => 'Fonts',
		'default' => 10,
		'option' => 'fonts_per_page'
		);
	add_screen_option( $option, $args );
	$afcFontListTable = new afc_fontsList();
}