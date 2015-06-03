var jj = jQuery.noConflict();
//Editor variables
var afc_fonts = afc_data_obj.allfonts
, fontGeneratorUrl = afc_data_obj.afcLocalFontFacesURL
, afc_domSelectors = ''
, afc_bindedClick = 0
, afc_allElems = []
, increaseInterval = null
, decreaseInterval = null
, afc_lockedElement = -1
, userIsEditing = 0
, afc_strings = afc_data_obj.afc_strings
, afc_existingData = afc_data_obj.afc_existingData
, afc_taxonomyList = afc_data_obj.pageTypes
, afc_activeTab = 1
, framejQuery = '';

if (afc_existingData.length > 0)
    afc_allElems = deepCopy(afc_existingData);

jj(document).ready(function () {
    jj('.afcui.toggleeditor').click(function () {
        if (jj('.afcwrap').css('display') == 'block')
            jj('.afcwrap').stop(true,false).fadeOut(200);
        else
            jj('.afcwrap').stop(true, false).fadeIn(200);
    });
    //To make the plugin editor draggable
    jj('.afcwrap').draggable({ cursor: 'move', cancel: '.afcselectedelement, input, select, .increasethesize, .decreasethesize, .afcui.toggle.checkbox, .afcui.dropdown, .afc-op-button', axis: 'x' });
    //To show pop up when user hovers an specified elements in editor
    jj('.afc-op-button, .afcwrap .taxonomytitle').afcpopup({ inline: true, position: 'top center', delay: { show: 100, hide: 50 } });
    //To call a function when selected font in fontselection dropdown changes
    jj('.afcwrap .propertylist').afcdropdown('setting', 'onChange', function (value) { afcShowProperty(value); });

    //To call a function when selected font in fontselection dropdown changes
    jj('.afcwrap .fontselection').afcdropdown('setting', 'onChange', function (value) { afcEditorController('fontname', value); });
    //To handle 'force change' switch in plugin editor
    jj('.afcwrap .afcforcechange').afccheckbox('setting', 'onChange', function () { afcEditorController('forcecheck', jj(this)); });

    jj('.fontsize.increasethesize').mousedown(function () { sizeButtons('inc', '.afcfontsize', 'font-size'); }).mouseup(function () { window.clearInterval(increaseInterval); increaseInterval = null; }).mouseout(function () { window.clearInterval(increaseInterval); increaseInterval = null; });
    jj('.fontsize.decreasethesize').mousedown(function () { sizeButtons('dec', '.afcfontsize', 'font-size'); }).mouseup(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; }).mouseout(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; });

    //To call a function when selected font in font-weight dropdown changes
    jj('.afcwrap .fontweight').afcdropdown('setting', 'onChange', function (value) { afcEditorController('fontweight', value); });
    //To call a function when selected font in font-style dropdown changes
    jj('.afcwrap .fontstyle').afcdropdown('setting', 'onChange', function (value) { afcEditorController('fontstyle', value); });

    //To init wp color picker
    jj('#textshadow-color, #afctextcolor').wpColorPicker({ change: function (event, ui) { if (jj(this).is('#afctextcolor')) changeTheColor(ui, 'textcolor'); else changeTheColor(ui, 'shadowcolor'); } });
    jj('.wp-color-result').on('click', function (e) {
        if (!jj(this).hasClass('.wp-picker-open') && afc_lockedElement == -1) {
            if (jj(' #afctextcolor', jj(this).next()).length > 0)
                jj('#afctextcolor').wpColorPicker('close');
            else
                jj('#textshadow-color').wpColorPicker('close');
            afcRunModal( afc_strings.noelemselected );
        }
    });

    jj('.textcolor .wp-picker-clear').on('click', function () { if (afc_lockedElement != -1 && typeof afc_allElems[afc_lockedElement].properties.textColor != 'undefined') resetProperties(['textColor']); });
    jj('.shadowcolor .wp-picker-clear').on('click', function () { if (afc_lockedElement != -1 && typeof afc_allElems[afc_lockedElement].properties.textShadow != 'undefined') resetProperties(['shadowColor']); });

    //To call a function when selected text decoration changes
    jj('.afcwrap .textdecoration').afcdropdown('setting', 'onChange', function (value) { afcEditorController('decoration', value); });

    //To handle text-shadow buttons
    jj('.h-shadow.increasethesize').mousedown(function () { increaseInterval = window.setInterval(function () { shadowSizeButtons('inc', '.afctxth-shadow', 'h-shadow'); }, 110); }).mouseup(function () { window.clearInterval(increaseInterval); increaseInterval = null; }).mouseout(function () { window.clearInterval(increaseInterval); increaseInterval = null; });
    jj('.h-shadow.decreasethesize').mousedown(function () { decreaseInterval = window.setInterval(function () { shadowSizeButtons('dec', '.afctxth-shadow', 'h-shadow'); }, 110); }).mouseup(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; }).mouseout(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; });
    jj('.v-shadow.increasethesize').mousedown(function () { increaseInterval = window.setInterval(function () { shadowSizeButtons('inc', '.afctxtv-shadow', 'v-shadow'); }, 110); }).mouseup(function () { window.clearInterval(increaseInterval); increaseInterval = null; }).mouseout(function () { window.clearInterval(increaseInterval); increaseInterval = null; });
    jj('.v-shadow.decreasethesize').mousedown(function () { decreaseInterval = window.setInterval(function () { shadowSizeButtons('dec', '.afctxtv-shadow', 'v-shadow'); }, 110); }).mouseup(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; }).mouseout(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; });
    jj('.shadow-blur.increasethesize').mousedown(function () { increaseInterval = window.setInterval(function () { shadowSizeButtons('inc', '.afcshadow-blur', 'blur'); }, 110); }).mouseup(function () { window.clearInterval(increaseInterval); increaseInterval = null; }).mouseout(function () { window.clearInterval(increaseInterval); increaseInterval = null; });
    jj('.shadow-blur.decreasethesize').mousedown(function () { decreaseInterval = window.setInterval(function () { shadowSizeButtons('dec', '.afcshadow-blur', 'blur'); }, 110); }).mouseup(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; }).mouseout(function () { window.clearInterval(decreaseInterval); decreaseInterval = null; });

    //To handle pagetype switches of selector in plugin editor
    jj('.afcwrap .tax').afccheckbox('setting', 'onChange', function () { afcEditorController('pt', getFJ(this)); });

    //Running the core
    //afcRunTheCore();
    jj('.afcframe').load(function () {
        framejQuery = jj('.afcframe')[0].contentWindow.jQuery;
        afc_domSelectors = framejQuery.find('body *:not( script, #wpadminbar, #wpadminbar *,.afcwrap, .afcwrap *, .afcwaiting )');
        afcRunTheCore();
        jj('.afcwrap,.toggleeditor').css('display', 'block');
    });
});

