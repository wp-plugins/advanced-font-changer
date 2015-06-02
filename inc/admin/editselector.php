<?php
/**
 * Outputs content of Edit Selector admin page.
 */
function afc_editselector( ){
	settings_errors('afc_editselectorsettings');
	echo '<h2 class="nav-tab-wrapper">';
	$current = 'afc_manageselectors';
		$tabs = afcStrings::getString( 'manageSelectors' );
	foreach( $tabs as $tab => $name ){
		$classnames = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$classnames' href='?page=$tab&tab=$tab'>$name</a>";

	}
	echo '</h2>';
?>
	<form action='options.php' method='post'>
		
		<?php
		settings_fields( 'afc_editSelectorPage' );
		do_settings_sections( 'afc_editSelectorPage' );
		submit_button( __('Submit', 'afc_textdomain') );
		?>
		
	</form>
	</div>
<?php
}

add_action( 'admin_init', 'afc_editselector_settings_init' );
/**
 * Creates a section and its fields using wp settings api
 */
function afc_editselector_settings_init(  ) { 
	
	register_setting( 'afc_editSelectorPage', 'afc_editselectorsettings', 'afc_validate_editedselector' );

	add_settings_section(
		'afc_editSelectorPage_section', 
		__( 'Add New Selector', 'afc_textdomain' ), 
		'afc_editselector_settings_section_callback', 
		'afc_editSelectorPage'
	);

	add_settings_field( 
		'afc_selectorname_input', 
		__( 'Selector Name ', 'afc_textdomain' ), 
		'afc_edit_selectorname_input_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_forshortcode_select', 
		__( 'Is It For ShortCode ?', 'afc_textdomain' ), 
		'afc_edit_forshortcode_select_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);

	add_settings_field( 
		'afc_fontname_select', 
		__( 'Font Name', 'afc_textdomain' ), 
		'afc_edit_fontname_select_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_forcechange_select', 
		__( 'Force Change Font', 'afc_textdomain' ), 
		'afc_edit_forcechange_select_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_fontsize_input', 
		__( 'Font Size', 'afc_textdomain' ), 
		'afc_edit_fontsize_input_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_fontweight_select', 
		__( 'Font Weight', 'afc_textdomain' ), 
		'afc_edit_fontweight_select_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_fontstyle_select', 
		__( 'Font Style', 'afc_textdomain' ), 
		'afc_edit_fontstyle_select_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_textcolor_input', 
		__( 'Text Color', 'afc_textdomain' ), 
		'afc_edit_textcolor_input_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_textdecoration_input', 
		__( 'Text Decoration', 'afc_textdomain' ), 
		'afc_edit_textdecoration_input_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_textshadow_input', 
		__( 'Text Shadow', 'afc_textdomain' ), 
		'afc_edit_textshadow_input_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
	
	add_settings_field( 
		'afc_pagetype_checkbox', 
		__( 'Selector Page Type', 'afc_textdomain' ), 
		'afc_edit_pagetype_checkbox_render', 
		'afc_editSelectorPage', 
		'afc_editSelectorPage_section' 
	);
}

/**
 * Prints a field to receive selectorname
 */
function afc_edit_selectorname_input_render(  ) { 
	$option = get_option('afc_selector_for_edit');
	?>
	<input id="selectorname" name="afc_editselectorsettings[selectorname]" type="text" value="<?php echo $option['selectorName']; ?>" />
	<?php
}

/**
 * Prints a field for is it for short code
 */
function afc_edit_forshortcode_select_render(  ){
	$option = get_option('afc_selector_for_edit');
	?>
	<select id="isshortcode" name="afc_editselectorsettings[isshortcode]" autocomplete="off">
		<option value="0" <?php echo ( ( isset( $option['editorData']['isShortCode'] ) && $option['editorData']['isShortCode'] == '0' )? 'selected="selected"' : '' ); ?>>No</option>
		<option value="1" <?php echo ( ( isset( $option['editorData']['isShortCode'] ) && $option['editorData']['isShortCode'] == '1' )? 'selected="selected"' : '' ); ?> >Yes</option>
	</select>
	<?php
}

/**
 * Prints a field to receive font name
 */
function afc_edit_fontname_select_render(  ) {
	$option = get_option('afc_selector_for_edit');
	$afcFonts = new afcfonts();
	$allFonts = $afcFonts->getCols( array('name') );
	echo '<select autocomplete="off"  name="afc_editselectorsettings[fontname]">';
		echo '<option value="none"></option>';
		foreach( $allFonts as $font )
			if( is_array( $option ) && isset( $option['properties']['fontName'] ) && $option['properties']['fontName']['name'] == $font['name'] )
				echo '<option selected="selected" value="' . $font['name'] . '">' . $font['name'] . '</option>';
			else
				echo '<option value="' . $font['name'] . '">' . $font['name'] . '</option>';
	echo '</select>';
}

/**
 * Prints a select for user to select if plugin must forcefully changes font family of selector or not
 */
function afc_edit_forcechange_select_render(  ) {
	$option = get_option('afc_selector_for_edit');
	?>
	<select name="afc_editselectorsettings[forcechange]" autocomplete="off">
		<option value="0" <?php echo ( ( isset( $option['properties']['fontName']['forceChangeFont'] ) && $option['properties']['fontName']['forceChangeFont'] == '0' )? 'selected="selected"' : '' ); ?>>No</option>
		<option value="1" <?php echo ( ( isset( $option['properties']['fontName']['forceChangeFont'] ) && $option['properties']['fontName']['forceChangeFont'] == '1' )? 'selected="selected"' : '' ); ?> >Yes</option>
	</select>
	<?php
}

/**
 * Prints a field to receive font size
 */
function afc_edit_fontsize_input_render(  ) {
	$option = get_option('afc_selector_for_edit');
	?>
	<input id="fontsize" name="afc_editselectorsettings[fontsize]" type="text" value="<?php echo ( ( isset( $option['properties']['fontSize'] ) )? $option['properties']['fontSize'] : '' ); ?>" /><?php echo _e('px','afc_textdomain'); ?>
	<?php
}

/**
 * Prints a select for user to select a font weight
 */
function afc_edit_fontweight_select_render(  ) {
	$option = get_option('afc_selector_for_edit');
	$items = afcStrings::getString('weights');
	$selected = 'selected="selected"';
	?>
	<select name="afc_editselectorsettings[fontweight]" autocomplete="off" >
	<option value="none" ><?php _e( 'Unset', 'afc_textdomain' ); ?></option>
	<?php
	foreach( $items as $item ){
		echo '<option value="' . $item . '"' . ( ( isset( $option['properties']['fontWeight'] ) && $option['properties']['fontWeight'] == $item )? $selected : '' ) . '>' . $item . '</option>';
	}
	?>
	</select>
	<?php
}

/**
 * Prints a select for user to select a font weight
 */
function afc_edit_fontstyle_select_render(  ) {
	$option = get_option('afc_selector_for_edit');
	$items = afcStrings::getString('fontstyles');
	$selected = 'selected="selected"';
	?>
	<select name="afc_editselectorsettings[fontstyle]" autocomplete="off" >
	<option value="none" ><?php _e( 'Unset', 'afc_textdomain' ); ?></option>
	<?php
	foreach( $items as $item ){
		echo '<option value="' . $item . '"' . ( ( isset( $option['properties']['fontStyle'] ) && $option['properties']['fontStyle'] == $item )? $selected : '' ) . '>' . $item . '</option>';
	}
	?>
	</select>
	<?php
}

/**
 * This prints a field to receive text color
 */
function afc_edit_textcolor_input_render(  ) {
	$option = get_option('afc_selector_for_edit');
	?>
	<input id="textcolor" name="afc_editselectorsettings[textcolor]" type="text" value="<?php echo ( ( isset( $option['properties']['textColor'] ) )? $option['properties']['textColor'] : '' ); ?>" />
	<?php
}

/**
 * This prints a select to receive textdecoration
 */
function afc_edit_textdecoration_input_render(  ) {
	$option = get_option('afc_selector_for_edit');
	$items = afcStrings::getString('decorations');
	$selected = 'selected="selected"';
	?>
	<select name="afc_editselectorsettings[textdecoration]" autocomplete="off" >
	<?php
	foreach( $items as $item ){
		echo '<option value="' . $item . '"' . ( ( isset( $option['properties']['textDecoration'] ) && $option['properties']['textDecoration'] == $item )? $selected : '' ) . '>' . $item . '</option>';
	}
	?>
	</select>	
	<?php
}

/**
 * This prints a field to receive textshadow
 */
function afc_edit_textshadow_input_render(  ) {
	$option = get_option('afc_selector_for_edit');
	echo '
	<em>'. __( 'If you dont want text shadow just left all fields blank.', 'afc_textdomain' ) . '</em>
	<table>
	<tr >
	<th><label>' . __( 'h-shadow  ', 'afc_textdomain' ) . '<i>*</i></label></th>
	<td ><input id="textshadow-h-shadow" name="afc_editselectorsettings[textshadow][h-shadow]" type="text" value="' . ( ( isset( $option['properties']['textShadow']['hshadow'] ) )? $option['properties']['textShadow']['hshadow'] : '' ) . '" />' . __('px','afc_textdomain') . '</td>
	</tr>
	<tr >
	<th><label>' . __( 'v-shadow  ', 'afc_textdomain' ) . '<i>*</i></label></th>
	<td ><input id="textshadow-v-shadow" name="afc_editselectorsettings[textshadow][v-shadow]" type="text" value="' . ( ( isset( $option['properties']['textShadow']['vshadow'] ) )? $option['properties']['textShadow']['vshadow'] : '' ) . '" />' . __('px','afc_textdomain') . '</td>
	</tr>
	<tr >
	<th><label>' . __( 'blur  ', 'afc_textdomain' ) . '</label></th>
	<td><input id="textshadow-blur" name="afc_editselectorsettings[textshadow][blur]" type="text" value="' . ( ( isset( $option['properties']['textShadow']['blur'] ) )? $option['properties']['textShadow']['blur'] : '' ) . '" />' . __('px','afc_textdomain') . '</td>
	<tr >
	<th><label>' . __( 'color  ', 'afc_textdomain' ) . '</label></th>
	<td><input id="textshadow-color" name="afc_editselectorsettings[textshadow][color]" type="text" value="' . ( ( isset( $option['properties']['textShadow']['color'] ) )? $option['properties']['textShadow']['color'] : '' ) . '" /></td>
	</tr>
	</table>
	';
}

/**
 * This prints available page types for user to choose if we must load the font every where or only in specified page types
 */
function afc_edit_pagetype_checkbox_render(  ) {
	$option = get_option('afc_selector_for_edit');
	$specificTypes = array( 'home', 'archive' );
	echo '<em>'. __( 'If the selector is not for specific Page Types don\'t check anyone.<br> For finding id\'s take a look at <a href="http://codex.wordpress.org/FAQ_Working_with_WordPress#How_do_I_determine_a_Post.2C_Page.2C_Category.2C_Tag.2C_Link.2C_Link_Category.2C_or_User_ID.3F">this link</a>.<br> Seperate each id with a "," character.', 'afc_textdomain' ) . '</em>
	<table>';
	
	foreach ( afcStrings::getString('pagetype') as $pt=>$value ){
		$ptStatus = '';
		if( isset( $option['pageType'][ $pt ]['status'] ) )
			$ptStatus = $option['pageType'][ $pt ]['status'];
		echo '
		<tr >
			<th><label><input id="'. $pt .'_label" name="afc_editselectorsettings[pagetype]['. $pt .'][checked]" type="checkbox" value="1" '. ( ( isset( $option['pageType'][$pt] ) )? 'checked="checked"' : '' ) . '>' . $value . '</label></th>
		';
		if( !in_array( $pt, $specificTypes ) )
			echo '<td >
			'. __( '(optional)', 'afc_textdomain' ) . '
					<select name="afc_editselectorsettings[pagetype]['. $pt .'][status]">
						<option value="include" '. ( ( $ptStatus != '' && $ptStatus == 'include' )? 'selected="selected"' : '' ) .'>Include</option>
						<option value="exclude" '. ( ( $ptStatus != '' && $ptStatus == 'exclude' )? 'selected="selected"' : '' ) .'>Exclude</option>
					</select>
					<input id="'. $pt .'_input" name="afc_editselectorsettings[pagetype]['. $pt .'][valuearr]" type="text" value="'. ( ( isset( $option['pageType'][ $pt ]['status'] ) )? implode ( ',', $option['pageType'][ $pt ]['valueArr'] ) : '' ) . '" />
				</td>';
		else{
			echo '<td></td>';
		}
		echo '</tr>';
	} ?>
	</table>
	<?php
}

/**
 * This page settings section callback
 */
function afc_editselector_settings_section_callback() {
	echo __( 'Here you can edit an existing selector and add it as a new selector.<br> <strong>Note1:</strong> If you have another selector with same name as this one and one of page types of that one match with current selector, plugin removes the existing and adds this one. So be careful when editing selector name or pagetypes.<br><strong>Note2:</strong> If you change the pagetype and choose pagetype(s) other than current pagetype(s), Plugin adds the selector as new selector. ', 'afc_textdomain' );
}

/**
 * This function checks values intered in Add Selector page
 */
function afc_validate_editedselector( $input ){
	$message = '';
	$data= array();
	$sName = '';
	if( !isset( $input['selectorname'] ) || trim( $input['selectorname'] ) == '' )
		$message .= __( 'Please fill in selectorname.', 'afc_textdomain' ) . '<br>';
	else{
		$sName = trim( $input['selectorname'] );
		if( preg_match( "/[,]/" , $sName ) || preg_match( "/[#.,]$/" , $sName ) ){
			$message .= __( 'Selector Name must starts with tag name, id or class name. Also using [,] at the selector name or using [.], [#] at the end of selector name is not allowed. ', 'afc_textdomain' ) . '<br>';
		}
		else{	
			$data['selectorName'] = $sName;
            if( !isset( $input['isshortcode'] ) || trim( $input['isshortcode'] ) == '' )
                $message .= __( 'You can not left "is it a short code" field empty.', 'afc_textdomain' ) . '<br>';
            elseif( $input['isshortcode'] == '1' && preg_match( "/^.+[.# ]/" , $sName ) ){
                $message .= __( "For using this selector in a shortcode it might not have '.' , '#' or ' '(space) in the middle or end of its name. But using [.] or [#] in the begin is allowed. ", 'afc_textdomain' ) . '<br>';
            }
            elseif( $input['isshortcode'] == '1' ){
                $data['editorData']['isShortCode'] = 1;
            }
            else{
                $data['editorData']['isShortCode'] = 0;
            }
            if( isset( $data['editorData']['isShortCode'] ) && $data['editorData']['isShortCode'] == 1 
            && !preg_match( "/^[.#].*/", $sName ) ){
                $data['selectorName'] = '.' . $sName;
            }
		}
	}
    
	if( isset( $input['fontname'] ) && $input['fontname'] != '' && $input['fontname'] != 'none' ){
		$afcFonts = new afcfonts();
		$fontExists = $afcFonts->getFonts( 'name', array( $input['fontname'] ) );
		if( count( $fontExists ) <= 0 ){
			$message .= __( 'Selected Font for this selector is invalid.', 'afc_textdomain' ). '<br>';
		}
		else{
			$data['properties']['fontName']['name'] = trim( $input['fontname'] );
		}
	}
	if( $input['fontname'] == 'none' && $input['forcechange'] == '1' )
		$message .= __( 'You have not selected any font to force it.', 'afc_textdomain' ) . '<br>';
	if( isset( $data['properties']['fontName'] ) )
		$data['properties']['fontName']['forceChangeFont'] = trim( $input['forcechange'] );

	if( isset( $input['fontsize'] ) && $input['fontsize'] != '' ){
		if( !ctype_digit( trim( $input['fontsize'] ) ) ){
			$message .= __( 'Fontsize is numeric, please type a positive number or left it empty.', 'afc_textdomain' ) . '<br>';
		}
		else{
			$data['properties']['fontSize'] = trim( $input['fontsize'] );
		}
	}
	if( isset( $input['fontweight'] ) && $input['fontweight'] != '' && $input['fontweight'] != 'none' ){
		if( !ctype_digit( $input['fontweight'] ) ){
			$message .= __( 'Font Weight is numeric.', 'afc_textdomain' ) . '<br>';
		}
		else{
			$data['properties']['fontWeight'] = trim( $input['fontweight'] );
		}
	}
	if( isset( $input['fontstyle'] ) && $input['fontstyle'] != '' && $input['fontstyle'] != 'none' ){
		if( !in_array( $input['fontstyle'], afcStrings::getString('fontstyles') ) ){
			$message .= __( 'Invalid font style.', 'afc_textdomain' ) . '<br>';
		}
		else{
			$data['properties']['fontStyle'] = trim( $input['fontstyle'] );
		}
	}
	if( isset( $input['textcolor'] ) && $input['textcolor'] != '' ){
		if( !preg_match( "/^[#][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f]$/i", $input['textcolor'] ) )
			$message .= __( 'The text color code is invalid.', 'afc_textdomain' ) . '<br>';
		else{	
			$data['properties']['textColor'] = trim( $input['textcolor'] );
		}
	}
	if( isset( $input['textdecoration'] ) && $input['textdecoration'] != '' && $input['textdecoration'] != 'none' ){
		if( !in_array( $input['textdecoration'], afcStrings::getString('decorations') ) ){
			$message .= __( 'Selected decoration is invalid.', 'afc_textdomain' ) . '<br>';
		}
		else{
			$data['properties']['textDecoration'] = trim( $input['textdecoration'] );
		}
	}
	if( isset( $input['textshadow'] ) ){
		if( $input['textshadow']['h-shadow'] != '' && $input['textshadow']['v-shadow'] != '' ){
			if( !preg_match( "/^[-]*[0-9]+$/", $input['textshadow']['h-shadow'] ) || !preg_match( "/^[-]*[0-9]+$/", $input['textshadow']['v-shadow'] ) ){
				$message .= __( 'Horizontal or vertical values in text shadow fields are invalid.', 'afc_textdomain' ) . '<br>';
			}
			else{
				$data['properties']['textShadow']['hshadow'] = trim( $input['textshadow']['h-shadow'] );
				$data['properties']['textShadow']['vshadow'] = trim( $input['textshadow']['v-shadow'] );
				if( trim( $input['textshadow']['blur'] ) != '' ){
					if( !preg_match( "/^[-]*[0-9]+$/", $input['textshadow']['blur'] ) ){
						$message .= __( 'Value in blur field of text shadow is invalid.', 'afc_textdomain' ) . '<br>';
					}
					else{
						$data['properties']['textShadow']['blur'] = trim( $input['textshadow']['blur'] );
					}
				}
				else{
					$data['properties']['textShadow']['blur'] = '0';
				}
				if( $input['textshadow']['color'] != '' ){
					if( !preg_match( "/^[#][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f]$/i", $input['textshadow']['color'] ) )
						$message .= __( 'Text shadow color code is invalid.', 'afc_textdomain' ) . '<br>';
					else{	
						$data['properties']['textShadow']['color'] = trim( $input['textshadow']['color'] );
					}
				}
				else{
					$data['properties']['textShadow']['color'] = '';
				}
			}
		}
		elseif( ( $input['textshadow']['h-shadow'] == '' && $input['textshadow']['v-shadow'] != '' ) 
			|| ( $input['textshadow']['h-shadow'] != '' && $input['textshadow']['v-shadow'] == '' ) ){
			$message .= __( 'Both the h-shadow and v-shadow of textshadow are required, left them empty or fill in both.', 'afc_textdomain' ) . '<br>';
		}
	}
	
	$specificTypes = array( 'home', 'archive' );
	if( isset( $input['pagetype'] ) ){
		foreach ( afcStrings::getString('pagetype') as $pt=>$val ){
			if( isset( $input['pagetype'][ $pt ]['checked'] ) ){
				if( isset( $input['pagetype'][ $pt ]['valuearr'] ) && $input['pagetype'][ $pt ]['valuearr'] != '' && !in_array( $pt, $specificTypes ) ){
					if( !preg_match( "/^([0-9]+[,]*)*[0-9]+$/", $input['pagetype'][ $pt ]['valuearr'] ) )
						$message .= __( 'Intered value in following field is incorrect:', 'afc_textdomain' ) . $val . __( ' . example of correct value is: 1,2,3', 'afc_textdomain' ) . '<br>';
					else{
						$data['pageType'][ $pt ]['status'] = $input['pagetype'][ $pt ]['status'];
						$data['pageType'][ $pt ]['valueArr'] = explode( ',', trim( $input['pagetype'][ $pt ]['valuearr'] ) );
					}
				}
				else{
					$data['pageType'][ $pt ] = 'empty';
				}
			}
		}
	}
	if( !isset( $data['properties'] ) )
		$data['properties'] = array();
	if( !isset( $data['pageType'] ) || count( $data['pageType'] ) <= 0 )
		$data['pageType']['all'] = 'empty';
	
	$afcselectors = new afcselectors();
	if( $message == '' ){
		$editingOne = get_option('afc_selector_for_edit');
		
		$data['id'] = $editingOne['id'];
		if( $data['selecorName'] != $editingOne['selectorName'] || $data['pageType'] != $editingOne['pageType'] ){
			$explodedSelector = explode( ' ', $data['selectorName'] );
			$parents = -1;
			foreach( $explodedSelector as $parent){
				if( trim( $parent ) != '' )
					$parents++;
			}
			$data['editorData']['numOfParents'] = $parents;
			$data['editorData']['isEditable'] = 0;
		}
		$data['editorData']['isNew'] = 0;
		$result = $afcselectors->getSimilarSelectors( $data );
		$result = $afcselectors->getForChecks( $result, $data );
		if( count( $result ) >= 1 ){
			$check = $afcselectors->isEqualSelectors( $data, $result );
			if( $check !== false ){
				$message = $check;
			}
		}
	}
	
	update_option( 'afc_selector_for_edit', $data );
	if( $message == '' ){
		$afcselectors->addelems( array( $data ) );
		$type = 'updated';
		$message = __( 'Your edit\'s successfully saved.', 'afc_textdomain' );
	}
	else{
		$type = 'error';
	}
	
	add_settings_error( 'afc_editselectorsettings', 'afc', $message, $type );
}

?>