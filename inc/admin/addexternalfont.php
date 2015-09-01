<?php
/*
* This function outputs content of Add external Font admin page.
*/
function afc_external_font(){
	echo '<div class="afc-main">';
	$tabs = afcStrings::getString( 'manageFonts' );
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	settings_errors( 'afc_externalfontsettings' );
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_external_font';
	foreach( $tabs as $tab => $name ){
		$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab" . $classnames . "' href='?page=$tab&tab=$tab'>$name</a>";

	}
	echo '</h2>';
?>
	<form action='options.php' method='post'>
		
		<?php
		settings_fields( 'afc_externalfontpage' );
		do_settings_sections( 'afc_externalfontpage' );
		submit_button( __('Submit', 'afc_textdomain') );
		?>
		
	</form>
	</div>
<?php
}

add_action( 'admin_init', 'afc_externalfont_settings_init' );

/*
* This function creates a section and its fields using wp settings api
*/
function afc_externalfont_settings_init(  ) { 
	
	register_setting( 'afc_externalfontpage', 'afc_externalfontsettings', 'afc_validate_externalfonts' );

	add_settings_section(
		'afc_externalfontpage_section', 
		__( 'Add External Font', 'afc_textdomain' ), 
		'afc_externalfont_settings_section_callback', 
		'afc_externalfontpage'
	);
	
	add_settings_field( 
		'afc_select_field_0', 
		__( 'Font Status <strong style="color:red;">*</strong>', 'afc_textdomain' ), 
		'afc_externalfont_select_field_0_render', 
		'afc_externalfontpage', 
		'afc_externalfontpage_section' 
	);

	add_settings_field( 
		'afc_input_field_0', 
		__( 'Font Name <strong style="color:red;">*</strong>', 'afc_textdomain' ), 
		'afc_externalfont_input_field_0_render', 
		'afc_externalfontpage', 
		'afc_externalfontpage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_1', 
		__( 'Font URL', 'afc_textdomain' ), 
		'afc_externalfont_input_field_1_render', 
		'afc_externalfontpage', 
		'afc_externalfontpage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_2', 
		__( 'Font Meta (FVD)', 'afc_textdomain' ), 
		'afc_externalfont_input_field_2_render', 
		'afc_externalfontpage', 
		'afc_externalfontpage_section' 
	);
}

/*
* This prints the font status select element
*/
function afc_externalfont_select_field_0_render(){
	?>	
	<select id="external_font_status" name="afc_externalfontsettings[status]">
		<option value="unknown"><?php _e( 'Unknown', 'afc_textdomain' );?></option>
		<option value="google"><?php _e( 'Google', 'afc_textdomain' );?></option>
        <option value="local"><?php _e( 'Local', 'afc_textdomain' );?></option>
	</select>
	<?php
}

/*
* This prints the font name field
*/
function afc_externalfont_input_field_0_render(){ 
	?>
	<input id="external_font_name" name="afc_externalfontsettings[fontname]" type="text" />
	<?php
}

/*
* This prints the font url input field
*/
function afc_externalfont_input_field_1_render(){ 
	?>
	<input id="external_font_url" name="afc_externalfontsettings[fonturl]" type="text" />
	<?php
}

/*
* This prints the font weight input field
*/
function afc_externalfont_input_field_2_render(){
	?>
	<input id="external_font_fvd" name="afc_externalfontsettings[fvd]" type="text" />
	<?php
}

/*
* This page settings section callback
*/
function afc_externalfont_settings_section_callback() { 
	echo __( 'Here you can add a external font to plugin\'s fonts list. <br /> <strong>Note:</strong> If you are adding a google font or a font which is included in os, just write font name and left the url empty.( example: Tahoma, Helvetica, Droid Sans , etc ).<br /> <strong>Note2:</strong> If you are unable to upload your fonts to the plugin using upload font page, you can add its name to plugin fonts list by choosing local font. In this case the name of your font is your font file name.', 'afc_textdomain' );
}

/*
* This function checks values entered in add external fonts page
*/
function afc_validate_externalfonts( $input ){
	$message = ''; $type = '';
	if( isset( $input['fontname']) && trim( $input['fontname'] ) != '' ){
		$fontName = trim( $input['fontname'] );
		$allInfo = array();
		$afcFonts = new afcfonts();
		if( !$afcFonts->fontExists( $fontName ) ){
			if( isset( $input['fvd'] ) && trim( $input['fvd'] ) != '' ){
				if( preg_match( '/^(((a)|(b)|(c)|(d)|(n)|(e)|(f)|(g)|(h)|(i)|(o))[1-9]*(,))*(((a)|(b)|(c)|(d)|(n)|(e)|(f)|(g)|(h)|(i)|(o))[1-9]*)$/', trim( $input['fvd'] ) ) ){
					$allInfo['metadata']['fvd'] = $input['fvd'];
				}
				else{
					$message = __( 'Your metadata is incorrect. Example of correct meta data is : b or i4 or n4,i7 <br>', 'afc_textdomain' );
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
							$message .= __( 'Entered url is incorrect. A url must have a http at its begining.', 'afc_textdomain' );
							$type = 'error';
						}
					}
						
					if( $message == '' ){
						$afcFonts->addFonts( array( $allInfo ) );
						$message .= __( 'Your font added to list.', 'afc_textdomain' );
						$type = 'updated';
					}
					
				}
				elseif( isset( $input['status'] ) && $input['status'] == 'google'  ){
					$allInfo['status'] = 'google';
					if( !preg_match( '/^[a-z1-9 ]+$/i', $fontName ) ){
						$message .= __( 'Invalid google font name. A google font name can only contains this characters : [A-Z],[a-z],[1-9],[space].', 'afc_textdomain' );
						$type = 'error';
					}

					if( $message == '' ){
						$afcFonts->addFonts( array($allInfo) );
						$message = __( 'Your font added to list.', 'afc_textdomain' );
						$type = 'updated';
					}
				}
                elseif( isset( $input['status'] ) && $input['status'] == 'local'  ){
					$allInfo['status'] = 'local';
					if( !preg_match( '/^[a-z1-9-_]+$/i', $fontName ) ){
						$message .= __( 'Invalid local font name. A local font name can only contains this characters : [A-Z],[a-z],[1-9],[-],[_].', 'afc_textdomain' );
						$type = 'error';
					}

					if( $message == '' ){
						$afcFonts->addFonts( array($allInfo) );
						$message = __( 'The name of your local font added to list. Now you can upload its files in plugin-dir/fonts/local .', 'afc_textdomain' );
						$type = 'updated';
					}
				}
			}
		}
		else{
			$message = __( 'A font with this name already exists.', 'afc_textdomain' );
			$type = 'error';
		}
	}
	else{
		$message = __( 'Please enter a font name.', 'afc_textdomain' );
		$type = 'error';
	}
	add_settings_error( 'afc_externalfontsettings', 'afc', $message, $type );
}

?>