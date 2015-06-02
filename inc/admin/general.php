<?php
/*
* This function outputs content of General Options admin page.
*/
function afc_general_options() {
	echo '<div class="afc-main">';
	$tabs = array( 'afc_plugin_options' =>  __('General Options', 'afc_textdomain' ), 'afc_managefonts' =>  __('Manage Fonts', 'afc_textdomain' ), 'afc_manageselectors' =>  __('Manage Selectors', 'afc_textdomain' ), 'afc_import_export' =>  __('Import/Export', 'afc_textdomain' ));
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	settings_errors('afc_generalPageSettings');
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_plugin_options';
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=$tab&tab=$tab'>$name</a>";

	}
	echo '</h2>';
?>
	<form action='options.php' method='post'>
		
		<?php
		settings_fields( 'afc_generalPage' );
		do_settings_sections( 'afc_generalPage' );
		submit_button( __('Submit', 'afc_textdomain') );
		?>
		
	</form>
	</div>
<?php
}

add_action( 'admin_init', 'afc_general_settings_init' );
/*
* This function creates a section and its fields using wp settings api
*/
function afc_general_settings_init(  ) { 
	
	register_setting( 'afc_generalPage', 'afc_generalPageSettings', 'afc_validate_generalOptions' );

	add_settings_section(
		'afc_generalPage_section', 
		__( 'Genaral Options', 'afc_textdomain' ), 
		'afc_general_settings_section_callback', 
		'afc_generalPage'
	);

	add_settings_field( 
		'afc_input_field_0', 
		__( 'Load editor', 'afc_textdomain' ), 
		'afc_general_show_editor_select', 
		'afc_generalPage', 
		'afc_generalPage_section' 
	);
}

/*
* This prints a field to receive selectorname
*/
function afc_general_show_editor_select(  ) { 

	$options = get_option( 'afc_general_settings' ); 
	?>
	<select name="afc_generalPageSettings[show_editor_btn]">
		<option value="yes" <?php echo ( ( isset( $options['show_editor_btn'] ) && $options['show_editor_btn'] == 'yes' )? 'selected="selected"' : '' ); ?>/>Yes</option>
		<option value="no" <?php echo ( ( isset( $options['show_editor_btn'] ) && $options['show_editor_btn'] == 'no' )? 'selected="selected"' : '' ); ?>/>No</option>
	</select>
	<?php
	echo __( ' Activate frontend editor ( Can be accessed from its button in admin bar. Only for administrator )', 'afc_textdomain' );
}

/*
* This page, settings section callback
*/
function afc_general_settings_section_callback() { 
	echo __( 'Here you can see general plugin options.', 'afc_textdomain' );
}

/*
* This function checks values intered in General page
*/
function afc_validate_generalOptions( $input ){
	$options = get_option('afc_general_settings');
	if( isset($input['show_editor_btn']) )
		$options['show_editor_btn'] = $input['show_editor_btn'];
	update_option('afc_general_settings', $options );
	add_settings_error( 'afc_generalPageSettings', 'afc', __( 'Your changes saved.', 'afc_textdomain' ), 'updated' );
	
}