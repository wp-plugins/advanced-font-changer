<?php
defined( 'ABSPATH' ) OR exit;
/*
Plugin Name: Advanced Font Changer
Plugin URI: -
Description: This plugin lets you visually change the font and other text properties in your theme, using its visual editor.
Version: 1.5
Author: wp-magic
Author URI: -
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

//afc constants
define( 'ADVANCEDFONTCHANGERDIR', plugin_dir_path( __FILE__ ) );
define( 'ADVANCEDFONTCHANGERURL', plugin_dir_url( __FILE__ ) );
//Loading plugin strings class
require_once( ADVANCEDFONTCHANGERDIR . 'inc/classes/class_afcstrings.php' );

add_action( 'init', 'afc_initialize' );
/**
 * To doing things that must be done in wordpress initialize. including translation, global needed classes, admin pages and etc.
 */
function afc_initialize() {
	//translation file
	load_plugin_textdomain( 'afc_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	//Public
	foreach( afcStrings::getString( 'publicRequiredFiles' ) as $file ){
            require_once( ADVANCEDFONTCHANGERDIR . $file ); 
        }
	//Admin
	if( is_user_logged_in() && current_user_can('manage_options') ){	
		foreach( afcStrings::getString( 'adminRequiredFiles' ) as $file ){
                    require_once( ADVANCEDFONTCHANGERDIR . $file ); 
                }
		//We load admin bar menu item only in frontend. we called it in here to check if current user is allowed to edit fonts or not	
		if ( !is_admin() ){
			add_action( 'admin_bar_menu', 'pg_afc_adminbarmenu', 999 );
		}
	}
	
	global $wpdb;
	$GLOBALS['afcConfig'] = array(
				 "charset"			=> $wpdb->get_charset_collate(),
				 "fontsTable"		=> $wpdb->prefix . "afc_fonts",
				 "selectorsTable" 	=> $wpdb->prefix . "afc_selectors"
				 );
}

function afc_get_url( $status = '' ){
	$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if( $status == 'normal' ) {
        if( strpos( $url, 'afceditor') !== false )
            $url = substr($url, 0, strpos($url, "afceditor"));
        return $url;
    }
    else{
        return add_query_arg( array('afceditor'=> '1', 'afcnonce'=>wp_create_nonce( 'afc-editor-nonce' ) ), $url );
    }
}

register_activation_hook( __FILE__, 'pg_afc_install' ); 
/**
* Activation Hook
* Things that must be done on plugin activation
*/
function pg_afc_install(){
	global $wpdb;
	$GLOBALS['afcConfig'] = array(
				 "charset"			=> $wpdb->get_charset_collate(),
				 "fontsTable"		=> $wpdb->prefix . "afc_fonts",
				 "selectorsTable" 	=> $wpdb->prefix . "afc_selectors"
				);
	if( $options = get_option('afc_general_settings') ){
		$options['show_editor_btn'] = 'yes';
		update_option( 'afc_general_settings', $options );
	}
	else
		update_option( 'afc_general_settings', array( 'show_editor_btn' => 'yes' ) );
	update_option('afc_db_vertion','1.0');
	//creating plugin tables
	require_once( ADVANCEDFONTCHANGERDIR . 'inc/classes/class_afcfonts.php' );
	require_once( ADVANCEDFONTCHANGERDIR . 'inc/classes/class_afctables.php' );
	require_once( ADVANCEDFONTCHANGERDIR . 'inc/classes/class_defaults.php' ); 
	$con = new afcdefaults;
	$con->createTables();
	
}

/**
* Loading Styles And Scripts
*/
add_action( 'wp', 'afc_register_scripts' );
/**
 * Registering scripts
 */
function afc_register_scripts(){
	$afcStyles = new afcstyles;
	$editorNonce = wp_create_nonce( 'afc-editor-nonce' );
	$activateEditor = get_option('afc_general_settings');
	if( is_user_logged_in() && current_user_can('manage_options') && $activateEditor['show_editor_btn'] == 'yes' ){
		$afcSelectors = new afcselectors();
		$afcFonts = new afcfonts();
		$currentPageTypeSelectors = $afcSelectors->getByPageType();
		$allfonts = $afcFonts->getCols();
		//styles
		wp_register_style( 'afc-editor-ui', ADVANCEDFONTCHANGERURL . 'css/uimodules.css' );
		wp_register_style( 'afc-editor-loader', ADVANCEDFONTCHANGERURL . 'css/main-style.css' );
		wp_register_style( 'afc-editor-style', ADVANCEDFONTCHANGERURL . 'css/editor-style.css' );
		wp_register_style( 'afc-editor-fonts', ADVANCEDFONTCHANGERURL . 'css/editor-fonts.css' );
		wp_register_style( 'afc-editor-rtl', ADVANCEDFONTCHANGERURL . 'css/rtl.css' );
        
		//scripts
		wp_register_script( 'afc-iris-color-picker', admin_url( 'js/iris.min.js' ),
			array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ) 
		);
		wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'afc-iris-color-picker' ) );
		wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', afcstrings::getString('colorpicker') ); 
		wp_register_script( 'afc-editor-ui', ADVANCEDFONTCHANGERURL . 'js/uimodules.js' );
		wp_register_script( 'afc-editor-loader', ADVANCEDFONTCHANGERURL . 'js/defaults.js' );
		wp_localize_script( 'afc-editor-loader', 'afc_data_obj', 
			array( 'afcnonce' => $editorNonce, 'afcsiteurl' => get_bloginfo('wpurl') )
		);
		wp_register_script( 'afc-editor-js', ADVANCEDFONTCHANGERURL . 'js/editor.js' );
		wp_localize_script( 'afc-editor-js', 'afc_data_obj', 
			array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'afcnonce' => $editorNonce,
			'allfonts' => $allfonts, 'afcpluginurl' => ADVANCEDFONTCHANGERURL, 'afcsiteurl' => get_bloginfo('wpurl'), 
			'afc_strings' => afcStrings::getString('editorStrings'), 'propertyList' => afcStrings::getString("propertyList"),
			'afc_existingData' => ( is_array( $currentPageTypeSelectors ) )?  $currentPageTypeSelectors : array(),
			'afcLocalFontFacesURL' => $afcStyles->createFontGeneratorUrl('afc-editor-fonts-nonce')
			)
		);
	}

}

