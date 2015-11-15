<?php
/**
* This class generates links for font faces and generates style from available selectors based on current requested page type.
* page type 'all' means all kind of pages.
*/
class afcstyles{
	private $fontGeneratorUrl;

	/**
     * Generates style from available selectors based on requested page type
	 * @param mixed $pageType 
	 * @param mixed $export 
	 * @return mixed
	 */
	function generateStyles( $pageType = 'all', $export = 'no' ){
		$afcSelector = new afcselectors();
		$currentPageType = $afcSelector->getCurrentPageType();
		$allSelectors = ( $pageType == 'all' )? $afcSelector->getCols( array( 'selectorName', 'properties', 'pageType', 'editorData' ) ) : $afcSelector->getByPageType();
		$output = '';
		foreach( $allSelectors as $selector ){
			foreach( $selector['pageType'] as $key=>$val ){
				if( !in_array( $key, $currentPageType ) && $pageType != 'all' )
					continue;
				$pt = '';
				if( $key == 'all' )
					$pt = '';
				elseif( $key == 'cat' ) 
					$pt = '.category';
				else 
					$pt = '.' . $key;
				$properties = array( 'fontFamily' => '', 'fontSize' => '', 'fontWeight' => '', 'fontStyle' => '', 'textColor' => '', 'textDecoration' => '', 'textShadow' => '' );
				$wfClass = '';
				if( isset( $selector['properties']['fontName']['name'] ) && trim( $selector['properties']['fontName']['name'] ) != '' && trim( $selector['properties']['fontName']['name'] ) != 'none' )
					$properties['fontFamily'] =  ' font-family: "' . $selector['properties']['fontName']['name'] . '" '. ( ( $selector['properties']['fontName']['forceChangeFont'] == 1 )? '!important' : '' ) . ';/*eof*/ ';
				if( isset( $selector['properties']['fontSize'] ) && trim( $selector['properties']['fontSize'] ) != '' ) 
					$properties['fontSize'] =  ' font-size:' . $selector['properties']['fontSize'] . 'px;/*eofs*/ ';
				if( isset( $selector['properties']['fontWeight'] ) && trim( $selector['properties']['fontWeight'] ) != '' ) 
					$properties['fontWeight'] =  ' font-weight: ' . $selector['properties']['fontWeight'] . ';/*eofw*/ ';
				if( isset( $selector['properties']['fontStyle'] ) && trim( $selector['properties']['fontStyle'] ) != '' ) 
					$properties['fontStyle'] =  ' font-style: ' . $selector['properties']['fontStyle'] . ';/*eofs*/ ';
				if( isset( $selector['properties']['textColor'] ) && trim( $selector['properties']['textColor'] ) != '' ) 
					$properties['textColor'] =  ' color: ' . $selector['properties']['textColor'] . ' !important;/*eotc*/ ';
				if( isset( $selector['properties']['textDecoration'] ) && trim( $selector['properties']['textDecoration'] ) != '' ) 
					$properties['textDecoration'] =  ' text-decoration: ' . $selector['properties']['textDecoration'] . ';/*eotd*/ ';
				if( isset( $selector['properties']['textShadow'] ) ){
					$properties['textShadow'] =  ' text-shadow: ' . $selector['properties']['textShadow']['hshadow'] . 'px ' . $selector['properties']['textShadow']['vshadow'] . 'px ';
					if( isset( $selector['properties']['textShadow']['blur'] ) && trim( $selector['properties']['textShadow']['blur'] ) != '' )
						$properties['textShadow'] .= $selector['properties']['textShadow']['blur'] . 'px ';
					if( isset( $selector['properties']['textShadow']['color'] ) && trim( $selector['properties']['textShadow']['color'] ) != '' )
						$properties['textShadow'] .= $selector['properties']['textShadow']['color'] . ' !important';
					$properties['textShadow'] .= ';/*eots*/ ';
				}
				if( $export == 'no' && $properties['fontFamily'] != '' ){
                    $option = get_option('afc_general_settings');
                    if( isset( $option['use_webfontloader'] ) && $option['use_webfontloader'] == 'yes' )
                        $wfClass = '.wf-active ';
                    else
                        $wfClass = ' ';
                }
				$output .= $wfClass . $pt . ' ' . ( ( $selector['editorData']['isShortCode'] == 1 && !preg_match( "/[#.]/", $selector['selectorName'] ) )? '.' : ''  ) . $selector['selectorName'] . '{ ' 
						. $properties['fontFamily'] 
						. $properties['fontSize'] 
						. $properties['fontWeight'] 
						. $properties['fontStyle']
						. $properties['textColor']
						. $properties['textDecoration']
						. $properties['textShadow']
						. ' }/*eos*/';
			}
		}
		return  $output;
	}

