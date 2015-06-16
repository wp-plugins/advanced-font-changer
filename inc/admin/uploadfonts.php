<?php
/*
 * This function outputs content of Upload Font admin page.
 */
function afc_fontupload(){
	echo '<div class="afc-main">';
	$tabs = afcStrings::getString( 'manageFonts' );
	echo '<h2 class="afc-head">' . __( 'Advanced Font Changer', 'afc_textdomain' ) . '</h2><br>';
	settings_errors('afc_uploadfontPageSettings');
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_fontupload';
	foreach( $tabs as $tab => $name ){
		$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";

	}
	echo '</h2>';
?>
<form action='options.php' method='post'>

    <?php
    settings_fields( 'afc_uploadfontPage' );
    do_settings_sections( 'afc_uploadfontPage' );
    submit_button( __('Submit', 'afc_textdomain') );
    ?>

</form>
</div>
<?php
}

add_action( 'admin_init', 'afc_uploadfont_settings_init' );
/*
 * This function creates a section and its fields using wp settings api
 */
function afc_uploadfont_settings_init(  ) { 
	
	register_setting( 'afc_uploadfontPage', 'afc_uploadfontPageSettings', 'afc_validate_fonts' );

	add_settings_section(
		'afc_uploadfontPage_section', 
		__( 'Upload font', 'afc_textdomain' ), 
		'afc_uploadfont_settings_section_callback', 
		'afc_uploadfontPage'
	);

	add_settings_field( 
		'afc_input_field_0', 
		__( 'Upload an eot file', 'afc_textdomain' ), 
		'afc_input_field_0_render', 
		'afc_uploadfontPage', 
		'afc_uploadfontPage_section' 
	);

	add_settings_field( 
		'afc_input_field_1', 
		__( 'Upload an ttf file', 'afc_textdomain' ), 
		'afc_input_field_1_render', 
		'afc_uploadfontPage', 
		'afc_uploadfontPage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_2', 
		__( 'Upload an woff file', 'afc_textdomain' ), 
		'afc_input_field_2_render', 
		'afc_uploadfontPage', 
		'afc_uploadfontPage_section' 
	);
	
	add_settings_field( 
		'afc_input_field_3', 
		__( 'Upload an svg file', 'afc_textdomain' ), 
		'afc_input_field_3_render', 
		'afc_uploadfontPage', 
		'afc_uploadfontPage_section' 
	);


}

/*
 * This prints a field to receive a eot file 
 */
function afc_input_field_0_render(  ) { 

	//$options = get_option( 'afc_data' ); 
?>
<input id="afc_eot_file_id" name="afc_uploadfontPageSettings[afc_eot_file_id]" type="hidden" value="" />
<input id="afc_eot_file" name="afc_uploadfontPageSettings[afc_eot_file]" type="text" readonly />
<input id="afc_eot_file_button" class="button" type="button" value="Upload" />
<?php

}
/*
 * This prints a field to receive a ttf file 
 */
function afc_input_field_1_render(  ) {
?>
<input id="afc_ttf_file_id" name="afc_uploadfontPageSettings[afc_ttf_file_id]" type="hidden" value="" />
<input id="afc_ttf_file" name="afc_uploadfontPageSettings[afc_ttf_file]" type="text" readonly />
<input id="afc_ttf_file_button" class="button" type="button" value="Upload" />
<?php

}
/*
 * This prints a field to receive a woff file 
 */
function afc_input_field_2_render(  ) {
?>
<input id="afc_woff_file_id" name="afc_uploadfontPageSettings[afc_woff_file_id]" type="hidden" value="" />
<input id="afc_woff_file" name="afc_uploadfontPageSettings[afc_woff_file]" type="text" readonly />
<input id="afc_woff_file_button" class="button" type="button" value="Upload" />
<?php

}
/*
 * This prints a field to receive a svg file 
 */
function afc_input_field_3_render(  ) { 
?>
<input id="afc_svg_file_id" name="afc_uploadfontPageSettings[afc_svg_file_id]" type="hidden" value="" />
<input id="afc_svg_file" name="afc_uploadfontPageSettings[afc_svg_file]" type="text" readonly />
<input id="afc_svg_file_button" class="button" type="button" value="Upload" />
<?php

}

/*
 * This page settings section callback
 */