add_action( 'wp_enqueue_scripts', 'afc_enqueue_in_frontend' );
add_action( 'admin_enqueue_scripts', 'afc_enqueue_in_admin' );

/**
 * To load required files for plugin editor to work
 */
function afc_enqueue_in_frontend(){
	
	$afcStyles = new afcstyles;
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'afc-web-font-loader', ADVANCEDFONTCHANGERURL . 'js/webfontloader.js' );
	wp_enqueue_script( 'afc-font-loader', ADVANCEDFONTCHANGERURL . 'js/fontloader.js' );
	wp_localize_script( 'afc-font-loader', 'afc_fonts_loader_data', array( 'wf_obj' => $afcStyles->getFontLoaderObject() ) );
	$activateEditor = get_option('afc_general_settings');
	//We load this style and js files only for admins. Normal viewer do not needs this files.
	if( is_user_logged_in() && current_user_can('manage_options') && $activateEditor['show_editor_btn'] == 'yes' ){
		//styles
		wp_enqueue_style( 'afc-editor-loader' );
		//scripts
		wp_enqueue_script( 'afc-editor-loader');
	}
}

/**
 * To load required js and css files in plugin admin pages
 */
function afc_enqueue_in_admin(){
	if( current_user_can('manage_options') ){
		if( isset($_GET['page']) && strpos( $_GET['page'], 'afc_' ) !== false ){
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'afc-admin-styles', ADVANCEDFONTCHANGERURL . 'css/admin-style.css' );
            if(is_rtl()){
                wp_enqueue_style( 'afc-editor-rtl', ADVANCEDFONTCHANGERURL . 'css/admin-rtl.css' );
            }
			wp_enqueue_script( 'afc-admin-js', ADVANCEDFONTCHANGERURL . 'js/admin-script.js', array('wp-color-picker') );
		}
		
	}
	global $pagenow;
	if( !empty($pagenow) && ('post-new.php' === $pagenow || 'post.php' === $pagenow ) ){
		$afcSelectors = new afcselectors();
		wp_enqueue_style( 'afc-mce-button-style', ADVANCEDFONTCHANGERURL . 'css/mce-button.css' );
		wp_enqueue_script( 'afc-mce-popup-data', ADVANCEDFONTCHANGERURL . 'js/mce-data.js' );
		$selectorNames = array();
		foreach( $afcSelectors->getCols( array( 'selectorName', 'editorData' ) ) as $item ){
			if( $item['editorData']['isShortCode'] == 1 )
				$selectorNames[] = $item['selectorName'];
		}
		wp_localize_script( 'afc-mce-popup-data', 'afc_mce_data_object', array( 'selectorsList' => array_unique( $selectorNames ) ) ); 
	}
}

