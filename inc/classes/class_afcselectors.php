<?php
/**
 * This class is for insert,update or fetching requested font names from database
 */
class afcselectors{
	var $selectorsTable;
	
	/**
     * Class constructor
     */
	public function afcselectors(){
		global $afcConfig;
		$this->selectorsTable = $afcConfig["selectorsTable"];
	}
	
	/**
     * This function is for adding a selector name
     * @param array $arr 
     */
	public function addelems( $arr ){
		$this->updateElems( 'add', $arr );
	}
	
	/**
     * Removes all selectors
     */
	public function reset(){
		$this->updateElems( 'empty' );
	}

	/**
     * Extracts elements from database
     * there is three type of usage
     * 1- sending 'id' or 'selectorName' and array of Selector Names
     * 2- sending 'pagetype' or 'properties' and array of pagetypes
     * 3- sending 'all' to receive all available selectors
     * @param string $mode 
     * @param array $arr 
     * @return array or false
     */
	public function getElems( $mode = 'all', $arr = array() ){
		global $wpdb;
		$notEmpty = ( count($arr) > 0 )? 1 : 0 ;
		$similarModes = array( 'id', 'selectorName' );
		$similarModes2 = array( 'pageType', 'properties' );
		$allModes = array( 'all', 'id', 'selectorName', 'pageType', 'properties' );
		$sql = '';
		$result = array();
		if( $mode == 'all' ){
			$sql = "SELECT * FROM $this->selectorsTable";
		}
		elseif( in_array( $mode, $similarModes ) && $notEmpty ){
			$count = count( $arr );
			$sql = "SELECT * FROM $this->selectorsTable WHERE ";
			foreach( $arr as $key=>$val ){
				if( $count != $key + 1  )
					$sql .= "$mode = '$val' OR ";
				else
					$sql .= "$mode = '$val'";
			}
		}
		elseif( in_array( $mode, $similarModes2 ) && $notEmpty ){
			$count = count( $arr );
			$sql = "SELECT * FROM $this->selectorsTable WHERE ";
			foreach( $arr as $key=>$val ){
				if( $count != $key + 1  )
					$sql .= "$mode LIKE '%$val%' OR ";
				else
					$sql .= "$mode LIKE '%$val%'";
			}
		}
		
		if( in_array( $mode, $allModes ) ){
			$result = $wpdb->get_results( $sql, ARRAY_A );
            if( !empty($result) )
                $result = $this->unserializeIt( $result );
            return $result;
		}
		else{
			return false;
		}
	}
	
	/**
     * This function returns all fields in specified columns from fonts table
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
		
		$sql .= "FROM $this->selectorsTable";
		$result = $wpdb->get_results( $sql, ARRAY_A );
		if( in_array( 'properties' , $cols ) || in_array( 'pageType' , $cols ) || in_array( 'editorData' , $cols ) ){
			$result = $this->unserializeIt( $result );
		}
		return $result;
	}

	/**
     * Gets the count of available selectors
     * @return int
     */
	function getCount( $isSearch = false, $search = NULL ){
		global $wpdb;
        if( $isSearch )
            $sql = $wpdb->prepare("SELECT count(*) FROM $this->selectorsTable WHERE selectorName LIKE '%%%s%%' OR pageType LIKE '%%%s%%' OR properties LIKE '%%%s%%'", $search, $search,$search);
        else
            $sql = "SELECT count(*) FROM $this->selectorsTable;";
		return $wpdb->get_var( $sql );
	}
	
	/**
     * Returns limited number of available selectors for showing then in manage fonts page
     * @param int $limit 
     * @param int $offset 
     * @return array
     */
	function getLimitedRows( $limit, $offset ){
		global $wpdb;
		$sql = "SELECT * FROM $this->selectorsTable LIMIT $limit OFFSET $offset";
		$result = $wpdb->get_results( $sql, ARRAY_A );
        if( !empty( $result ) )
            $result = $this->unserializeIt( $result );
        return $result;
	}
    
    /**
     * Searches selectors table for $search and returns limited number of results
     * @param string $search 
     * @param int $limit 
     * @param int $offset 
     * @return array
     */
    function getSearchedRows( $search, $limit, $offset ){
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $this->selectorsTable WHERE selectorName LIKE '%%%s%%' OR pageType LIKE '%%%s%%' OR properties LIKE '%%%s%%' LIMIT $limit OFFSET $offset", $search, $search, $search );
        $result = $wpdb->get_results( $sql, ARRAY_A );
        if( !empty( $result ) )
            $result = $this->unserializeIt( $result );
        return $result;
    }