//We call this function every time user clicks on an element in dom
function afcRunTheCore() {
    if (!userIsEditing) {
        jj('.afcwrap').show();
        userIsEditing = 1;
        if (!afc_bindedClick) {
            setTimeout(function () {
                jj(afc_domSelectors).click(function (e) {
                    if (userIsEditing) {
                        if (afc_lockedElement == -1) {
                            var afcself = jj(e.target);
                            jj(".afcwrapper").animate({ borderColor: "#5BBD72" }, 200);
                            jj(afcself).addClass('afcactive');
                            jj(afcself).removeClass('afchover');
                            afcInitEditorData(afcself);
                        }
                        return false;
                    }
                    else {
                        return true;
                    }
                });

                jj(afc_domSelectors).mouseover(
					function (e) {
					    if (jj('.afcactive').length == 0 && userIsEditing && afc_lockedElement == -1)
					        jj(e.target).addClass('afchover');
					    else return true;
					})
				.mouseout(
					function (e) {
					    if (userIsEditing)
					        jj(e.target).removeClass('afchover');
					});

            }, 500);
            afc_bindedClick = 1;
        }
    }
    else {
        afccancel();
    }
    afcWaiting('hide');
}

//Main controller of plugin editor
function afcInitEditorData(currObj) {
    var objectPos = afcObjectExists(currObj);
    if (objectPos !== false) {
        var tempObj = afc_allElems[objectPos];
        afc_lockedElement = objectPos;
        afcMakeFormReady(tempObj);
    }
    else {
        afcAddElementToList(currObj);
        objectPos = afcObjectExists(currObj);
        var tempObj = afc_allElems[objectPos];
        afc_lockedElement = objectPos;
        afcMakeFormReady(tempObj);
    }
}

//Switches to requested function
function afcEditorController(property, val) {
    if (afc_lockedElement != -1) {
        switch (property) {
            case 'fontname':
                afcInsertFont(val);
                break;
            case 'forcecheck':
                afcforcechangehandle(val);
                break;
            case 'fontweight':
                afcChangeFW(val);
                break;
            case 'fontstyle':
                afcChangeFS(val);
                break;
            case 'decoration':
                afcInsertTD(val);
                break;
            case 'pt':
                afcPtHandle(val);
        }
    }
    else {
        if ((val != 'unset' && val != 'none' && typeof val != 'object') || (typeof val == 'object' && val.is(':checked'))) {
            if (property == 'forcecheck') 
                afcRunModal(afc_strings.nofontselected);
            else
                afcRunModal(afc_strings.noelemselected);
            resetTheForm();
        }
    }
}

//Generates an object for afc_allElems array from current received object.
function afcAddElementToList(currObj) {
    var numberOfParents = 0;
    if (!afcObjectHasID(currObj) && !afcObjectHasClass(currObj)) {
        tempObject = currObj;
        while (tempObject.get(0) !== jj('body').get(0)) {
            tempObject = tempObject.parent();
            if (afcObjectHasID(tempObject) || afcObjectHasClass(tempObject)) {
                numberOfParents++;
                break;
            }
            else {
                numberOfParents++;
            }
        }
    }
    afc_allElems.push({ selectorName: afcGenerateSelector('string', currObj, numberOfParents), properties: {}, pageType: { all: 'empty' }, editorData: { object: currObj, selectorMap: afcGenerateSelector('arr', currObj, numberOfParents), numOfParents: numberOfParents, isEditable: 1, isNew: 1, isShortCode: 0 } });
}

//Generates a complete selector from received object. uses parents number to determine how many parents must be inserted in selector
function afcGenerateSelector(status, object, parents) {
    var tempObj = object
	, result = []
	, tempStr = '';
    result.push({ name: afcGenerateSelectorForThisObj(object), included: 1 });
    if (parents > 0) {
        for (var i = 1; i <= parents; i++) {
            tempObj = tempObj.parent();
            result[i] = { name: afcGenerateSelectorForThisObj(tempObj), included: 1 };
        }
    }
    if (status == 'string') {
        for (var i = result.length - 1; i >= 0; i--)
            tempStr += ' ' + result[i].name;
        return tempStr;
    }
    return result;
}

//Returns string containing current object tag plus its classes
function afcGenerateSelectorForThisObj(object) {
    var objTagName = object.get(0).tagName.toLowerCase()
	, objID = object.prop('id')
	, objClasses = object.prop('class').split(' ')
	, result = objTagName;
    if (objID.trim() != '')
        result += '#' + objID;
    for (var i in objClasses)
        if (objClasses[i] != 'afchover' && objClasses[i] != 'afcactive' && objClasses[i].trim() != '')
            result += '.' + objClasses[i];
    return result;
}

