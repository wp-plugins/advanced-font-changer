<?php
/**
* This class contains all plugin strings
*/
class afcStrings{

	/**
     * Returns requested string
	 * @param string $strname 
	 * @return array
	 */
	static function getString( $strname ){
		//List of page types
		$pageTypeStrings = array(
			'home'    	 => __( 'Home Page', 'afc_textdomain' ),
			'archive'  	 => __( 'Archive', 'afc_textdomain' ),
			'cat'     	 => __( 'Category(s) Archive', 'afc_textdomain' ),
			'author'   	 => __( 'Author Page(s)', 'afc_textdomain' ),
			'single' 	 => __( 'Single Post(s)', 'afc_textdomain' ),
			'page'		 => __( 'Page(s)', 'afc_textdomain' ),
			'tagarchive' => __( 'Tag(s) Archive', 'afc_textdomain' ),
			'hastag' 	 => __( 'This Tag (s) (For posts)', 'afc_textdomain' )
		);

		//list of all supported pagetypes
		$editorPageTypes = array( 			
			'home' => __( 'Home Page', 'afc_textdomain' ),
			'cat' => __( 'Category', 'afc_textdomain' ),
			'archive' => __( 'Archive', 'afc_textdomain' ),
			'author' => __( 'Author', 'afc_textdomain' ),
			'single' => __( 'Single Post', 'afc_textdomain' ),
			'page' => __( 'Page', 'afc_textdomain' ),
			'tagarchive' => __( 'Tag Archive', 'afc_textdomain' ),
			'hastag' => __( 'Has Tag', 'afc_textdomain' )
		);
		
		//List of files for including
		$publicRequiredFiles = array(
			'inc/classes/class_afctables.php', //database connector
			'inc/classes/class_afcselectors.php', //for selectors
			'inc/classes/class_afcfonts.php', //for fonts
			'inc/classes/class_afcgeneratestyle.php', //for generating styles
			'inc/printfonts.php'
		);

		$adminRequiredFiles = array(
			'inc/afc-ajax.php', //for editor ajax
			'inc/printeditor.php', //for editor ajax
			'inc/classes/class_wp-list-table.php', //for lists
			//admin pages
			'inc/admin/general.php',
			'inc/admin/import-export.php',
			'inc/admin/uploadfonts.php',
			'inc/admin/fontslist.php',
			'inc/admin/addexternalfont.php',
			'inc/admin/selectorslist.php',
			'inc/admin/addselector.php',
			'inc/admin/editselector.php',
			'inc/admin/editfont.php',
			'inc/classes/class_fontslist.php',
			'inc/classes/class_selectorslist.php'
		);

		//List of built in fonts
		$defaultFonts = array(
			array( 'name' => 'canterbury',   'status' => 'local', 'metadata' => array() ), array( 'name' => 'lexifont4',    'status' => 'local', 'metadata' => array() ),
			array( 'name' => 'digit-square', 'status' => 'local', 'metadata' => array() ), array( 'name' => 'merrymurnisc', 'status' => 'local', 'metadata' => array() ),
			array( 'name' => 'erthqake',     'status' => 'local', 'metadata' => array() ), array( 'name' => 'scripalt',     'status' => 'local', 'metadata' => array() ),
			array( 'name' => 'eskargot',     'status' => 'local', 'metadata' => array() ), array( 'name' => 'scriptin',     'status' => 'local', 'metadata' => array() ),
			array( 'name' => 'ilits',        'status' => 'local', 'metadata' => array() ), array( 'name' => 'junegull',     'status' => 'local', 'metadata' => array() )
		);

		$googleFonts = array(
			array( 'name' => 'Pacifico',    'status' => 'google', 'metadata' => array() ), array( 'name' => 'Ubuntu',     'status' => 'google', 'metadata' => array() ),
			array( 'name' => 'Nova Square', 'status' => 'google', 'metadata' => array() ), array( 'name' => 'PT Sans',    'status' => 'google', 'metadata' => array() ),
			array( 'name' => 'Michroma',    'status' => 'google', 'metadata' => array() ), array( 'name' => 'Oswald',     'status' => 'google', 'metadata' => array() ),
			array( 'name' => 'News Cycle',  'status' => 'google', 'metadata' => array() ), array( 'name' => 'Roboto',     'status' => 'google', 'metadata' => array() ),
			array( 'name' => 'Halant',      'status' => 'google', 'metadata' => array() ), array( 'name' => 'Droid Sans', 'status' => 'google', 'metadata' => array() ),
			array( 'name' => 'Sarpanch',    'status' => 'google', 'metadata' => array() )
		);
		//Plugin editor strings
		$editorStrings = array(
			'selectedelement' => __( 'Generated Selector: ', 'afc_textdomain' ),
			'choosetaxonomy' => __( 'Page Type:', 'afc_textdomain' ),
			'choosetaxonomyhelp' => __( 'To use in all pages don\'t active anyone', 'afc_textdomain' ),
			'clear' => __( 'Unlock', 'afc_textdomain' ),
			'save' => __( 'Save', 'afc_textdomain' ),
			'switchtab' => __( 'Switch Tab', 'afc_textdomain' ),
			'noelemselected' => __( 'No element selected.', 'afc_textdomain' ),
            'servererror' => __( 'Server returned an invalid output. Maybe you are not logged in, or your user has no sufficient access to edit site theme. Here is server response', 'afc_textdomain' ),
            'changessaved' => __( 'Your Changes Saved!', 'afc_textdomain' ),
            'pleaselogin' => __( 'You are not logged in, or your user has no sufficient access to edit site theme.', 'afc_textdomain' ),
            'savefailed' => __( 'Unable To Save.', 'afc_textdomain' ),
            'bodyreached' => __('You have reached body tag !. There is no more parents for this tag.','afc_textdomain'),
			'nothingtosave' => __( 'No data to save . Please first make some changes!', 'afc_textdomain' ),
            'elemremoved' => __( 'Selector Successfully Removed!', 'afc_textdomain' ),
			'reset' => __( 'Reset This', 'afc_textdomain' ),
			'fontnotexist' => __( 'Requested font name not exists.', 'afc_textdomain' ),
			'force' => __( 'Force Change', 'afc_textdomain' ),
			'nofontselected' => __( 'No font is selected to be forced !', 'afc_textdomain' ),
			'properties' => __( 'Choose Property: ', 'afc_textdomain' ),
			'h-shadow' => __( 'H-Shadow: ', 'afc_textdomain' ),
			'v-shadow' => __( 'V-Shadow: ', 'afc_textdomain' ),
			'blur' => __( 'Blur: ', 'afc_textdomain' ),
			'color' => __( 'Color: ', 'afc_textdomain' ),
			'unset' => __( 'Unset', 'afc_textdomain' ),
			'none' => __( 'None', 'afc_textdomain' ),
			'underline' => __( 'Under Line', 'afc_textdomain' ),
			'overline' => __( 'Over Line', 'afc_textdomain' ),
			'linethrough' => __( 'Line Through', 'afc_textdomain' ),
			'tab1' => __( 'Return', 'afc_textdomain' ),
			'tab2' => __( 'Go To Selector Properties', 'afc_textdomain' ),
			'normal' => __( 'Normal', 'afc_textdomain' ),
			'italic' => __( 'Italic', 'afc_textdomain' ),
			'oblique' => __( 'Oblique', 'afc_textdomain' )
		);
		
		$propertyList = array(
			'fontfamily'     => __( 'Font Family: ', 'afc_textdomain' ),
			'fontsize'       => __( 'Font Size: ', 'afc_textdomain' ),
			'fontweight'     => __( 'Font Weight: ', 'afc_textdomain' ),	
			'fontstyle'      => __( 'Font Style: ', 'afc_textdomain' ),	
			'textcolor'      => __( 'Text Color: ', 'afc_textdomain' ),
			'textdecoration' => __( 'Text Decoration: ', 'afc_textdomain' ),
			'textshadow'     => __( 'Text Shadow: ', 'afc_textdomain' )
		);
		
		$colorPicker = array(
			'clear'         => __( 'Clear', 'afc_textdomain' ),
			'defaultString' => __( 'Default', 'afc_textdomain' ),
			'pick'          => __( 'Select Color', 'afc_textdomain' )
		);
		
		//Tabs strings for admin pages
		$manageFonts = array( 
            'afc_plugin_options' => __( 'General Options', 'afc_textdomain' ), 
            'afc_managefonts'    => __( 'Manage Fonts', 'afc_textdomain' ), 
            'afc_fontupload'     => __( 'Upload Font', 'afc_textdomain' ), 
            'afc_external_font'  => __( 'Add External Font', 'afc_textdomain' ) 
            );

		$manageSelectors = array( 
            'afc_plugin_options'  => __( 'General Options', 'afc_textdomain' ), 
            'afc_manageselectors' => __( 'Manage Selectors', 'afc_textdomain' ), 
            'afc_addnewselector'  => __( 'Add New Selector', 'afc_textdomain' ) 
            );

		$decorations = array( 'none', 'underline', 'overline', 'line-through' );
		
		$weightslist = array( 100, 200, 300, 400, 500, 600, 700, 800, 900 );
		
		$fontstyles = array( 'normal', 'italic', 'oblique' );
		
		switch( $strname ){
			case 'pagetype':
				return $pageTypeStrings;
			case 'pagetypes':
				return $editorPageTypes;
			case 'defaultFonts':
				return $defaultFonts;
			case 'googleFonts':
				return $googleFonts;
			case 'editorStrings':
				return $editorStrings;
			case 'adminRequiredFiles':
				return $adminRequiredFiles;
			case 'publicRequiredFiles':
				return $publicRequiredFiles;
			case 'manageFonts':
				return $manageFonts;
			case 'manageSelectors':
				return $manageSelectors;
			case 'decorations':
				return $decorations;
			case 'weights':
				return $weightslist;
			case 'propertyList':
				return $propertyList;
			case 'fontstyles':
				return $fontstyles;
			case 'colorpicker':
				return $colorPicker;
			default:
				return 0;
		}
	}
}
?>