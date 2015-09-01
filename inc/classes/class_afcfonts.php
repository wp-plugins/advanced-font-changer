<?php
/**
* This class is for insert,update or get requested font names from database
*/
class afcfonts{
	var $fontsTable;
	
	/**
	* Class constructor
	*/
	function afcfonts(){
		global $afcConfig;
		$this->fontsTable = $afcConfig["fontsTable"];
	}
	
	/**
     * This function is for adding a font name
	 * @param array $arr 
	 */
	function addFonts($arr){
		$this -> updatefonts( 'add', $arr );
	}
	

	/**
     * Removes all available fonts
	 */
	function reset(){
		$this -> updatefonts( 'empty' );
	}
	
	/**
     * Returns requested fonts based on the value in name or status columns
	 * @param string $control 
	 * @param array $arr 
	 * @return array or false
	 */
	function getFonts( $control, $arr = array() ){
		global $wpdb;
		$cols = array( 'id', 'name', 'status' );
		$sql = '';

		if( in_array( $control, $cols ) && count( $arr ) > 0 ){
			$count = count($arr);
			$sql = "SELECT * FROM $this->fontsTable WHERE ";
			foreach( $arr as $key=>$val ){
				if( $count != $key + 1  )
					$sql .= "$control = '$val' OR ";
				else
					$sql .= "$control = '$val'";
			}
			$result = $wpdb->get_results( $sql, ARRAY_A );
            if( !empty( $result ) )
                $result = $this->unserializeIt( $result );
            return $result;
		}
		else return false;
	}
	
	/**
     * Returns all fields in specified columns from fonts table
	 * @param array $cols 
	 * @return array
	 */
	function getCols( $cols = array()  ){
		global $wpdb;
		$sql = "SELECT ";
		if( count( $cols ) > 0 ){
			foreach( $cols as $key=>$val ){
				$sql .= "$val ";
				if( count( $cols ) != $key + 1  )
					$sql .= ", ";
			}
		}
		else{
			$sql .= "* ";
		}
		$sql .= "FROM $this->fontsTable ORDER BY status ASC, name ASC;";
		$result = $wpdb->get_results( $sql, ARRAY_A );
		if( in_array( 'metadata' , $cols ) ){
			$result = $this->unserializeIt( $result );
		}
		return $result;
	}

	/**
     * Returns count of available fonts
	 * @return int
	 */
	function getCount( $isSearch = false, $search = NULL ){
		global $wpdb;
        if( $isSearch )
            $sql = $wpdb->prepare( "SELECT count(*) FROM $this->fontsTable WHERE name LIKE '%%%s%%' OR status LIKE '%%%s%%' OR metadata LIKE '%%%s%%'", $search, $search, $search );
        else
            $sql = "SELECT count(*) FROM $this->fontsTable;";
		return $wpdb->get_var( $sql );
	}

	/**
     * Returns limited number of available fonts for shwoing then in manage fonts page
	 * @param int $limit 
	 * @param int $offset 
	 * @return array
	 */
	function getLimitedRows( $limit, $offset ){
		global $wpdb;
		$sql = "SELECT * FROM $this->fontsTable ORDER BY status ASC, name ASC LIMIT $limit OFFSET $offset";
		$result = $wpdb->get_results( $sql, ARRAY_A );
        if( !empty($result) )
            $result = $this->unserializeIt( $result );
		return $result;
	}
    
    /**
     * Searches fonts table for $search and returns limited number of results
     * @param string $search 
     * @param int $limit 
     * @param int $offset 
     * @return array
     */
    function getSearchedRows( $search, $limit, $offset ){
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $this->fontsTable WHERE name LIKE '%%%s%%' OR status LIKE '%%%s%%' OR metadata LIKE '%%%s%%' ORDER BY status ASC, name ASC LIMIT $limit OFFSET $offset", $search, $search, $search );
        $result = $wpdb->get_results( $sql, ARRAY_A );
        if( !empty( $result ) )
            $result = $this->unserializeIt( $result );
        return $result;
    }
	
	/**
     * Gets fonts based on two conditions , 'name' and 'status'
	 * @param string $status 
	 * @param array $namesArr 
	 * @return array or false
	 */
	function getByNameAndStatus( $status, $namesArr ){
		if( count( $namesArr ) > 0 ){
			global $wpdb;
			$sql = "SELECT name,metadata FROM $this->fontsTable WHERE status ='$status' AND ( ";
			foreach( $namesArr as $key=>$val ){
				if( count( $namesArr ) != $key + 1  )
					$sql .= "name='$val' OR ";
				else
					$sql .= "name='$val' )";
			}
			$result = $wpdb->get_results( $sql, ARRAY_A );
            if( !empty($result) )
                $result = $this->unserializeIt( $result );
            return $result;
		}
		return false;
	}
	
