<?php
/**
 * Prints font faces based on requested fontnames
 * @param array $fontnames 
 */
function afc_print_the_fonts( $fontnames ){
	header("Content-type: text/css", true);
	
	$fontsArr = array();
	$fontFaces = '';
	if( $fontnames != '' ){
		$fontsArr = explode( '|', $fontnames );
		$afcstyles = new afcstyles;
		if( count( $fontnames ) > 0 ){
			$fontNames = array_unique( $fontsArr );
			$afcFonts = new afcfonts();
			$fontNames = $afcFonts->getFonts( 'name', $fontsArr );
			$link = ADVANCEDFONTCHANGERURL . 'fonts/local/';
			if( is_array($fontNames) && count($fontNames) > 0 )
			foreach( $fontNames as $key ){
				if( $key['status'] == 'local' ){
					$fontFaces .= '@font-face { font-family: "' . $key['name'] . '"; 
						src: url("' . $link . 'eot/' . $key['name'] . '.eot"); 
						src: url("' . $link . 'svg/' . $key['name'] . '.svg#titillium-light-webfont") format("svg"), 
							url("' . $link . 'eot/' . $key['name'] . '.eot?#iefix") format("embedded-opentype"), 
							url("' . $link . 'woff/' . $key['name'] . '.woff") format("woff"), 
							url("' . $link . 'ttf/' . $key['name'] . '.ttf") format("truetype");}
					';
				}
			}
		}
	}
	echo $fontFaces;
}

?>