add_action( 'admin_menu', 'pg_afc_menupage' );
/**
 * To add plugin admin pages in wordpress admin menu
 */
function pg_afc_menupage(){
	add_menu_page( 'Advanced Font Changer', __('Font Changer', 'afc_textdomain'), 'manage_options', 'afc_plugin_options', 'afc_general_options', 'dashicons-facebook-alt' );
	add_submenu_page( 'afc_plugin_options', 'General',  __('General Settings', 'afc_textdomain'), 'manage_options', 'afc_plugin_options', 'afc_general_options' );
	$hook = add_submenu_page( 'afc_plugin_options', 'Manage Fonts',  __('Manage Fonts', 'afc_textdomain'), 'manage_options', 'afc_managefonts', 'afc_managefonts' );
	add_action( "load-$hook", 'fonts_table_options' );	
		add_submenu_page( null, 'Upload A Font',  __('Upload A Font', 'afc_textdomain'), 'manage_options', 'afc_fontupload', 'afc_fontupload' );
		add_submenu_page( null, 'Add External Font',  __('Add External Font', 'afc_textdomain'), 'manage_options', 'afc_external_font', 'afc_external_font' );
	$hook2 = add_submenu_page( 'afc_plugin_options', 'Manage Selectors',  __('Manage Selectors', 'afc_textdomain'), 'manage_options', 'afc_manageselectors', 'afc_manageselectors' );
	add_action( "load-$hook2", 'selectors_table_options' );
		add_submenu_page( null, 'Add Selector',  __('Add selector', 'afc_textdomain'), 'manage_options', 'afc_addnewselector', 'afc_addnewselector' );
        add_submenu_page( 'afc_plugin_options', 'Import / Export',  __('Import/Export', 'afc_textdomain'), 'manage_options', 'afc_import_export', 'afc_import_export' );
}

/**
 * To add plugin editor admin bar menu item
 */
function pg_afc_adminbarmenu( $wp_admin_bar ) {
	$activEditor = get_option('afc_general_settings');
	if( $activEditor['show_editor_btn'] == 'yes' && is_user_logged_in() && current_user_can('manage_options')){
		$args = array(
			'id'     => 'pg-afc-toggle',
			'title'  => __( 'Edit Font', 'afc_textdomain' ),
			'href'   => afc_get_url( ),
			'parent' => false, 
			'meta'   => array( 
				'class'   => 'pg-afc-togg',
                'target'  => 'blank'
				)
			);
		$wp_admin_bar->add_node( $args );
	}
}

/*
* Custome Styles
*/
add_action( 'wp_head', 'afc_inline_styles', 999 );
add_action( 'admin_head', 'afc_adminmenu_icon_style' );

/**
 * To insert style of selectors in current requestd page, head tag
 */
function afc_inline_styles() {
	$afcStyles = new afcstyles;
	$inlineStyles = $afcStyles->generateStyles('custom');
	echo '
	<style class="afccss">
	' . $inlineStyles . '
	</style>';
}

/**
 * To print styles into admin head tag
 */
function afc_adminmenu_icon_style() {
	echo '<style>
	 #toplevel_page_afc_plugin_options div.wp-menu-image:before {transform: rotate(-20deg);}
	 #toplevel_page_afc_plugin_options:hover div.wp-menu-image:before {transform: rotate(0deg);}
	</style>';
}

add_filter('upload_mimes', 'afc_add_new_fileExtensions');
/**
 * Editing Allowed Extensions For Uploaded Files
 * To add our extensions ( font formats ) to wordpress allowed extensions list for media uploader
 */
