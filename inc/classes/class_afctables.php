<?php
/**
* This class is for working with plugin data tables
*/
class afctables{

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