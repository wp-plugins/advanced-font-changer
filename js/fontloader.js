var jj = jQuery.noConflict();
//Here we send fonts to WebFontLoader script for every visitor. 
jj( document ).ready(function(){
	var afcFontsObj = {};
	if( typeof afc_fonts_loader_data != 'undefined' && typeof afc_fonts_loader_data.wf_obj != 'undefined' && typeof afc_fonts_loader_data.wf_obj != 'undefined' )
		afcFontsObj = afc_fonts_loader_data.wf_obj;
	if( isNotEmpty( afcFontsObj ) ){
		WebFont.load( afcFontsObj );
	}
});

//To check if an object is empty or not
function isNotEmpty(object) { 
	for(var i in object) { 
		return true; 
	} 
	return false; 
}
