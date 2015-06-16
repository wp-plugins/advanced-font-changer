<?php
/**
* This class adds plugin default data into wp database
*/
class afcdefaults extends afctables{
	
	/**
     * Class constructor
	 */
	function __construct(){
        $this->setInfo();
	}
    
	
    function run(){
        $this->move_fonts();
        $this->createTables();
    }
    
    function move_fonts( ){
        $src = ADVANCEDFONTCHANGERDIR . 'fonts/local';
        $fonts = afcStrings::getString('defaultFonts');
        $uploaddir = wp_upload_dir();
        $dest = $uploaddir['basedir'] . '/afc-local-fonts';
        $formats = array('.eot','.ttf','.woff','.svg');
        
        //echo $dest; 
        // echo file_exists( $dest );
        if( !file_exists( $uploaddir['basedir'] ) ){
            mkdir( $uploaddir['basedir'], 0755, true );
        }
        if( !file_exists( $dest ) ){
            mkdir( $dest, 0755, true );
        }
        if( file_exists( $src ) ){
            foreach($fonts as $font){
                foreach ($formats as $format){
                    if ( file_exists( $src . '/' . $font['name'] . $format ) ){
                        rename( $src . '/' . $font['name'] . $format, $dest . '/' . $font['name'] . $format );
                    }
                }
            }
        }
    }
    
	/**
	 * Creates plugin tables
	 */
	function createTables(){
		$charset = $this->charset;
		$table_name = $this->fontsTable;
		$sql = "CREATE TABLE $table_name (
					  id mediumint NOT NULL auto_increment,
					  name varchar(255) NOT NULL,
					  status varchar(20) NOT NULL,
					  metadata text DEFAULT '' NOT NULL,
					  UNIQUE KEY id (id),
					  UNIQUE KEY name (name)
				) $charset;";
				
		if( $this->createTable( $table_name, $sql, $charset ) )
			$this->add_default_fonts();
			
		$table_name = $this->selectorsTable;
		$sql = "CREATE TABLE $table_name (
					  id mediumint NOT NULL auto_increment,
					  selectorName text NOT NULL,
					  properties text NOT NuLL,
					  pageType text NOT NULL,
					  editorData text NOT NULL,
					  UNIQUE KEY id (id)
				) $charset;";
		$this->createTable( $table_name, $sql, $charset );
	}
	
	/**
	 * Inserts default data into database
	 */
	function add_default_fonts(){
        $afcFonts = new afcfonts();
        $afcFonts->addFonts(afcStrings::getString('defaultFonts'));
        $afcFonts->addFonts(afcStrings::getString('googleFonts'));
	}
}
?>