//Generates a set of elements from received object. uses parents number to determine how many parents must be reached. This function's usage is for plugin editor.
function afcGenerateEditorElement(selector, isEditable) {
    var result = ''
	, tempStr = '';
    if (selector != '') {
        var selectorArr = selector.trim().split(' ')
		, parentsCount = selectorArr.length;
        if (parentsCount > 1) {
            for (var i = 0; i < parentsCount ; i++) {
                if (i == 0)
                    result = afcGenerateAnElement(selectorArr[i], parentsCount - i - 1, isEditable, 'begin');
                else if (i == parentsCount - 1)
                    result += afcGenerateAnElement(selectorArr[i], parentsCount - i - 1, isEditable, 'end');
                else {
                    result += afcGenerateAnElement(selectorArr[i], parentsCount - i - 1, isEditable);
                }
            }
        }
        else if (parentsCount == 1) {
            result = afcGenerateAnElement(selectorArr[0], 0, isEditable, 'end', 1);
        }
    }
    return result;
}

//To generate an element for a selector of type shortcode
function afcGenerateShortCodeElement(selector) {
    return outPut = '<span class="tagclass">' + selector + '</span>';
}

//Generates one element for current part of selector
function afcGenerateAnElement(selector, parent, editable, pos, sLength) {
    var result = ''
    , sTag = ''
	, specialElems = { removeThisTag: '', addParentTag: '', removeID: '', reBuildThis: '', removeThisClass: '' };
    if (editable) {
        specialElems = {
            removeThisTag: '<span class="removethistag afcui button icon green" onclick="afcRemoveThisTag( this );"><i class="icon minus"></i></span>'
			, addParentTag: '<span class="addparenttag afcui button icon green" onclick="afcAddParentTag();"><i class="icon plus"></i></span>'
			, removeID: '<span class="removeid afcui button icon blue"  onclick="removeThisAttr( \'id\', this );"><i class="icon minus"></i></span>'
			, reBuildThis: '<span class="regenclasses afcui button icon red" onclick="afcReBuildThis( this );"><i class="icon plus"></i></span>'
			, removeThisClass: '<span class="removethisclass afcui button icon red"  onclick="removeThisAttr( \'class\', this );"><i class="icon minus"></i></span>'
        };
    }

    if (selector.length > 0) {
        var sArr = selector.trim().replace(/#/g, ' #').replace(/\./g, ' .').split(' ');
        sTag = ' <span class="tagname" parent="' + parent + '"> ';
        for (var i in sArr) {
            if (sArr[i].indexOf('.') != -1) {
                sTag += '<span class="tagclass" attpos="' + (i - 1) + '">' + sArr[i] + specialElems.removeThisClass + '</span>';
            }
            else if (sArr[i].indexOf('#') != -1) {
                sTag += '<span class="tagid" attpos="' + (i - 1) + '">' + sArr[i] + specialElems.removeID + '</span>';
            }
            else {
                sTag += sArr[i];
            }
        }
        sTag += specialElems.reBuildThis + ((pos != 'end') ? specialElems.removeThisTag : '') + '</span>';

        if (pos == 'begin' || (pos == 'end' && sLength == 1))
            sTag = specialElems.addParentTag + sTag;
    }
    return sTag;
}

//Removes requested tag from the current selector
function afcRemoveThisTag(element) {
    var objectPos = afc_lockedElement
	, parentNum = afc_allElems[objectPos].editorData.numOfParents
	, selectorArr = afc_allElems[objectPos].editorData.selectorMap
	, targetObj = jj(element).parent()
    , tagID = -1
    , targetSelector = targetObj.text().replace(/<.>/g, '').replace(' ', '');
    for (var i in selectorArr) {
        if (targetSelector.trim() == selectorArr[i].name.trim()) {
            tagID = i;
        }
    }
    if (tagID == parentNum) {
        selectorArr.pop();
        parentNum -= 1;
        var newParentNum = parentNum;
        for (var i = parentNum; i > 0; i--) {
            if (selectorArr[i].included == 0) {
                selectorArr.pop();
                newParentNum -= 1;
            }
            else {
                break;
            }
        }
        parentNum = newParentNum;
    }
    else {
        selectorArr[tagID].included = 0;
    }
    afc_allElems[objectPos].editorData.numOfParents = parentNum;
    reNewSelector(objectPos, selectorArr);
}

//Adds one parent tag to the current selector
function afcAddParentTag() {
    var objectPos = afc_lockedElement
	, object = afc_allElems[objectPos].editorData.object
	, parentNum = afc_allElems[objectPos].editorData.numOfParents
	, selectorArr = afc_allElems[objectPos].editorData.selectorMap
	, error = 0;
    if (selectorArr[parentNum].included == 0) {
        selectorArr[parentNum].included = 1;
    }
    else {
        var tempObj = object;
        for (var i = 0; i <= parentNum; i++) 
            tempObj = tempObj.parent();
        if (tempObj.get(0) != getFJ('html').get(0)) {
            afc_allElems[objectPos].editorData.numOfParents = parseInt(parentNum) + 1;
            selectorArr.push({ name: afcGenerateSelectorForThisObj(tempObj), included: 1 });
        }
        else {
            afcRunModal(afc_strings.bodyreached);
            error = 1;
        }
    }
    if (!error) {
        reNewSelector(objectPos, selectorArr);
    }

}

//Rebuilds requested tag of current selector
function afcReBuildThis(element) {
    var objectPos = afc_lockedElement
	, selectorArr = afc_allElems[objectPos].editorData.selectorMap
	, tagID = jj(element).parent().attr('parent')
	, object = afc_allElems[objectPos].editorData.object;
    for (var i = 0; i < tagID; i++) {
        object = object.parent();
    }
    selectorArr[tagID].name = afcGenerateSelectorForThisObj(object);
    reNewSelector(objectPos, selectorArr);
}

//Removes the requested class or id from the its tag in current selector
function removeThisAttr(type, element) {
    var objectPos = afc_lockedElement
	, selectorArr = afc_allElems[objectPos].editorData.selectorMap
    , targetObj = jj(element).parent()
    , targetSelector = targetObj.parent().text().replace(/<.>/g, '').replace(' ', '')
	, tagID = -1;
    for (var i in selectorArr) {
        if (targetSelector.trim() == selectorArr[i].name.trim()) {
            tagID = i;
        }
    }
	var classes = selectorArr[tagID].name.split('.')
	, tagAndID = classes[0];
    //print_r(selectorArr);
    //alert(tagID);
    if (type == 'class') {
        targetClass = targetObj.text().replace(/<.>/g, '').replace(' ', '').replace('.','')
        , cPos = -1;
        for (var i in classes) {
            if (targetClass.trim() == classes[i].trim()) {
                cPos = i;
            }
        }
        //alert(cPos);
        classes.splice(cPos, 1);
        classes.shift();
        //print_r(classes);
        if (classes.length > 0) {

            classes = '.' + classes.join('.');
        }
        else {
            classes = '';
        }
    }
    else if (type == 'id') {
        tagAndID = tagAndID.split('#');
        tagAndID.pop();
        tagAndID = tagAndID[0];
        classes.shift();
        if (classes.length > 0)
            classes = '.' + classes.join('.');
        else
            classes = '';
    }
    selectorArr[tagID].name = tagAndID + classes;
    reNewSelector(objectPos, selectorArr);
}

function reNewSelector(objectPos, selectorArr) {
    var newSelector = afcSelectorArrToString(selectorArr);
    afc_allElems[objectPos].selectorName = newSelector;
    afc_allElems[objectPos].editorData.selectorMap = selectorArr;
    jj('.afcselectedelement').text('').append(afcGenerateEditorElement(newSelector, 1));
}

//Converts selector map to string
function afcSelectorArrToString(selectorArr) {
    var result = '';
    for (var i in selectorArr)
        if (selectorArr[i].included == 1)
            result = selectorArr[i].name + ' ' + result;
    return result;
}

//Fills the editor form using existing data in received object.
function afcMakeFormReady(elementObject) {
    if (elementObject.editorData.isEditable == 1)
        jj('.afcselectedelement').append(afcGenerateEditorElement(elementObject.selectorName, 1));
    else if (elementObject.editorData.isShortCode == 1)
        jj('.afcselectedelement').append(afcGenerateShortCodeElement(elementObject.selectorName));
    else
        jj('.afcselectedelement').append(afcGenerateEditorElement(elementObject.selectorName, 0));

    var selectorData = elementObject.properties;
    if (typeof selectorData.fontName != 'undefined') {
        jj(".fontselection").afcdropdown('set selected', selectorData.fontName.name);
        if (selectorData.fontName.forceChangeFont == '1') {
            jj(".afcwrap .afcforcechange").afccheckbox('check');
        }
    }
    if (typeof selectorData.fontSize != 'undefined') {
        jj('.afcwrap .afcfontsize').text(selectorData.fontSize);
    }
    else {
        jj('.afcwrap .afcfontsize').text(elementObject.editorData.object.css('font-size').replace('px', ''));
    }
    if (typeof selectorData.fontWeight != 'undefined') {
        jj(".fontweight").afcdropdown('set selected', selectorData.fontWeight);
    }
    if (typeof selectorData.fontStyle != 'undefined') {
        jj(".fontstyle").afcdropdown('set selected', selectorData.fontStyle);
    }
    if (typeof selectorData.textColor != 'undefined') {
        jj("#afctextcolor").wpColorPicker('color', selectorData.textColor);
    }
    if (typeof selectorData.textDecoration != 'undefined') {
        jj(".textdecoration").afcdropdown('set selected', selectorData.textDecoration);
    }
    if (typeof selectorData.textShadow != 'undefined') {
        jj(".textshadow .afctxth-shadow").text(selectorData.textShadow.hshadow);
        jj(".textshadow .afctxtv-shadow").text(selectorData.textShadow.vshadow);
        jj(".textshadow .afcshadow-blur").text(selectorData.textShadow.blur);
        jj("#textshadow-color").wpColorPicker('color', selectorData.textShadow.color);
    }
    for (var j in selectorData.pageType) {
        jj('#chb_' + selectorData.pageType[j]).afccheckbox('check');
    }
}

//Checks wether object exists in afc_allElems array or not.
function afcObjectExists(currObj) {
    var selector = '';
    for (var i in afc_allElems) {
        selector = '';
        if (afc_allElems[i].editorData.isEditable == 1) {
            for (j = afc_allElems[i].editorData.selectorMap.length - 1; j >= 0; j--)
                selector += ' ' + afc_allElems[i].editorData.selectorMap[j].name;
        }
        else {
            selector = afc_allElems[i].selectorName;
        }
        if (currObj.is(framejQuery.find(selector))) {
            if (typeof afc_allElems[i].editorData.object == 'undefined' || afc_allElems[i].editorData.object.get(0) != currObj.get(0)) {
                afc_allElems[i].editorData.object = currObj;
                afc_allElems[i].editorData.isNew = 0;
            }
            return i;
        }
    }
    return false;
}

//Checks wether selected html element has any class or not.
function afcObjectHasClass(object) {
    var objClasses = jj(object).prop('class').split(' ');
    for (var i in objClasses)
        if (objClasses[i] != 'afchover' && objClasses[i] != 'afcactive' && objClasses[i].trim() != '')
            return true;
    return false;
}

//Checks wether selected html element has ID or not.
function afcObjectHasID(object) {
    var objID = jj(object).prop('id');
    if (objID.trim() != '')
        return true;
    return false;
}

//To save changes on server and update current page
function afcSaveChanges() {
    afcWaiting('show');
    if (afc_lockedElement != -1)
        afcUnlock();
    var forSave = []
    if (afc_allElems.length > 0) {
        for (var i in afc_allElems) {
            if (typeof afc_allElems[i].selectorName != 'undefined') {
                if (typeof afc_allElems[i].editorData.object != 'undefined')
                    delete afc_allElems[i].editorData.object;
                if (typeof afc_existingData[i] != 'undefined')
                    afc_allElems[i].editorData.isNew = 0;
                forSave.push(afc_allElems[i]);
            }
        }
        if (forSave.length > 0) {
            if (isLoggedIn()) {
                jj.ajax({
                    url: afc_data_obj.ajax_url,
                    type: 'POST',
                    data: { 'action': 'afc_ajax', 'afcnonce': afc_data_obj.afcnonce, 'afcdata': forSave, 'afcaction': 'add' },
                    success: function (data) {
                        afcRunModal(afc_strings.changessaved, 'relaod');
                        location.reload();
                    },
                    error: function (data) { afcRunModal(afc_strings.savefailed); }
                });
            }
            else {
                afcWaiting('hide');
            }
        }
        else {
            nothingToSave();
        }
    }
    else {
        nothingToSave();
    }
}

//Shows a message when there is nothing for saving
function nothingToSave() {
    afcRunModal(afc_strings.nothingtosave);
    afcWaiting('hide');
}

//To remove style of current selected element from current page and server
function afcRemoveSelector() {
    if (afc_lockedElement != -1) {
        afcWaiting('show');
        var thisElem = []
		, doajax = 0
		, selector = afc_allElems[afc_lockedElement].selectorName;
        if (typeof afc_existingData[afc_lockedElement] != 'undefined') {
            thisElem.push(afc_existingData[afc_lockedElement]);
            doajax = 1;
        }
        properties = ['fontName', 'fontSize', 'fontWeight', 'fontStyle', 'textColor', 'textShadow', 'textDecoration'];
        if (doajax) {
            jj.ajax({
                url: afc_data_obj.ajax_url,
                type: 'POST',
                data: { 'action': 'afc_ajax', 'afcdata': thisElem, 'afcnonce': afc_data_obj.afcnonce, 'afcaction': 'removethiselem' },
                success: function (data) {
                    resetProperties(properties, 0, afc_lockedElement);
                    afcRunModal(afc_strings.elemremoved);
                    var currentInlineCSS = getFJ('.afccss').text();
                    if (currentInlineCSS.indexOf(selector) >= 0) {
                        var selectorsList = currentInlineCSS.split('/*eos*/');
                        for (var j in selectorsList) {
                            if (selectorsList[j].indexOf(selector) > -1) {
                                selectorsList[j] = '';
                                break;
                            }
                        }
                        for (var j in selectorsList) {
                            if (selectorsList[j].trim != '')
                                selectorsList[j] = selectorsList[j] + '/*eos*/';
                        }
                        getFJ('.afccss').text(selectorsList.join(' '));
                    }

                    if (typeof afc_existingData[afc_lockedElement] != 'undefined')
                        delete afc_existingData[afc_lockedElement];
                    var elementID = afc_lockedElement;
                    afcUnlock();
                    delete afc_allElems[elementID];
                    afcWaiting('hide');
                },
                error: function () {
                    afcWaiting('hide');
                }
            });
        }
        else {
            resetProperties(properties, 0, afc_lockedElement);
            var elementID = afc_lockedElement;
            afcUnlock();
            delete afc_allElems[elementID];
            afcWaiting('hide');
        }
    }
    else
        afcRunModal(afc_strings.noelemselected);
}

//To unlock the ability to select elements with mouse if script is locked on an element
function afcUnlock() {
    jj(".afcwrapper").animate({ borderColor: "#D95C5C" }, 200);
    jj(afc_allElems[afc_lockedElement].editorData.object).removeClass('afcactive');
    afc_lockedElement = -1;
    resetTheForm();
}

//To cancel new edits on current page only
function afcCancel() {
    //location.reload();
    if (window.top)
        window.top.close();
    else
        window.close();
}

//To reseet values in plugin editor
function resetTheForm() {
    jj('.afcselectedelement').text('');
    jj('.afcfontsize,.afctxth-shadow,.afctxtv-shadow,.afcshadow-blur').text(0);
    jj(".afcui.dropdown ").afcdropdown('set selected', 'none');
    jj(".textdecoration.afcui.dropdown ").afcdropdown('set selected', 'unset');
    jj(".afcwrap .afcforcechange").afccheckbox('uncheck');
    jj(".afcwrap .tax").afccheckbox('uncheck');
    jj('.wp-picker-clear').trigger('click');
}

function resetProperties(properties, fs, sID) {
    var selectorID = (typeof sID != 'undefined') ? sID : afc_lockedElement
	, fromStyle = (typeof fs != 'undefined') ? fs : 1
	, selectorName = afc_allElems[selectorID].selectorName;
    for (var i in properties) {
        switch (properties[i]) {
            case 'fontName':
                delete afc_allElems[selectorID].properties.fontName;
                removeInlineProperty(selectorName, 'font-family', '/*eof*/', fromStyle);
                break;
            case 'fontSize':
                delete afc_allElems[selectorID].properties.fontSize;
                removeInlineProperty(selectorName, 'font-size', '/*eofs*/', fromStyle);
                break;
            case 'fontWeight':
                removeInlineProperty(selectorName, 'font-weight', '/*eofw*/', fromStyle);
                delete afc_allElems[selectorID].properties.fontWeight;
                break;
            case 'fontStyle':
                removeInlineProperty(selectorName, 'font-style', '/*eofs*/', fromStyle);
                delete afc_allElems[selectorID].properties.fontStyle;
                break;
            case 'textColor':
                delete afc_allElems[selectorID].properties.textColor;
                removeInlineProperty(selectorName, 'color', '/*eotc*/', fromStyle);
                break;
            case 'textDecoration':
                removeInlineProperty(selectorName, 'text-decoration', '/*eotd*/', fromStyle);
                delete afc_allElems[selectorID].properties.textDecoration;
                break;
            case 'textShadow':
                var shadowVals = [0, 0, 0];
                changeShadowInlineProperties(shadowVals, '', selectorName);
                delete afc_allElems[selectorID].properties.textShadow;
                break;
            case 'shadowColor':
                var isDefined = (typeof afc_allElems[selectorID].properties.textShadow != 'undefined')
				, shadowVals = getShadowVals();
                if (isDefined)
                    afc_allElems[selectorID].properties.textShadow.color = '';
                changeShadowInlineProperties(shadowVals, '');
                break;
        }
    }
}

function removeInlineProperty(selector, property, code, fromStyle) {
    var currentInlineCSS = getFJ('.afccss').text();
    getFJ(selector).css(property, '');
    var propertyRegex = new RegExp(property + "([0-9a-zA-Z# :\)\(,])*;", "g");
    getFJ(selector).attr('style', function (i, s) {
        if (typeof s != 'undefined') {
            if (s.indexOf(property) != -1) {
                s = s.replace(propertyRegex, '');
            }
            return s;
        }
    });
    if (currentInlineCSS.indexOf(selector) >= 0 && fromStyle) {
        var selectorsList = currentInlineCSS.split('/*eos*/');
        for (var i = 0; i < selectorsList.length; i++) {
            var thisSelectorPr = selectorsList[i].substring(selectorsList[i].indexOf(property), selectorsList[i].indexOf(code));
            if (selectorsList[i].indexOf(selector) >= 0) {
                selectorsList[i] = selectorsList[i].replace(thisSelectorPr + code, '');
            }
        }
        for (var i in selectorsList)
            if (selectorsList[i].trim != '')
                selectorsList[i] = selectorsList[i] + '/*eos*/';
        getFJ('.afccss').text(selectorsList.join(' '));
    }
}

//To handle editor taxonomy switches status change event
function afcPtHandle(obj) {
    if (obj.is(':checked') && afc_lockedElement != -1) {
        if (typeof afc_allElems[afc_lockedElement].pageType[obj.val()] == 'undefined')
            afc_allElems[afc_lockedElement].pageType[obj.val()] = 'empty';
        if (typeof afc_allElems[afc_lockedElement].pageType.all != 'undefined')
            delete afc_allElems[afc_lockedElement].pageType.all;
    }
    else if (obj.is(':checked') && afc_lockedElement == -1) {
        afcRunModal(afc_strings.noelemselected);
        obj.prop('checked', false);
        setTimeout(function () { obj.removeClass('checked'); }, 500);
        resetTheForm();
    }
    else if (!obj.is(':checked')) {
        if (afc_lockedElement != -1) {
            delete afc_allElems[afc_lockedElement].pageType[obj.val()];
            if (afcIsEmptyObject(afc_allElems[afc_lockedElement].pageType))
                afc_allElems[afc_lockedElement].pageType['all'] = 'empty';
        }
    }
}

function afcIsEmptyObject(obj) {
    var name;
    for (name in obj) {
        return false;
    }
    return true;
}

//To load font-face and style for current element
function afcInsertFont(selectedFontName) {
    var fontName = selectedFontName;
    if (fontName != 'none') {
        removeForceChecked();
        insertFont(fontName);
        afc_allElems[afc_lockedElement].properties.fontName = { name: fontName, forceChangeFont: 0 };
    }
    else if (fontName == 'none') {
        removeForceChecked();
        resetProperties(['fontName']);
    }
}

//To load the font face in document if not exists 
function insertFont(requestedFontName) {
    var fontName = requestedFontName
	, thisFont = extractThisFont(fontName)
	, thisFontsObj = {};
    if (thisFont['status'] == 'local') {
        if (fontGeneratorUrl.indexOf(fontName) < 0)
            fontGeneratorUrl += fontName + '|';
        var localFont = afcCreateFontObject('local', [thisFont]);
        if (isNotEmpty(localFont)) {
            thisFontsObj.custom = localFont;
            getFJ('.afcfontloaderlink').remove();
        }
    }
    else if (thisFont['status'] == 'google') {
        var googleFont = afcCreateFontObject('google', [thisFont]);
        if (isNotEmpty(googleFont))
            thisFontsObj.google = googleFont;
    }

    if (isNotEmpty(thisFontsObj)) {
        document.getElementsByClassName('afcframe')[0].contentWindow.WebFont.load(thisFontsObj);
    }

    getFJ().css({ 'font-family': fontName });
}

//This function generates a object of fonts for send to fontloader.
function afcCreateFontObject(status, fontsArr) {
    var fontsObj = {};
    if (typeof fontsArr == 'object' && fontsArr.length > 0) {
        var familiesArr = [], urlArr = [];
        if (status == 'google') {
            var weight = '';
            for (i in fontsArr) {
                weight = '';
                if (typeof fontsArr[i].metadata != 'undefined' && typeof fontsArr[i].metadata.fvd != 'undefined' && fontsArr[i].metadata.fvd != '') {
                    weight = fontsArr[i].metadata.fvd;
                }
                if (weight != '')
                    familiesArr.push(fontsArr[i].name + ':' + weight);
                else
                    familiesArr.push(fontsArr[i].name);
            }
            fontsObj.families = familiesArr;
        }
        else if (status == 'local') {
            fontsObj.custom = {};
            for (i in fontsArr) {
                familiesArr.push(fontsArr[i].name);
            }
            fontsObj = { families: familiesArr, urls: [fontGeneratorUrl] };
        }
        else if (status == 'unknown') {
            var weight = '';
            for (i in fontsArr) {
                weight = '';
                if (typeof fontsArr[i].metadata != 'undefined' && typeof fontsArr[i].metadata.fvd != 'undefined' && fontsArr[i].metadata.fvd != '') {
                    weight = fontsArr[i].metadata.fvd;
                }
                if (typeof fontsArr[i].metadata != 'undefined' && typeof fontsArr[i].metadata.url != 'undefined' && fontsArr[i].metadata.url != '') {
                    urlArr.push(fontsArr[i].metadata.url);
                }
                if (weight != '')
                    familiesArr.push(fontsArr[i].name + ':' + weight);
                else
                    familiesArr.push(fontsArr[i].name);
            }
            if (urlArr.length > 0)
                fontsObj = { families: familiesArr, urls: urlArr };
            else
                fontsObj = { families: familiesArr };
        }
    }
    return fontsObj;
}

//To extract font name and status of given fontname
function extractThisFont(fontName) {
    for (i in afc_fonts)
        if (afc_fonts[i]['name'] == fontName)
            return afc_fonts[i];
    return false;
}

//To handle force change font switch
function afcforcechangehandle(obj) {
    if (obj.is(':checked') && jj('.fontselection').afcdropdown('get value') != 'none') {
        afc_allElems[afc_lockedElement].properties.fontName.forceChangeFont = '1';
        getFJ().css({ 'font-family': '' }).attr('style', function (i, s) {
            return s + 'font-family: ' + jj('.fontselection').afcdropdown('get value') + ' !important;';
        });
    }
    else if (obj.is(':checked') && jj('.fontselection').afcdropdown('get value') == 'none') {
        afcRunModal(afc_strings.noelemselected);
        obj.prop('checked', false);
    }
    else if (!obj.is(':checked')) {
        afc_allElems[afc_lockedElement].properties.fontName.forceChangeFont = '0';
        removeForceChecked();
    }
}

//To reset force change switch
function removeForceChecked() {
    jj('.afcwrap .afcforcechange').prop('checked', false);
    getFJ().css('font-family', '');
    getFJ().attr('style', function (i, s) {
        return s + 'font-family: ' + jj('.fontselection').afcdropdown('get value') + ';';
    });
}

//To handle 'font size change buttons' functionality in editor
function sizeButtons(job, target, property) {
    if (job == 'inc') {
        increaseInterval = window.setInterval(function () {
            if (afc_lockedElement != -1) {
                var newSize = parseInt(jj(target).text()) + 1;
                jj(target).text(newSize);
                getFJ().css(property, newSize + 'px');
                if (property == 'font-size')
                    afc_allElems[afc_lockedElement].properties.fontSize = newSize;
            }
        }, 110);
    }
    else if (job == 'dec') {
        decreaseInterval = setInterval(function () {
            if (afc_lockedElement != -1) {
                if (parseInt(jj(target).text()) > 0) {
                    var newSize = parseInt(jj(target).text()) - 1;
                    jj(target).text(newSize);
                    getFJ().css(property, newSize + 'px');
                    if (property == 'font-size')
                        afc_allElems[afc_lockedElement].properties.fontSize = newSize;
                }
            }
        }, 110);
    }
}

//To handle font weight change
function afcChangeFW(fontWeight) {
    if (fontWeight != 'none') {
        getFJ().css('font-weight', fontWeight);
        afc_allElems[afc_lockedElement].properties.fontWeight = fontWeight;
    }
    else {
        resetProperties(['fontWeight']);
    }
}

//To handle font style change
function afcChangeFS(fontStyle) {
    if (fontStyle != 'none') {
        getFJ().css('font-style', fontStyle);
        afc_allElems[afc_lockedElement].properties.fontStyle = fontStyle;
    }
    else {
        resetProperties(['fontStyle']);
    }
}

//To change the color when user changes it in wp-color-picker
function changeTheColor(ui, property) {
    if (property == 'textcolor') {
        getFJ().css({ 'color': '' }).attr('style', function (i, s) {
            if (typeof s != 'undefined') {
                var temp = s;
                if (s.indexOf('color') != -1) {
                    temp = s.replace(/color([0-9a-zA-Z# :)(,])*;/g, '');
                }
                return temp + 'color:' + ui.color.toString() + ';';
            }
            else {
                return 'color:' + ui.color.toString() + ';';
            }
        });
        afc_allElems[afc_lockedElement].properties.textColor = ui.color.toString();
    }
    else if (property == 'shadowcolor') {
        var isDefined = (typeof afc_allElems[afc_lockedElement].properties.textShadow != 'undefined')
		, shadowVals = getShadowVals()
		, color = ui.color.toString();
        changeShadowInlineProperties(shadowVals, color);
        if (!isDefined)
            afc_allElems[afc_lockedElement].properties.textShadow = { hshadow: 0, vshadow: 0, blur: 0, color: color };
        else
            afc_allElems[afc_lockedElement].properties.textShadow.color = color;
    }
}

//To adding css text decoration to selected element
function afcInsertTD(textDecor) {
    if (textDecor != 'unset') {
        getFJ().css('text-decoration', textDecor);
        afc_allElems[afc_lockedElement].properties.textDecoration = textDecor;
    }
    else {
        resetProperties(['textDecoration']);
    }
}

//To handle text shadow size buttons functionality in editor
function shadowSizeButtons(job, target, property) {
    if (afc_lockedElement != -1) {
        var isDefined = (typeof afc_allElems[afc_lockedElement].properties.textShadow != 'undefined')
		, isInc = (job == 'inc')
		, isDec = (job == 'dec')
		, shadowVals = getShadowVals()
		, color = (isDefined) ? afc_allElems[afc_lockedElement].properties.textShadow.color : '';
        switch (property) {
            case 'h-shadow':
                if (isInc)
                    shadowVals[0] += 1;
                else if (isDec)
                    shadowVals[0] -= 1;
                jj(target).text(shadowVals[0]);
                if (isDefined)
                    afc_allElems[afc_lockedElement].properties.textShadow.hshadow = shadowVals[0];
                else
                    afc_allElems[afc_lockedElement].properties.textShadow = { hshadow: shadowVals[0], vshadow: 0, blur: 0, color: '' };
                break;
            case 'v-shadow':
                if (isInc)
                    shadowVals[1] += 1;
                else if (isDec)
                    shadowVals[1] -= 1;
                jj(target).text(shadowVals[1]);
                if (isDefined)
                    afc_allElems[afc_lockedElement].properties.textShadow.vshadow = shadowVals[1];
                else
                    afc_allElems[afc_lockedElement].properties.textShadow = { hshadow: 0, vshadow: shadowVals[1], blur: 0, color: '' };
                break;
            case 'blur':
                if (isInc)
                    shadowVals[2] += 1;
                else if (isDec && shadowVals[2] > 0)
                    shadowVals[2] -= 1;
                jj(target).text(shadowVals[2]);
                if (isDefined)
                    afc_allElems[afc_lockedElement].properties.textShadow.blur = shadowVals[2];
                else
                    afc_allElems[afc_lockedElement].properties.textShadow = { hshadow: 0, vshadow: 0, blur: shadowVals[2], color: '' };
                break;
        }
        changeShadowInlineProperties(shadowVals, color);
    }
}

//Returns shadow properties values
function getShadowVals() {
    var hshadow = parseInt(jj('.afctxth-shadow').text())
	, vshadow = parseInt(jj('.afctxtv-shadow').text())
	, blur = parseInt(jj('.afcshadow-blur').text());
    return [hshadow, vshadow, blur];
}

//To edit text shadow properties
function changeShadowInlineProperties(shadowVals, color, selector) {
    var isEmpty = 1
	, selectorName = (typeof selector != 'undefined') ? selector : afc_allElems[afc_lockedElement].selectorName
	, currentInlineCSS = getFJ('.afccss').text()
	, code = '/*eots*/';
    for (var i in shadowVals)
        if (parseInt(shadowVals[i]) != 0) {
            isEmpty = 0;
            break;
        }
    var tShadow = (!isEmpty || color != '') ? 'text-shadow: ' + shadowVals[0] + 'px ' + shadowVals[1] + 'px ' + shadowVals[2] + 'px ' + color + ';' : '';
    if (isEmpty && color == '') {
        delete afc_allElems[afc_lockedElement].properties.textShadow;
    }
    if (currentInlineCSS.indexOf(selectorName) >= 0) {
        var selectorsList = currentInlineCSS.split('/*eos*/');
        for (var i = 0; i < selectorsList.length; i++) {
            var thisSelectorTS = selectorsList[i].substring(selectorsList[i].indexOf('text-shadow'), selectorsList[i].indexOf(code));
            if (selectorsList[i].indexOf(selectorName) >= 0) {
                selectorsList[i] = selectorsList[i].replace(thisSelectorTS + code, '');
            }
        }
        for (var i in selectorsList)
            if (selectorsList[i].trim != '')
                selectorsList[i] = selectorsList[i] + '/*eos*/';
        getFJ('.afccss').text(selectorsList.join(' '));
    }
    getFJ(selectorName).attr('style', function (i, s) {
        if (typeof s != 'undefined') {
            var temp = s;
            if (s.indexOf('text-shadow') != -1) {
                temp = s.replace(/text-shadow([0-9a-zA-Z# :)(,])*;/g, '');
            }
            return temp + tShadow;
        }
        else {
            return tShadow;
        }
    });
}

//To handle switch tabs buttons in editor
function afcSwitchTab() {
    if (afc_activeTab == 1) {
        jj('.afcwrap .group2').stop(true,true).fadeIn();
        jj('.afcwrap .group1').stop(true, true).fadeOut();
        afc_activeTab = 2;
    }
    else if (afc_activeTab == 2) {
        jj('.afcwrap .group1').stop(true, true).fadeIn();
        jj('.afcwrap .group2').stop(true, true).fadeOut();
        afc_activeTab = 1;
    }
}

//To show choosen property in editor
function afcShowProperty(val) {
    for (var i in afc_data_obj.propertyList) {
        jj('.afcwrap .databox .' + i).css('display', 'none');
    }
    jj('.afcwrap .databox .' + val).css('display', 'block');
}

//To check if user is loggedin or not
function isLoggedIn() {
    var info = jj.ajax({
        async: false,
        url: afc_data_obj.ajax_url,
        type: 'POST',
        data: { 'action': 'afc_ajax', 'afcnonce': afc_data_obj.afcnonce, 'afcaction': 'logincheck' },
        error: function (data) { afcRunModal(afc_strings.connectionfailed); }
    });
    if (info.responseText.indexOf('afc Logged In') != -1) {
        return true;
    }
    else if (info.responseText.indexOf('afc Not Logged In') != -1) {
        afcRunModal(afc_strings.pleaselogin);
        return false;
    }
    else {
        afcRunModal(afc_strings.servererror + ": \n" + info.responseText);
        return false;
    }
}

//To return object in frame
function getFJ(selector) {
    if (typeof selector != 'undefined')
        return framejQuery(selector);
    else
        return framejQuery(afc_allElems[afc_lockedElement].selectorName)
}

//To run modal, for showing message to user
function afcRunModal(message) {
    jj(".ui.modal .description").text(message);
    jj(".ui.modal").modal('setting', 'transition', 'horizontal flip').modal('show');
}

//To show waiting
function afcWaiting(status) {
    if(status == 'show')
        jj('.afcwaiting').css('display', 'block');
    else
        jj('.afcwaiting').css('display', 'none');
}


/*
* Helper function
*/

//To create a deepcopy of an array 
function deepCopy(obj) {
    if (Object.prototype.toString.call(obj) === '[object Array]') {
        var out = [], i = 0, len = obj.length;
        for (; i < len; i++) {
            out[i] = arguments.callee(obj[i]);
        }
        return out;
    }
    if (typeof obj === 'object') {
        var out = {}, i;
        for (i in obj) {
            out[i] = arguments.callee(obj[i]);
        }
        return out;
    }
    return obj;
}

//To print given array or object 
function print_r(printthis, returnoutput) {
    var output = '';

    if (jj.isArray(printthis) || typeof (printthis) == 'object') {
        for (var i in printthis) {
            output += i + ' : ' + print_r(printthis[i], true) + '\n';
        }
    } else {
        output += printthis;
    }
    if (returnoutput && returnoutput == true) {
        return output;
    } else {
        alert(output);
    }
}

//To check if an object is empty or not
function isNotEmpty(object) {
    for (var i in object) {
        return true;
    }
    return false;
}