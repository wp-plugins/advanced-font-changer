<?php
/**
* This class adds plugin default data into wp database
*/
class afcdefaults extends afctables{
	var $charset;
	var $fontsTable;
	var $selectorsTable;
	
	/**
     * Class constructor
	 */
	function __construct(){
		global $afcConfig;
		$this->charset = $afcConfig["charset"];
		$this->fontsTable = $afcConfig["fontsTable"];
		$this->selectorsTable = $afcConfig["selectorsTable"];
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