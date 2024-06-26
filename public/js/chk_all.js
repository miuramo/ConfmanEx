function CheckAll(formname) {
    for (var i = 0; i < document.forms[formname].elements.length; i++) {
        if (document.forms[formname].elements[i].type != "radio") {
            document.forms[formname].elements[i].checked = true;
        }
    }
}

function UnCheckAll(formname) {
    for (var i = 0; i < document.forms[formname].elements.length; i++) {
        if (document.forms[formname].elements[i].type != "radio") {
            document.forms[formname].elements[i].checked = false;
        }
    }
}