	/**
     * Generates local fontfaces based on requested page type
	 * @param mixed $pageType 
	 * @return string
	 */
	function generateLocalFontFaces( $pageType = 'all' ){
		$fontFaces = '';
		$fontNames = $this->fontNamesArr( $pageType, 'local' );
		if( count( $fontNames ) > 0 ){
            $uploaddir = wp_upload_dir();
			$link = $uploaddir['baseurl'] . '/afc-local-fonts/';
			foreach( $fontNames as $key ){
					$fontFaces .= '@font-face {
						font-family: "' . $key['name'] . '";
						src: url("' . $link . $key['name'] . '.eot");
						src: url("' . $link . $key['name'] . '.svg#titillium-light-webfont") format("svg"),
							 url("' . $link . $key['name'] . '.eot?#iefix") format("embedded-opentype"),
							 url("' . $link . $key['name'] . '.woff") format("woff"), 
							 url("' . $link . $key['name'] . '.ttf") format("truetype");}';
			}
		}
		return $fontFaces;
	}

	/**
     * Returns an array of used font names, based on requested page type
	 * @param string $pageType 
	 * @param string $status 
	 * @return array
	 */
	function getUsedFontNames( $pageType = 'all', $status ){
		$fontNamesList = array();
		$fontNames = $this->fontNamesArr( $pageType, $status );
		if( is_array( $fontNames ) && count( $fontNames ) > 0 ){
			foreach( $fontNames as $key ){
				$fontNamesList[] = $key['name'];
			}
		}
		return $fontNamesList;
	}

	/**
     * Creates an object that can be used directly in webfontloader
	 * @return array
	 */
	function getFontLoaderObject(){
		$afcFontsObj = array();
		$usedGoogleFonts = $this->fontNamesArr( 'custom', 'google' );
		$usedLocalFonts = $this->fontNamesArr( 'custom', 'local' );
		$usedUnknownFonts = $this->fontNamesArr( 'custom', 'unknown' );
		if( count( $usedGoogleFonts ) > 0 ){
			$googleFonts = $this->createFontLoaderObject( 'google', $usedGoogleFonts );
			if( count( $googleFonts ) > 0 )
				$afcFontsObj['google'] = $googleFonts;
		}
		if( count( $usedLocalFonts ) > 0 ){
			$localFonts = $this->createFontLoaderObject( 'local', $usedLocalFonts  );
			if( count( $localFonts ) > 0 )
				$afcFontsObj['custom'] = $localFonts;
		}
		if( count( $usedUnknownFonts ) > 0 ){
			$unknownFonts = $this->createFontLoaderObject( 'unknown', $usedLocalFonts  );
			if( count( $unknownFonts ) ){
				if( !array_key_exists( 'custom' , $afcFontsObj ) )
					$afcFontsObj[] = array( 'custom' => array() );
				if( array_key_exists( 'families' , $afcFontsObj['custom'] )  )
					$afcFontsObj['custom']['families'] = array_merge( $afcFontsObj['custom']['families'] , $unknownFonts['families'] );
				else{
					$afcFontsObj['custom'][] = array( 'families' => $unknownFonts['families'] );
				}
				if( array_key_exists( 'urls' , $unknownFonts ) ){
					if( array_key_exists( 'urls' , $afcFontsObj['custom'] ) )
						$afcFontsObj['custom']['urls'] = array_merge( $afcFontsObj['custom']['urls'] , $unknownFonts['urls'] );
					else{
						$afcFontsObj['custom']['urls'] = $unknownFonts['urls'];
					}
				}
			}
		}
		return $afcFontsObj;
	}
	
	/**
     * Creates objects that can be used in javascript webfontloader library
	 * @param string $status 
	 * @param array $fontsArr 
	 * @return array
	 */
	function createFontLoaderObject( $status, $fontsArr ){
		$fontsObj = array();
		if( is_array( $fontsArr ) && count( $fontsArr ) > 0 ){
			$familiesArr = array();
			$urlArr = array();
			if( $status == 'google' ){
				$fvd = '';
				foreach( $fontsArr as $key ){
					$fvd = '';
					if( isset( $key['metadata']['fvd'] ) && is_array( $key['metadata']['fvd'] ) ){
						$fvd = $key['metadata']['fvd'];
					}
					if( $fvd != '' )
						$familiesArr[] = $key['name'] . ':' . $fvd; 
					else
						$familiesArr[] = $key['name']; 
				}
				$fontsObj = array( 'families' => $familiesArr );
			}
			else if( $status == 'local' ){
				foreach( $fontsArr as $key ){
					$familiesArr[] = $key['name']; 
				}
				$fontsObj = array( 'families' => $familiesArr, 'urls' => array( $this->createFontGeneratorUrl( 'afc-public-fonts-nonce' ) ) );
			}
			else if( $status == 'unknown' ){
				$fvd = '';
				foreach( $fontsArr as $key ){
					$name = ''; $fvd = ''; $url = '';
					if( isset( $key['metadata']['fvd'] ) && is_array( $key['metadata']['fvd'] ) ){
						$fvd = $key['metadata']['fvd'];
					}
					if( $fvd != '' )
						$name = $key['name'] . ':' . $fvd; 
					else
						$name = $key['name']; 
					if( is_array( $key['metadata']['url'] ) && $key['metadata']['url'] != '' ){
						$url = $key['metadata']['url'];
					}
					if( $url != '' ){  //To prevent sending font families with no any url to webfontloader.
						$familiesArr[] = $name;
						$urlArr[] = $url;
					}
				}
				if( count( $urlArr ) > 0 )
					$fontsObj = array( 'families' => $familiesArr, 'urls' => $urlArr );
				else
					$fontsObj = array('families' => $familiesArr );
			}
		}
		return $fontsObj;
	}
    
    /**
     * Generates a link to used google fonts style based on requested page type
     * @param string $pageType 
     * @return string
     */
	function generateGoogleFontsStyle( $pageType = 'all' ){
		$link = 'http://fonts.googleapis.com/css?family=';
		$fontNames = $this->fontNamesArr( $pageType, 'google' );
		if( is_array( $fontNames ) && count( $fontNames ) > 0 ){
			foreach( $fontNames as $key )
				$link .= str_replace( ' ', '+', trim( $key['name'] ) ) . '|';
		}
		return $link;
	}
	
	/**
     * Generates a url to plugin font-face generator
	 * @param string $nonceName 
	 * @return string
	 */
	function createFontGeneratorUrl( $nonceName ){
		$localfonts = $this->getUsedFontNames( 'custom', 'local' );
		return get_bloginfo('wpurl') . '/?afcnonce='. wp_create_nonce( $nonceName ) . '&afcfontnames=' . ( ( count( $localfonts ) > 0 )? implode( '|', $localfonts ) . '|' : '' );
	}
	
	/**
     * Extracts status of a font name from afcfonts class
	 * @param string $pageType 
	 * @param string $status 
	 * @return array
	 */
	function fontNamesArr( $pageType = 'all', $status ){
		$fontNames = $this->fontNames( $pageType );
		if( count( $fontNames ) > 0 ){
			$fontNames = array_unique( $fontNames );
			$afcFonts = new afcfonts();
			$fontNames = $afcFonts->getByNameAndStatus( $status, $fontNames );
		}
		return $fontNames;
	}
	
	/**
     * Extracts font names from selectors based on requested page type
	 * @param string $pageType 
	 * @return array
	 */
	function fontNames( $pageType ){
		$afcSelector = new afcselectors();
		$allSelectors = ( $pageType == 'all' )? $afcSelector->getCols( array('properties') ) : $afcSelector->getByPageType();
		$fontNames = $afcSelector->extract( 'fontName', $allSelectors );
		if( !is_array( $fontNames ) )
			$fontNames = array();
		return $fontNames;
	}
}
?>