	/**
     * Updates the plugin fonts list by received font names
     * Modes are only 'add', 'remove' or 'empty'.
	 * @param string $mode 
	 * @param array $arr 
	 * @return array or false
	 */
	function updateFonts( $mode , $arr = array() ){
		global $wpdb;
		$notEmpty = ( count($arr) > 0 )? 1 : 0 ;
		$sql = '';
		$result = array();
		if( $mode == 'empty'){
			$sql = "truncate $this->selectorsTable";
			$result = $wpdb->query( $sql );
		}
		elseif( $mode == 'add' && $notEmpty ){
			$count = count($arr);
			$sql = "INSERT INTO $this->fontsTable VALUES ";
			foreach( $arr as $key=>$val ){
				$sql .= "( NULL, '" . mysql_real_escape_string( $val['name'] ) . "', '". mysql_real_escape_string( $val['status'] ) ."' , '" . mysql_real_escape_string( serialize( $val['metadata'] ) ) . "' )";
				if( $count != $key + 1 )
					$sql .= ', ';
			}
			$sql .= " ON DUPLICATE KEY UPDATE name=VALUES(name),metadata=VALUES(metadata);";
			$result = $wpdb->query( $sql );
		}
		elseif( $mode == 'remove' && $notEmpty ) {
			$count = count($arr);
			$sql = "DELETE FROM $this->fontsTable WHERE id IN (";
			$hasFile = array();
			foreach( $arr as $key=>$val ){
				if( $val['status'] == 'local' )
					$hasFile[] = $val;
				if( $count != $key + 1 )
					$sql .= $val['id'] . ",";
				else
					$sql .= $val['id'] . ")";
			}
			$result = $wpdb->query( $sql );
			
			if( count( $hasFile ) > 0)
				$this->removeFontFiles( $hasFile );
		}
		
		$modes = array('add', 'remove', 'empty' );
		if( in_array( $mode, $modes ) ){
			return $result;
		}
		else{ 
			return false;
		}
	}
	
	/**
     * Removes font file from plugin directory
     * If a user removes a local font this function removes its file from plugin directory
	 * @param array $fonts 
	 */
	function removeFontFiles( $fonts ){
		if( !is_array( $fonts ) || count( $fonts ) < 1 )
			return;
		foreach( $fonts as $key ){
			if( $key['status'] == 'local' ){
				$formats = array( 'eot', 'ttf', 'woff', 'svg' );
				$address = ADVANCEDFONTCHANGERDIR . 'fonts/' . $key['status'] . '/';
				foreach( $formats as $key2 ){
					$temp = $address . $key2 . '/' . $key['name'] . '.' . $key2;
					if( file_exists( $temp ) )
						unlink( $temp );
				}
			}
		}
	}
	
	/**
     * To unserialize metadata field of fonts (when we extract them from database)
	 * @param mixed $arr 
	 * @return mixed
	 */
	function unserializeIt( $arr ){
		$data = array();
		foreach( $arr as $font ){
			$font['metadata'] = unserialize( $font['metadata'] );
			$data[] = $font;
		}
		return $data;
	}
	
    /**
     * Converts specified fields from array to string
     * @param array $arr 
     * @return array
     */
	function implodeIt( $arr ){
		$editedArr = array();
        if( is_array($arr) ){
            foreach( $arr as $key ){
                $output = '';
                if( isset( $key['metadata'] ) && count( $key['metadata'] ) > 0 ){
                    foreach( $key['metadata'] as $md=>$val ){
                        $output = '[' . $md . ' = ';
                        if( is_array($val) )
                            foreach ($val as $id){
                                $output .= ',' . $id;
                            }
                        else{
                            $output .= $val;
                        }
                        $output .= ']';
                    }
                    $key['metadata'] = trim( $output );
                }
                else{
                    $key['metadata'] = '';
                }
                
                
                $editedArr[] = $key;
            }
        }
		return $editedArr;
	}
	
	/**
     * Checks wether requested font exists or not
	 * @param string $fontName 
	 * @return bool
	 */
	function fontExists( $fontName ){
		$result = $this->getFonts( 'name', array( $fontName ) );
		return ( is_array( $result ) && count($result) > 0 )? true : false ;
	}
    
    /**
     * Checks whether specified array is a valid font or not
     * @param array $arr 
     * @return bool
     */
	function isValidFont( $arr ){
		if( !isset( $arr['name'] ) || !isset( $arr['status'] ) || !isset( $arr['metadata'] ) 
		|| $arr['name'] == '' || $arr['name'] == '' )
			return false;
		else
			return true;
	}
}
?>