function afc_uploadfont_settings_section_callback() { 
	echo __( 'Here you can upload your own fonts. After upload process completes you must press save changes so we can validate your uploaded files ( All four fields are required. If you don\'t have one or more of this formats , you can convert your font using online font converters ).<br><strong>Note1:</strong> We use font filename as fontname. <br><strong>Note2:</strong> you can only use this characters in a font name: [A-Z] and [a-z] and [0-9] and [-]. ', 'afc_textdomain' );
}

/*
 * This function checks values intered in upload font page
 */
function afc_validate_fonts( $input ){
	
	$formats = array( 'eot', 'ttf', 'woff', 'svg' );
    $firstFileInfo = array();
    $fileName = '';
    $uploaddir = wp_upload_dir();
	if( isset( $input['afc_eot_file'] ) && trim( $input['afc_eot_file'] ) != '' ){
		$firstFileInfo = pathinfo( trim( $input['afc_eot_file'] ) );
		$fileName = $firstFileInfo['filename'];
		$message = '';
		$type = '';
		if( preg_match( '/^[A-Za-z0-9-_]+$/', $fileName ) ){
			$afcFonts = new afcfonts();
			$allFonts = $afcFonts->getFonts('name');
			$alreadyExists = 0;
			if( is_array( $allFonts ) ){
				foreach( $allFonts as $key ){
					if( $key['name'] == $fileName ){
						$alreadyExists = 1;
						break;
					}
				}
			}
			if( !$alreadyExists ){
				foreach( $formats as $format ){
					$thisfile = trim( $input['afc_' . $format . '_file'] );
					if( $thisfile != '' ){
						$info = pathinfo( $thisfile );
						$fileaddress = $uploaddir['path'] . '/' . $info['basename'];
						if( $fileName == $info['filename'] ){
							if( $info['extension'] == $format ){
								if( !is_file( $fileaddress )  ){
									$message .= __( 'Link address of file with this format is incorrect : ', 'afc_textdomain' ) . $format . '<br>';
									$type = 'error';
								}
							}
							else{
								$message .= __( 'You have inserted incorrect file format in field : ', 'afc_textdomain' ) . $format . '<br>';
								$type = 'error';
							}
                            
						}
						else{
							$message .= __( 'Name of file with format eot is diffrent than file with format : ', 'afc_textdomain' ) . $format . '<br>';
							$type = 'error';
						}
					}
					else{
						$message .= __( 'Field for this file format is empty : ', 'afc_textdomain' ) . $format . '<br>';
						$type = 'error';
					}
				}
			}
			else{
				$message .= __( 'A font with this name already exists.', 'afc_textdomain' );
				$type = 'error';
			}
		}
		else{
			$message .= __( 'Name of file with format eot has wrong characters.', 'afc_textdomain' );
			$type = 'error';
		}
	}
	else{
		$message .= __( 'Please insert a eot file in field with format eot.', 'afc_textdomain' );
		$type = 'error';
	}
    $fontsFolder = $uploaddir['basedir'] . '/afc-local-fonts';
	if( !file_exists( $fontsFolder ) ){
        mkdir( $fontsFolder, 0755, true );
        if (!file_exists( $fontsFolder ))
        {
            $message .= __( 'My fonts folder not exists and i am unable to create it. please go to your wp uploads directory and create a folder with this name: afc-local-fonts.', 'afc_textdomain' );
            $type = 'error';
        }
    }

	if( $message == '' ){
		foreach( $formats as $format ){
			$thisfile = trim( $input['afc_' . $format . '_file'] );
			$info = pathinfo( $thisfile );
			$fileaddress = $uploaddir['path'] . '/' . $info['basename'];
			rename( $uploaddir['path'] . '/' . $info['basename'], $fontsFolder . '/' . $info['basename'] );
			wp_delete_attachment( $input['afc_' . $format . '_file_id'] );
		}
		$afcFonts = new afcfonts();
		$afcFonts->addFonts( 
			array( 
				array( 
					'name' => $fileName , 
					'status' => 'local',
					'metadata' => array()
				) 
			) 
		);
		$message = __('Your font successfully saved.', 'afc_textdomain' );
		$type = 'updated';
	}
	
	if( $message != '')
		add_settings_error( 'afc_uploadfontPageSettings', 'afc', $message, $type );
}

?>