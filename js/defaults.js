//Loads editor when user clicks on plugins adminbar menu item
function afcLoadEditor() {
    jj(document).ready(function () {
        var editorUrl = afc_data_obj.afcsiteurl + '/?afcnonce=' + afc_data_obj.afcnonce + '&afceditor=1&afcsaveurl=no';
        window.location.href = editorUrl;
    });
}