	/**
     * Updates the plugin selectors list by received selectors
     * mode is only 'add', 'remove' or 'empty'.
     * @param string $mode 
     * @param array $arrs 
     * @return array or false
     */
	function updateElems( $mode , $arrs = array() ){
		global $wpdb;
		$notEmpty = ( count($arrs) > 0 )? 1 : 0 ;
		$arr = $this->trimIt( $arrs );
		$result = array();
		if( $mode == 'empty' ){
			$sql = "truncate $this->selectorsTable";
			$result = $wpdb->query( $sql );
		}
		elseif( $mode == 'add' && $notEmpty ){

			$data = array();
			foreach( $arr as $key ){
				if( !$this->isValidSelector( $key ) ){
					continue;
				}
				$result = $this->getSimilarSelectors( $key );
				$result = $this->getForChecks( $result, $key );
				if( count( $result ) >= 1 ){
					if( $this->isEqualSelectors( $key, $result ) !== false )
						continue;
				}
				$data[] = array( 'id' 			=> ( $key['editorData']['isNew'] == 0 )? $key['id'] : 'NULL', 
								 'selectorName' => mysql_real_escape_string( $key['selectorName'] ), 
								 'properties'	=> mysql_real_escape_string( serialize( $key['properties'] ) ),
								 'pageType'		=> mysql_real_escape_string( serialize( $key['pageType'] ) ),
								 'editorData'	=> mysql_real_escape_string( serialize( $key['editorData'] ) )
						  );
			}
			if( count( $data ) > 0 ){
				$count = count( $data );
				$sql = "INSERT INTO $this->selectorsTable VALUES ";
				foreach( $data as $key=>$val ){
					$sql .= "('" . $val['id'] . "','". $val['selectorName'] ."','" . $val['properties'] . "','" . $val['pageType'] . "','" . $val['editorData'] . "')";
					if( $count != $key + 1 )
						$sql .= ',';
				}
				$sql .= " ON DUPLICATE KEY UPDATE selectorName=VALUES(selectorName),properties=VALUES(properties),pageType=VALUES(pageType),editorData=VALUES(editorData);";
				$result = $wpdb->query( $sql );
			}
		}
		elseif( $mode == 'remove' && $notEmpty ){
			$count = count( $arr );
			$sql = "DELETE FROM $this->selectorsTable WHERE id IN (";
			foreach( $arr as $key=>$val )
				if( $count != $key + 1 )
					$sql .= $val['id'] . ",";
				else
					$sql .= $val['id'] . ")";
			$result = $wpdb->query( $sql );
		}
		
		$modes = array('add', 'remove', 'empty' );
		if ( in_array( $mode, $modes )){
			return $result;
		}
		else{ 
			return false;
		}
	}
	
	/**
     * Checks selectors that are similar to $thisone, if their id was not same as $thisone then it returns them as must check selectors
     * @param array $result 
     * @param array $thisOne 
     * @return array
     */
	function getForChecks( $result, $thisOne ){
		$forCheck = array();
		foreach( $result as $existingSelector )
			if( $existingSelector['id'] != $thisOne['id'] )
				$forCheck[] = $existingSelector;
		return $forCheck;
	}
	
