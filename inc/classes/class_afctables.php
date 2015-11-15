<?php
/**
* This class is for working with plugin data tables
*/
class afctables{
    var $charset;
	var $fontsTable;
	var $selectorsTable;

    
    function setInfo(){
        global $afcConfig;
		$this->charset = $afcConfig["charset"];
		$this->fontsTable = $afcConfig["fontsTable"];
		$this->selectorsTable = $afcConfig["selectorsTable"];
    }
    
    /**
     * To create a table
	 * @param string $tableName 
	 * @param string $sql 
	 * @return bool
	 */
	function createTable( $tableName, $sql ){
		if( !$this->tableExists( $tableName ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
     * Checks wether table exists or not
	 * @param string $tableName 
	 * @return bool
	 */
	function tableExists( $tableName ){
		global $wpdb;
		return $wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName;
	}

}
?>