function afc_add_new_fileExtensions ( $existing_mimes = array() ) {
	$exts = array( 'eot', 'ttf', 'woff', 'svg' );
	foreach( $exts as $ext )
		$existing_mimes[ $ext ] = 'application/octet-stream'; 
	return $existing_mimes;
}

/**
* Editing query vars
*/

add_filter( 'query_vars', 'afc_add_query_vars', 10, 1 );
/**
 * To add query vars to wordpress query vars. We use this query vars to determine whether current request is for loading local font faces or not
 */
function afc_add_query_vars($vars){   
	//Variables for generating styles and font faces
	$vars[] = 'afcnonce';    
	$vars[] = 'afcfontnames';
	$vars[] = 'afceditor';
	$vars[] = 'afcsaveurl';
	return $vars;
}

/*
* Handling fontface generation
*/
add_action( 'template_redirect', 'afc_print_fonts' );
/**
 * Redirects to the font face generator function. We check to see if above registered query vars are set in the url or not.
 */
function afc_print_fonts(){
	$nonce = get_query_var( 'afcnonce', 0 );
	$qvs = $GLOBALS['wp_query']->query_vars;
	$exists = array_key_exists( 'afcfontnames', $qvs );
	if( $nonce && $exists ){
        if( $nonce == '' ){
            die('Unsecure request.');
        }
		
        if( !wp_verify_nonce( $nonce, 'afc-editor-nonce' ) 
        && !wp_verify_nonce( $nonce, 'afc-editor-fonts-nonce' ) 
        && !wp_verify_nonce( $nonce, 'afc-public-fonts-nonce' ) ){
            die('Invalid nonce.');
        }
		$fontnames = get_query_var( 'afcfontnames', 0 );
		afc_print_the_fonts( $fontnames ); //This function is in file inc/printfonts.php
		die();
	}
}

add_action( 'template_redirect', 'afc_print_editor' );
/**
 * Redirects to the print editor function. We check to see if AFC registered query vars are set in the url or not.
 */
function afc_print_editor(){
	$nonce = get_query_var( 'afcnonce', 0 );
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$activateEditor = get_option('afc_general_settings');
	if( $nonce && strpos( $url, 'afceditor' ) !== false && is_user_logged_in() && current_user_can('manage_options') && $activateEditor['show_editor_btn'] == 'yes' ){
        if( $nonce == '' ){
			die('Unsecure request.');
        }
        elseif( !wp_verify_nonce( $nonce, 'afc-editor-nonce' ) ){
			die('Invalid nonce.');
        }
		afc_print_the_editor(  ); //This function is in file inc/printeditor.php
		die();
	}
}

add_shortcode( 'afcselector', 'afc_shortcode_handler' );
/**
 * Registering The Short Code
 * To giving user ability to call a selector name in a post content. 
 */
function afc_shortcode_handler( $attr, $content = '' ){
	$selector = $attr['selector'];
    $att= "";
    if( preg_match( "/[.]./", $selector ) )
        $att = "class='" . str_replace( '.', '',$selector ) . "'";
    elseif( preg_match( "/[#]./", $selector ) )
        $att = "id='" . str_replace( '#', '',$selector ) . "'";
    else{
        $att = "class='" . $selector . "'";
    }
    if( isset( $attr['selector'] ) ){
        return "<span $att >" . $content . '</span>';
    }
}

/*
* Adding TinyMce Button For Short Code
*/
add_action( 'init', 'afc_choose_selector_button' );
/**
 * Adding filter on tinymce loader functions ouptput
 */
function afc_choose_selector_button() {
	if( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ){
		return;
	}
	if( get_user_option('rich_editing') == 'true' ){
		add_filter( 'mce_external_plugins', 'afc_mce_plugin_file' );
		add_filter( 'mce_buttons', 'afc_register_mce_button' );
	}
}

/**
 * Registers afc plugin file for tinymce
 */
function afc_mce_plugin_file( $plugin_array ){
	$plugin_array['afcselectors'] = ADVANCEDFONTCHANGERURL . 'js/mce-button.js';
	return $plugin_array;
}

/**
 * Adds afc button in wordpress editor (tinymce)
 */
function afc_register_mce_button( $buttons ){
	array_push( $buttons, "|", "afcselectors" );
	return $buttons;
}
?>