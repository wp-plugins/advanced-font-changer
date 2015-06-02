<?php
/*
* This function outputs content of Edit Font admin page.
*/
function afc_editfont( ){
	settings_errors('afc_editfontsettings');
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_managefonts';
	$tabs = afcStrings::getString( 'manageFonts' );
	foreach( $tabs as $tab => $name ){
		$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";
	}
	echo '</h2>';
?>
	<form action='options.php' method='post'>
		
		<?php
		settings_fields( 'afc_editFontPage' );
		do_settings_sections( 'afc_editFontPage' );
		submit_button( __('Submit', 'afc_textdomain') );
		?>
		
	</form>
	</div>
<?php
}

add_action( 'admin_init', 'afc_editfont_settings_init' );
/*
* This function creates a section and its fields using wp settings api
*/
function afc_editfont_settings_init(  ) { 
	
	register_setting( 'afc_editFontPage', 'afc_editfontsettings', 'afc_validate_editFont' );

	add_settings_section(
		'afc_editFontPage_section', 
		__( 'Edit Font', 'afc_textdomain' ), 
		'afc_editfont_settings_section_callback', 
		'afc_editFontPage'
	);

	
	add_settings_field( 
		'afc_select_field_0', 
		__( 'Font Status <strong style="color:red;">*</strong>', 'afc_textdomain' ), 
		'afc_editFont_select_field_0_render', 
		'afc_editFontPage', 
		'afc_editFontPage_section' 
	);

	add_settings_field( 
		'afc_input_field_0', 
		__( 'Font Name <strong style="color:red;">*</strong>', 'afc_textdomain' ), 
		'afc_editFont_input_field_0_render', 
		'afc_editFontPage', 
		'afc_editFontPage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_1', 
		__( 'Font URL', 'afc_textdomain' ), 
		'afc_editFont_input_field_1_render', 
		'afc_editFontPage', 
		'afc_editFontPage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_2', 
		__( 'Font Meta (FVD)', 'afc_textdomain' ), 
		'afc_editFont_input_field_2_render', 
		'afc_editFontPage', 
		'afc_editFontPage_section' 
	);


}

/*
* This prints the font status select element
*/
function afc_editFont_select_field_0_render(){
	$option = get_option('afc_font_for_edit');
	echo '<select id="external_font_status" name="afc_editfontsettings[status] autocomplete="off"">
		<option value="unknown" '. (( $option['status'] == 'unknown' )? 'selected="selected"' : '' ) .'>'. __( 'Unknown', 'afc_textdomain' ) . '</option>
		<option value="google" '. (( $option['status'] == 'google' )? 'selected="selected"' : '' ) .'>'. __( 'Google', 'afc_textdomain' ) . '</option>
	</select>';
	
}

/*
* This prints the font name field
*/
function afc_editFont_input_field_0_render(){ 
	$option = get_option('afc_font_for_edit');
	?>
	<input id="edit_font_name" name="afc_editfontsettings[fontname]" type="text" value="<?php echo $option['name']; ?>" readonly />
	<?php
}

/*
* This prints the font url input field
*/
function afc_editFont_input_field_1_render(){ 
	$option = get_option('afc_font_for_edit');
	?>
	<input id="edit_font_url" name="afc_editfontsettings[fonturl]" type="text" value="<?php echo ( isset( $option['metadata']['url'] ) )? $option['metadata']['url'] : '' ; ?>" />
	<?php
}

/*
* This prints the font weight input field
*/
function afc_editFont_input_field_2_render(){
	$option = get_option('afc_font_for_edit');
	
	?>
	<input id="edit_font_meta" name="afc_editfontsettings[fvd]" type="text" value="<?php echo ( isset( $option['metadata']['fvd'] ) )? $option['metadata']['fvd'] : '' ; ?>" />
	<?php
}

/*
* This page settings section callback
*/
function afc_editFont_settings_section_callback() { 
	echo __( 'Here you can edit an existing external font. <br> <strong>Note1:</strong> If this is a google font or a font which is included in os, left the url empty.( example: Tahoma, Helvetica, Droid Sans , etc ).
	<br> <strong>Note2:</strong> For more info about fvd (font variation description) please see plugin documentation.', 'afc_textdomain' );
}

/*
* This function checks values intered in Edit Font page
*/
function afc_validate_editFont( $input ){
	$message = ''; $type = '';
	$option = get_option('afc_font_for_edit');
	if( isset( $input['fontname']) && trim( $input['fontname'] ) != '' ){
		$fontName = trim( $input['fontname'] );
		$allInfo = array();
		$allInfo['id'] = $option['id'];
		$afcFonts = new afcfonts();
		if( $afcFonts->fontExists( $fontName ) ){
			if( isset( $input['fvd'] ) && trim( $input['fvd'] ) != '' ){
				if( preg_match( '/^(((a)|(b)|(c)|(d)|(n)|(e)|(f)|(g)|(h)|(i)|(o))[1-9]*(,))*(((a)|(b)|(c)|(d)|(n)|(e)|(f)|(g)|(h)|(i)|(o))[1-9]*)$/', trim( $input['fvd'] ) ) ){
					$allInfo['metadata']['fvd'] = $input['fvd'];
				}
				else{
					$message = __( 'Your metadata is incorrect. Example of correct meta data is : b or i4 or n4,i7 ', 'afc_textdomain' );
					$type = 'error';						
				}
			}
			if( $message == '' ){
				$allInfo['name'] = $fontName;
				if( isset( $input['status'] ) && $input['status'] == 'unknown' ){
					$allInfo['status'] = 'unknown';
					if( isset( $input['fonturl'] ) && trim( $input['fonturl'] ) != '' ){
						if( strpos( 'http', $input['fonturl'] ) !== false && strpos( 'http', $input['fonturl'] ) == 0 ){
							$allInfo['metadata']['url'] = $input['fonturl'];
						}
						else{
							$message = __( 'Entered url is incorrect. A url must have a http at its begining.', 'afc_textdomain' );
							$type = 'error';
						}
					}
					
					if( $message == '' ){
						$thisFontArr = $afcFonts->getFonts( 'name', array( $input['fontname'] ) );
						$afcFonts->updateFonts( 'remove', array( $thisFontArr ) );
						$afcFonts->addFonts( array( $allInfo ) );
						update_option( 'afc_font_for_edit', $allInfo );
						$message = __( 'Any changes successfully saved.', 'afc_textdomain' );
						$type = 'updated';
					}
				}
				elseif( isset( $input['status'] ) && $input['status'] == 'google'  ){
					$allInfo['status'] = 'google';
					if( !preg_match( '/^[a-z1-9 ]+$/i', $fontName ) ){
						$message = __( 'Incorect google font name. A google font name can only contains this characters : [A-Z],[a-z],[1-9],[space].', 'afc_textdomain' );
						$type = 'error';
					}
					
					if( $message == '' ){
						$thisFontArr = $afcFonts->getFonts( 'name', array( $input['fontname'] ) );
						$afcFonts->updateFonts( 'remove', $thisFontArr );
						$afcFonts->addFonts( array( $allInfo ) );
						update_option( 'afc_font_for_edit', $allInfo );
						$message = __( 'Any changes successfully saved.', 'afc_textdomain' );
						$type = 'updated';
					}
				}
			}
		}
		else{
			$message = __( 'You can not edit the font name. But you can remove this font from list and add it with diffrent name.', 'afc_textdomain' );
			$type = 'error';
		}
	}
	else{
		$message = __( 'Please enter a font name.', 'afc_textdomain' );
		$type = 'error';
	}
	add_settings_error( 'afc_editfontsettings', 'afc', $message, $type );
}

?>