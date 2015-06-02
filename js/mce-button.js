(function() {
	var afcTinyMceSeletorsList = [];
	if( typeof afc_mce_data_object != 'undefined' && typeof afc_mce_data_object.selectorsList == 'object' ){
		var temp = afc_mce_data_object.selectorsList;
		for( var i in temp )
			afcTinyMceSeletorsList.push( { text: temp[i], value: temp[i] } );
	}
	tinymce.create('tinymce.plugins.afcselectors', {
		init : function(ed, url) {
			ed.addButton('afcselectors', {
				title : 'Change Font',
				icon : 'icon dashicons-facebook-alt',//url +'/recentpostsbutton.png',
				classes : 'afcselectors widget btn',
				onclick : function() {
					ed.windowManager.open({
					    title: 'Advanced Font Changer',
						body: [
							{ type: 'listbox', name: 'selectorname', label: 'Selector: ', 
							values:  ( afcTinyMceSeletorsList.length > 0 )? afcTinyMceSeletorsList : [{ text: 'No Selectors Found', value: 'none' }] },
							{
							    type: 'textbox', multiline: true, minHeight: 100, name: 'content', label: 'Content: '
                                , value: tinyMCE.activeEditor.selection.getContent()
							}
							
						],
						onsubmit: function (e) {
						    //Inserting content when the window form is submitted
						    if (e.data.selectorname != 'none') {
						        ed.insertContent('[afcselector selector="' + e.data.selectorname + '"]' + e.data.content + '[/afcselector]');
						    }
						}
					});
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add( 'afcselectors', tinymce.plugins.afcselectors );
})();