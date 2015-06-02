<?php
/*
* This function outputs content of import/export admin page.
*/
function afc_import_export( $current = 'afc_import_export' ){
	echo '<div class="afc-main">';
	$tabs = array( 'afc_plugin_options' => 'General Options', 'afc_import_export' => 'Import/Export' );
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	settings_errors('afc_ioPage');
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_import_export';
	foreach( $tabs as $tab => $name ){
		$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";

	}
	echo '</h2>';
?>
	<form action='options.php' method='post'>
		
		<?php
		settings_fields( 'ioPage' );
		do_settings_sections( 'ioPage' );
		submit_button( __('Submit', 'afc_textdomain') );
		?>
		
	</form>
	</div>
<?php
}

add_action( 'admin_init', 'afc_io_settings_init' );
/*
* This function creates a section and its fields using wp settings api
*/
function afc_io_settings_init(  ) { 

	register_setting( 'ioPage', 'afc_ioPage', 'afc_validate_io' );

	add_settings_section(
		'afc_ioPage_section', 
		__( 'Import / Export Settings', 'afc_textdomain' ), 
		'afc_io_settings_section_callback', 
		'ioPage'
	);

	add_settings_field( 
		'afc_textarea_field_0', 
		__( 'Export data', 'afc_textdomain' ), 
		'afc_textarea_field_0_render', 
		'ioPage', 
		'afc_ioPage_section' 
	);
	
	add_settings_field( 
		'afc_textarea_field_1', 
		__( 'Ready to use stylesheet', 'afc_textdomain' ), 
		'afc_textarea_field_1_render', 
		'ioPage', 
		'afc_ioPage_section' 
	);
	
	add_settings_field( 
		'afc_textarea_field_2', 
		__( 'Import data', 'afc_textdomain' ), 
		'afc_textarea_field_2_render', 
		'ioPage', 
		'afc_ioPage_section' 
	);
}

/*
* This prints a textarea to echo available selectors in json format
*/
function afc_textarea_field_0_render(  ) { 
	$afcSelectors = new afcselectors();
	$selectors = $afcSelectors->getCols( array( 'selectorName', 'properties', 'pageType', 'editorData' ) );
	$selectors = json_encode( $selectors );
    $afcFonts = new afcfonts();
    $fonts = $afcFonts->getCols( array( 'name', 'status', 'metadata' ) );
    $fonts = json_encode( $fonts );
    echo __( 'These are your selectors.', 'afc_textdomain' ) . '<br />';
	?>
	<textarea cols='60' rows='10' readonly ><?php echo $selectors; ?></textarea><br /><br />
	<?php
    echo __( 'These are your fonts. Local fonts must have files, and you must move them too. Font files are located in plugin-dir/fonts/local .', 'afc_textdomain' ) . '<br />';
    ?>
	<textarea cols='60' rows='10' readonly ><?php echo $fonts; ?></textarea>
	<?php
}

/*
* This prints two textarea for user to echo used font faces and selectors in css format
*/
function afc_textarea_field_1_render(  ) { 
	$style = new afcstyles;
	$output = $style->generateLocalFontFaces();
	$output .= "\n" . $style->generateStyles( 'all', 'yes' );
    $output = preg_replace('/\t+/', '', $output);
	$googleStyle = $style->generateGoogleFontsStyle();
	echo __( 'This is a ready to use stylesheet for inserting directly in your theme style file . keep in mind you must edit links to font files.', 'afc_textdomain' ) . '<br />';
	?>
	<textarea cols='60' rows='10' readonly ><?php echo $output; ?></textarea><br /><br />
	<?php
	echo __( 'This field contains link to your used google fonts.(if you used google fonts, insert it in the head tag of your theme. )', 'afc_textdomain' ) . '<br />';
	?>
	<textarea cols='60' rows='1' readonly ><?php echo $googleStyle; ?></textarea>
	<?php
}

/*
* This prints a textarea for user to import previously exported data
*/
function afc_textarea_field_2_render(  ) { 
	echo __( 'To import selectors paste your data here.', 'afc_textdomain' ) . '<br />';
    ?>
	<textarea cols='60' rows='10' name='afc_ioPage[afc_selectors_to_import]'></textarea>
    <br /><br />
	<?php
	echo __( 'To import fonts paste your data here.', 'afc_textdomain' ) . '<br />';
	?>
	<textarea cols='60' rows='10' name='afc_ioPage[afc_fonts_to_import]'></textarea>
	<?php
}

/*
* This page settings section callback
*/
function afc_io_settings_section_callback(  ) { 

	echo __( 'Here you can easily import , export plugin settings . Also you can export an generated style ( you can insert it directly in your styles, so you don\'t need this plugin in another wp installations ).<br>', 'afc_textdomain' );
	echo __( '<strong style="color:red;">Warning:</strong> be aware of submitting this form with empty data in import textarea. doing this will removes all your saved data', 'afc_textdomain' );
}

/*
* This function checks values intered in import/export page
*/
function afc_validate_io( $input ){
	$mustImportSelectors = str_replace( '\n', '', trim( $input['afc_selectors_to_import'] ) );
    $mustImportFonts = str_replace( '\n', '', trim( $input['afc_fonts_to_import'] ) );
	$afcSelectors = new afcselectors();
    $afcFonts = new afcfonts();
	$message = '';
	$type = '';
	if( $mustImportSelectors != '' ){
		$mustImportSelectors = json_decode( $mustImportSelectors, true );
		if( is_array( $mustImportSelectors ) && count( $mustImportSelectors ) > 0 ){
			$safeData = array();
			foreach( $mustImportSelectors as $key){
				if( $afcSelectors->isValidSelector( $key ) ){
					$safeData[] = $key;
				}
			}
			if( count( $safeData ) > 0 ){
				$afcSelectors->addelems( $safeData );
				$message = count( $safeData ) . __( ' Entry(s) successfully imported.', 'afc_textdomain' );
				$type = 'updated';
			}
			else {
				$message =  __( 'Your data is incorrect.', 'afc_textdomain' );
				$type = 'error';
			}
		}
		else{
			$message =  __( 'Your data is incorrect.', 'afc_textdomain' );
			$type = 'error';
		}
	}
    
    if( $mustImportFonts != '' ){
		$mustImportFonts = json_decode( $mustImportFonts, true );
		if( is_array( $mustImportFonts ) && count( $mustImportFonts ) > 0 ){
			$safeData = array();
			foreach( $mustImportFonts as $key){
				if( $afcFonts->isValidFont( $key ) ){
					$safeData[] = $key;
				}
			}
			if( count( $safeData ) > 0 ){
                $safeData = $afcFonts->unserializeIt($safeData);
				$afcFonts->addFonts( $safeData );
				$message = count( $safeData ) . __( ' Entry(s) successfully imported.', 'afc_textdomain' );
				$type = 'updated';
			}
			else {
				$message =  __( 'Your data is incorrect.', 'afc_textdomain' );
				$type = 'error';
			}
		}
		else{
			$message =  __( 'Your data is incorrect.', 'afc_textdomain' );
			$type = 'error';
		}        
    }
	
    
    
    if($mustImportFonts == '' && $mustImportSelectors == '' ) {
		$afcSelectors->reset();
		$message =  __( 'All your selectors successfully removed.', 'afc_textdomain' );
		$type = 'updated';

	}
	
	add_settings_error( 'afc_ioPage', 'afc', $message, $type );
}
?>