	/**
     * Selects selectors that are similar to $key ( same selectorName and similar pageTypes )
     * @param array $key 
     * @return array
     */
	function getSimilarSelectors( $key ){
		global $wpdb;
		$sql = "SELECT * FROM $this->selectorsTable WHERE selectorName = '$key[selectorName]' AND ( ";
		if( !in_array( 'all', $key['pageType'] ) ){
			foreach( $key['pageType'] as $pt=>$val ){
				$sql .= "pageType LIKE '%$pt%' OR ";
			}
			$sql .= " pageType LIKE '%all%')";	
		}
		else{
			$types = afcstrings::getString('pagetypes');
			foreach( $types as $pt=>$val )
				$sql .= "pageType LIKE '%$pt%' OR ";
			$sql .=  "pageType LIKE '%all%' )";
		}
		$result = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $result ) > 0 )
			$result = $this->unserializeIt( $result );
		return $result;
	}

	/**
     * Checks wether $key is in conflict with one of already available selectors or not
     * @param array $key 
     * @param array $arr 
     * @return string or false
     */
	function isEqualSelectors( $key, $arr ){
		$notFilterable = array( 'all', 'home', 'archive' );
		foreach( $arr as $selector ){
			foreach( $selector['pageType'] as $pt=>$val ){
				foreach( $key['pageType'] as $pt2=>$val2 ){			
					if( $pt == 'all' )
						return 'Following selector has pageType which is in conflict with current selector: ' . 'Selector ID: ' . $selector['id'] . ' Selector Name: ' . $selector['selectorName'];
					elseif( $pt == $pt2 ){
						if( in_array( $pt2, $notFilterable ) || !is_array( $val ) || !is_array( $val2 ) )
							return "Following selector is same as current selector ( selectorName's are same and pageTypes are in conflict ): " . ' Selector ID: ' . $selector['id'] . ' Selector Name: ' . $selector['selectorName'];
						elseif( is_array( $val ) && is_array( $val2 ) ){
							if( $val['status'] == $val2['status'] ){
								foreach( $val['valueArr'] as $ptVal )
									if( in_array( $ptVal, $val2['valueArr'] ) )
										return 'One of page ids in following selector is in conflict with current selector: '  . $selector['selectorName'] . ' conflicting page id is: ' . $ptVal . ' . This page id is included in both selectors.';
							}
							elseif( $val['status'] != $val2['status'] ){
								$includedOne = ( $val['status'] == 'include' )? $val['valueArr'] : $val2['valueArr']; //this one is including some ids.
								$excludedOne = ( $val['status'] != 'include' )? $val['valueArr'] : $val2['valueArr']; //this one must atleat excludes above ids.
								foreach( $includedOne as $ptID )
									if( !in_array( $ptID, $excludedOne ) )
										return 'One of page ids in following selector is in conflict with current selector: '  . $selector['selectorName'] . ' conflicting page id is: ' . $ptID . ' . One of selectors is included this id , but the other one is not excluded it.';
							}
						}
					}
				}
			}
		}
		return false;
	}

	/**
     * Checks whether specified array is a valid selector or not
     * @param array $arr 
     * @return bool
     */
	function isValidSelector( $arr ){
		if( !isset( $arr['selectorName'] ) || !isset( $arr['properties'] ) || !isset( $arr['pageType'] ) 
		|| !isset( $arr['editorData'] ) || $arr['selectorName'] == '' || empty( $arr['pageType'] ) )
			return false;
		else
			return true;
	}

	/**
     * Extracts $key from $arr
     * @param string $key 
     * @param array $arr 
     * @return array
     */
	function extract( $key, $arr ){
		$names = array();
		$notEmpty = ( count( $arr ) > 0 )? 1 : 0 ;
		if( $key == 'selectorName' && $notEmpty  ){
			foreach( $arr as $val)
				$names[] = $val['selectorName'];
		}
		elseif( $key == 'fontName' && $notEmpty  )
			foreach( $arr as $val )
				if( isset( $val['properties']['fontName']['name'] ) )
					$names[] = $val['properties']['fontName']['name'];
		return $names;
	}

	/**
     * Detects what page type currently is loading. 
     * Since in every page we must load global selectors and special selectors to that page, 
     * @return array
     */
	function getByPageType(){
		$pageTypes = $this->getCurrentPageType(); //array
		$returneddata = $this->getElems( 'pageType', $pageTypes );
		$returneddata = $this->filterTypeByValueArr( $returneddata, $pageTypes );
		return $returneddata;
	}

	/**
     * Detects current page type. Then it adds 'all' to list, which means selectors that has 'all' in their pagetype keys.
     * @return array
     */
	function getCurrentPageType(){
		$pageType = array();
		if( is_home() )
			$pageType[] = 'home';
		if( is_category() )
			$pageType[] = 'cat';
		if( is_archive() )
			$pageType[] = 'archive';
		if( is_single() )
			$pageType[] = 'single';
		if( is_author() )
			$pageType[] = 'author';
		if( is_page() )
			$pageType[] = 'page';
		if( is_tag() )
			$pageType[] = 'tagarchive';
		if( has_tag() )
			$pageType[] = 'hastag';

		$pageType[] = 'all';
		return $pageType;
	}

	/**
     * Checks wether selectors are specified globaly or they are for specific (page) ids, then it returns selectors that are allowed to being loaded in current page
     * @param array $arr 
     * @param array $pts 
     * @return array
     */
	function filterTypeByValueArr( $arr, $pts ){
		$notFilterable = array( 'all', 'home', 'archive' );
		$editedArray = array();
		foreach( $arr as $key ){
			$isAlreadyAdded = in_array( $key, $editedArray );
			if( !$isAlreadyAdded ){
				foreach( $key['pageType'] as $pt=>$val ){
					$isNotFilterable = in_array( $pt, $notFilterable );
					if( $isAlreadyAdded ){
						break;
					}
					elseif( $isNotFilterable ){
						$editedArray[] = $key;
					}
					elseif( isset( $key['pageType'][$pt]['valueArr'] ) ){
						$ptStatusInc = $key['pageType'][$pt]['status'] == 'include';
						$idArr = $key['pageType'][$pt]['valueArr'];
						switch( $pt ){
							case 'cat':
								if( ( is_category( $idArr ) && $ptStatusInc ) || ( !is_category( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
							case 'single':
								if( ( is_single( $idArr ) && $ptStatusInc ) || ( !is_single( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
							case 'author':
								if( ( is_author( $idArr ) && $ptStatusInc ) || ( !is_author( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
							case 'page':
								if( ( is_page( $idArr ) && $ptStatusInc ) || ( !is_page( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
							case 'tagarchive':
								if( ( is_tag( $idArr ) && $ptStatusInc ) || ( !is_tag( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
							case 'hastag':
								if( ( has_tag( $idArr ) && $ptStatusInc ) || ( !has_tag( $idArr ) && !$ptStatusInc ) ){
									$editedArray[] = $key;
									$isAlreadyAdded = true;
								}
								break;
						}
					}
					elseif( $key['pageType'][$pt] == 'empty' ){
						$editedArray[] = $key;
					}
					$isAlreadyAdded = in_array( $key, $editedArray );
				}
			}
		}
		return $editedArray;
	}

	/**
     * Trims the selectorname to make sure there is no spaces in it !
     * @param array $arr 
     * @return array
     */
	function trimIt( $arr ){
		$editedArr = array();
		if( is_array( $arr ) )
			foreach( $arr as $key ){
				$key['selectorName'] = trim( $key['selectorName'] );
				$editedArr[] = $key;
			}
		return $editedArr;
	}

	/**
     * To unserialize fields that was serialized
     * @param array $arr 
     * @return array
     */
	function unserializeIt( $arr ){
		$data = array();
		foreach( $arr as $key ){
			if( isset( $key['properties'] ) )
				$key['properties'] = unserialize( $key['properties'] );
			if( isset( $key['pageType'] ) )
				$key['pageType'] = unserialize( $key['pageType'] );
			if( isset( $key['editorData'] ) )
				$key['editorData'] = unserialize( $key['editorData'] );
			$data[] = $key;
		}
		return $data;
	}

	/**
     * Implodes the selector fields for showing them in manage selectors table
     * @param array $arr 
     * @return array
     */
	function implodeIt( $arr ){
		$editedArr = array();
        if( is_array($arr) ){
		    foreach( $arr as $key ){
			    $output = '';
                $propertyList = array(
                    'fontName'     => __( 'Font Family: ', 'afc_textdomain' ),
                    'fontSize'       => __( 'Font Size: ', 'afc_textdomain' ),
                    'fontWeight'     => __( 'Font Weight: ', 'afc_textdomain' ),	
                    'fontStyle'      => __( 'Font Style: ', 'afc_textdomain' ),	
                    'textColor'      => __( 'Text Color: ', 'afc_textdomain' ),
                    'textDecoration' => __( 'Text Decoration: ', 'afc_textdomain' ),
                    'textShadow'     => __( 'Text Shadow: ', 'afc_textdomain' )
                );
                if( isset( $key['properties'] ) && count( $key['properties'] ) > 0 ){
                    foreach ( $propertyList as $prop=>$val ) {
                	    if ( isset($key['properties'][$prop]) ){
                            if(!is_array($key['properties'][$prop]) ){
                                $output .= '[' . $val . $key['properties'][$prop] . ';]';
                            }
                            else{
                                $counter = 0;
                                $output .= '[' . $val;
                                foreach ( $key['properties'][$prop] as $property=>$value ){
                            	    if ( (count( $key['properties'][$prop] )-1) != $counter ){
                                        $output .= $property . ' = '. $value . ',';                              	
                                    }
                                    else {
                                	    $output .= $property . ' = '. $value . ';]';
                                    }
                                    $counter++;
                                }
                            }
                        }
                    }
                    $key['properties'] = trim( $output );
                }
                else{
                    $key['properties'] = '';
                }
                if( isset( $key['pageType'] ) && count( $key['pageType'] ) > 0 ){
                    $output = '';
                    if( !isset( $key['pageType']['all'] ) ){
                        foreach ( afcStrings::getString('pagetypes') as $pt=>$val ) {
                	        if ( isset($key['pageType'][$pt]) ){
                                if(!is_array($key['pageType'][$pt]) ){
                                    $output .= '[' . $val . ']';
                                }
                                else{
                                    $counter = 0;
                                    $output .= '[' . $val . ': ';
                                    $output .= 'Status = ' . $key['pageType'][$pt]['status'] . '; Page Ids = ';
                                    $counter = 0;
                                    foreach ( $key['pageType'][$pt]['valueArr'] as $id ){
                                        if ( ( count( $key['pageType'][$pt]['valueArr'] ) - 1 ) != $counter ){
                                            $output .= $id . ',';                              	
                                        }
                                        else {
                                	        $output .= $id . ';]';
                                        }
                                        $counter++;
                                    }
                                }
                            }
                        }
                    }
                    else{
                	    $output .= '[Every Where]';
                    }
                    
                    $key['pageType'] = trim( $output );
                }
                else{
                    $key['pageType'] = '';
                }
			    $editedArr[] = $key;
		    }
        }
        return $editedArr;
	}
}
?>