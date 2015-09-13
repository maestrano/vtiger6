/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

var typeofdata = new Array();
typeofdata['V'] = ['e', 'n', 's', 'ew', 'c', 'k'];
typeofdata['N'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['SUM'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['AVG'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['MIN'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['MAX'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['COUNT'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['T'] = ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'];
typeofdata['I'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['C'] = ['e', 'n'];
typeofdata['D'] = ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'];
typeofdata['NN'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['E'] = ['e', 'n', 's', 'ew', 'c', 'k'];

var fLabels = new Array();

var noneLabel;
var gcurrepfolderid = 0;
function trimfValues(value) {
    var string_array;
    string_array = value.split(":");
    return string_array[4];
}

function updatefOptions(sel, opSelName) {
    var selObj = document.getElementById(opSelName);
    var fieldtype = null;

    var currOption = selObj.options[selObj.selectedIndex];
    var currField = sel.options[sel.selectedIndex];

    if (currField.value != null && currField.value.length != 0) {
        fieldtype = trimfValues(currField.value);
        ops = typeofdata[fieldtype];
        var off = 0;
        if (ops != null) {

            var nMaxVal = selObj.length;
            for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                selObj.remove(0);
            }
            selObj.options[0] = new Option('None', '');
            if (currField.value == '') {
                selObj.options[0].selected = true;
            }
            off = 1;
            for (var i = 0; i < ops.length; i++) {
                var label = fLabels[ops[i]];
                if (label == null)
                    continue;
                var option = new Option(fLabels[ops[i]], ops[i]);
                selObj.options[i + off] = option;
                if (currOption != null && currOption.value == option.value)
                {
                    option.selected = true;
                }
            }
        }
    } else {
        var nMaxVal = selObj.length;
        for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
            selObj.remove(0);
        }
        selObj.options[0] = new Option('None', '');
        if (currField.value == '') {
            selObj.options[0].selected = true;
        }
    }

}

function changeSteps() {
    actual_step = document.getElementById('step').value * 1;
    next_step = actual_step + 1;

    if (next_step == "2") {
        document.getElementById('back_rep').disabled = false;
        changeSecOptions();
    } else if (next_step == "5") {
        blockname_val = document.getElementById('blockname').value

        if (blockname_val == '') {
            alert(alert_arr.BLOCK_NAME_CANNOT_BE_BLANK);
            return false;
        }
        document.NewBlock.submit();
    } else {
        if (next_step == "3") {
            if (selectedColumnsObj.options.length == 0) {
                alert(alert_arr.COLUMNS_CANNOT_BE_EMPTY);
                return false;
            }
            createRelatedBlockTable();
        } else if (next_step == "4") {
            var sortCol = document.getElementById("sortCol1");
            var selCol = document.getElementById("selectedColumns");
            var idx;

            for (idx = 1; idx < sortCol.options.length; idx++) {
                sortCol.options[idx] = null;
            }

            for (idx = 0; idx < selCol.options.length; idx++) {
                var tmpOption = selCol.options[idx];
                sortCol.options[idx + 1] = new Option(tmpOption.text, tmpOption.value, false, false);
            }
        } else if (next_step == "5") {
            if (!checkSortColDuplicates())
                return false;

            formSelectColumnString();

            if (!formSelectConditions())
                return false;

            var date1 = document.getElementById("startdate");
            var date2 = document.getElementById("enddate");

            if ((date1.value != '') || (date2.value != '')) {
                if (!dateValidate("startdate", "Start Date", "D"))
                    return false;

                if (!dateValidate("enddate", "End Date", "D"))
                    return false;

                if (!dateComparison("startdate", 'Start Date', "enddate", 'End Date', 'LE'))
                    return false;
            }
        }

        document.getElementById("step" + actual_step + "label").className = 'settingsTabList';
        document.getElementById("step" + next_step + "label").className = 'settingsTabSelected';
        hide('step' + actual_step);
        show('step' + next_step);
    }
    document.getElementById('step').value = next_step;
}

function changeStepsback() {
    actual_step = document.getElementById('step').value * 1;
    last_step = actual_step - 1;

    document.getElementById("step" + actual_step + "label").className = 'settingsTabList';
    document.getElementById("step" + last_step + "label").className = 'settingsTabSelected';

    hide('step' + actual_step);
    show('step' + last_step);

    if (last_step == 1)
        document.getElementById('back_rep').disabled = true;

    document.getElementById('step').value = last_step;
}

function standardFilterDisplay() {
    if (document.NewBlock.stdDateFilterField.options.length <= 0 || (document.NewBlock.stdDateFilterField.selectedIndex > -1 && document.NewBlock.stdDateFilterField.options[document.NewBlock.stdDateFilterField.selectedIndex].value == "Not Accessible")) {
        document.getElementById('stdDateFilter').disabled = true;
        document.getElementById('startdate').disabled = true;
        document.getElementById('enddate').disabled = true;
        document.getElementById('jscal_trigger_date_start').style.visibility = "hidden";
        document.getElementById('jscal_trigger_date_end').style.visibility = "hidden";
    } else {
        document.getElementById('stdDateFilter').disabled = false;
        document.getElementById('startdate').disabled = false;
        document.getElementById('enddate').disabled = false;
        document.getElementById('jscal_trigger_date_start').style.visibility = "visible";
        document.getElementById('jscal_trigger_date_end').style.visibility = "visible";
    }
}

/**
 * IE has a bug where document.getElementsByName doesnt include result of dynamically created 
 * elements
 */
function vt_getElementsByName(tagName, elementName) {
    var inputs = document.getElementsByTagName(tagName);
    var selectedElements = [];
    for (var i = 0; i < inputs.length; i++) {
        if (inputs.item(i).getAttribute('name') == elementName) {
            selectedElements.push(inputs.item(i));
        }
    }
    return selectedElements;
}

function changeEditSteps() {
    actual_step = document.getElementById('step').value * 1;
    next_step = actual_step + 1;


    if (next_step == "4") {
        blockname_val = document.getElementById('blockname').value;

        if (blockname_val == '') {
            alert(alert_arr.BLOCK_NAME_CANNOT_BE_BLANK);
            return false;
        }

        document.NewBlock.submit();
    } else {
        if (next_step == "3") {
            if (!checkSortColDuplicates())
                return false;

            document.getElementById('back_rep').disabled = false;

            if (!formSelectConditions())
                return false;

            var date1 = document.getElementById("startdate");
            var date2 = document.getElementById("enddate");

            if ((date1.value != '') || (date2.value != '')) {
                if (!dateValidate("startdate", "Start Date", "D"))
                    return false;

                if (!dateValidate("enddate", "End Date", "D"))
                    return false;

                if (!dateComparison("startdate", 'Start Date', "enddate", 'End Date', 'LE'))
                    return false;
            }
        }

        document.getElementById("step" + actual_step + "label").className = 'settingsTabList';
        document.getElementById("step" + next_step + "label").className = 'settingsTabSelected';
        hide('step' + actual_step);
        show('step' + next_step);
    }
    document.getElementById('step').value = next_step;
}

//functions related to sorting functionality in RelatedBlocks
function changeSortCol(selectObj) {
    if (selectObj.id.substr(7) < sortRowCount)
        return;

    if (selectObj.value != "0") {
        var parentTbl = document.getElementById("sortColTbl");
        sortColString += '@@' + selectObj.value;
        addSortTableRow(parentTbl);
    }
}

function addSortTableRow(tableObj) {
    var rowCount = tableObj.rows.length;
    var selCol = document.getElementById("sortCol1");

    if (selCol.options.length <= rowCount)
        return;

    var row = tableObj.insertRow(rowCount);
    row.id = "row" + rowCount;

    var colone = row.insertCell(0);
    var coltwo = row.insertCell(1);
    var colthree = row.insertCell(2);

    colone.style.textAlign = "right";
    colone.innerHTML = alert_arr.PM_LBL_THEN_BY;

    coltwo.innerHTML = '<select name="sortCol' + rowCount + '" id="sortCol' + rowCount + '" class="detailedViewTextBox" onchange="changeSortCol(this);">' + getSortColOptions() + '</select>';

    colthree.style.textAlign = "left";
    colthree.innerHTML = '<select name="sortDir' + rowCount + '" class="detailedViewTextBox"><option value="Ascending">' + alert_arr.PM_LBL_ASC + '</option><option value="Descending">' + alert_arr.PM_LBL_DESC + '</option></select>';

    sortRowCount = rowCount;
    document.getElementById("sortColCount").value = rowCount;
}

function getSortColOptions() {
    var selCol = document.getElementById("sortCol1");
    var idx;
    var optionsStr = '';

    for (idx = 0; idx < selCol.options.length; idx++) {
        var tmpOption = selCol.options[idx];
        if (sortColString.indexOf(tmpOption.value) == -1) {
            optionsStr += ' <option value="' + tmpOption.value + '">' + tmpOption.text + '</option>';
        }
    }

    return optionsStr;
}

function checkSortColDuplicates() {
    var tmpStr = '';
    var idx;
    for (idx = 1; idx <= sortRowCount; idx++) {
        var sortColSelect = document.getElementById('sortCol' + idx);
        if (sortColSelect.value != '0' && tmpStr.indexOf(sortColSelect.value) > -1) {
            alert(alert_arr.PM_LBL_SORTCOL_DUPLICATES);
            return false;
        }
        tmpStr += '@@' + sortColSelect.value;
    }
    return true;
}

jQuery(document).ready(function() {
    jQuery('.listViewEntriesDiv').on('click', '.listViewEntries', function(e) {
        if (jQuery(e.target).is('input[type="checkbox"]'))
            return;
        var elem = jQuery(e.currentTarget);
        var recordUrl = elem.data('recordurl');
        if (typeof recordUrl == 'undefined') {
            return;
        }
        window.location.href = recordUrl;
    });
});
