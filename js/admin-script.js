//To load wordpress media uploader in the add user font admin page
jQuery(document).ready(function($){
	//for upload font page
	var afcInputID = [ '#afc_eot_file', '#afc_ttf_file', '#afc_woff_file', '#afc_svg_file' ];
	for( var i = 0; i < 4; i++ ){
		$( afcInputID[i] + '_button' ).click(function(e){
				var thisElemID = $(this).attr( 'id' );
				thisElemID = '#' + thisElemID.replace( '_button', '' );
				e.preventDefault();
				var font = wp.media({
				title: 'Upload Image',
				multiple: false
			}).open()
			.on('select', function(e){
				var uploaded_font = font.state().get( 'selection' ).first().toJSON();
				$( thisElemID ).val( uploaded_font.url );
				$( thisElemID + '_id' ).val( uploaded_font.id );
			});
		});
	}
	
	$('.afc-main').fadeIn(1200);
	
	$(function() {
        $('#textshadow-color,#textcolor').wpColorPicker();
    });
	
	//for manage selectors page. shows column contents in a alert
	$('.pageType.column-pageType, .properties.column-properties').click(function(){
		alert($(this).text());
	});
	
	//for add or edit selector pages
	$('#selectorname').on( 'keyup', function(){
		if( $('#isshortcode').val() == 1 ){
			$('#isshortcode').val(0);
		}
	});
	
	//prevents user from inserting non valid selectorName if this selector is for using as shortcode
	$('#isshortcode').on( 'change', function(){
		if( $(this).val() == 1 ){
			var sName = $('#selectorname').val().trim();
			if( sName == '' ){
				alert("selectorName is empty.");
				$(this).val(0);
			}
			else if ( /.[.#]/g.test(sName) || /[ ,]/g.test(sName) ){
			    alert("For using this selector in a shortcode it must have no ' '(space) in its name. Also using '.' and '#' in middle or end of a shortcode selectorname is not allowed.");
				$(this).val(0);
			}
		}
	});

	if ($('#setting-error-afc').length > 0) {
	    var message = $('#setting-error-afc').clone().wrap('<div></div>').parent().html();
	    $('#setting-error-afc').remove();
	    $('.afc-head').before(message).trigger('afcUpdateAdded');
	    $('body').on('afcUpdateAdded', '#setting-error-afc', function () {
	        $('#setting-error-afc').fadeIn();
	    });
	}
});