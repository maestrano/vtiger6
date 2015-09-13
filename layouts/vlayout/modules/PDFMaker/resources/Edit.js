/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

if (typeof(PDFMaker_EditJs) == 'undefined') {
    /*
     * Namespaced javascript class for Import
     */
    PDFMaker_EditJs = {
        reportsColumnsList : false,
        advanceFilterInstance : false,
        
        clearRelatedModuleFields: function() {
            second = document.getElementById("relatedmodulefields");
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            var box2 = second;
            var optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--) {
                box2.removeChild(optgroups[i]);
            }

            objOption = document.createElement("option");
            objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
            objOption.value = "";
            box2.appendChild(objOption);
        },
        change_relatedmodule: function(first, second_name) {
            second = document.getElementById(second_name);
            optionTest = true;
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            var box = first;
            var number = box.options[box.selectedIndex].value;
            if (!number)
                return;
            var box2 = second;
            //box2.options.length = 0;

            var optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--) {
                box2.removeChild(optgroups[i]);
            }

            if (number == "none") {
                objOption = document.createElement("option");
                objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
                objOption.value = "";
                box2.appendChild(objOption);
            } else {
                var tmpArr = number.split('|', 2);
                var moduleName = tmpArr[0];
                number = tmpArr[1];
                var blocks = module_blocks[moduleName];
                for (b = 0; b < blocks.length; b += 2) {
                    var list = related_module_fields[moduleName + '|' + blocks[b + 1]];
                    if (list.length > 0) {

                        optGroup = document.createElement('optgroup');
                        optGroup.label = blocks[b];
                        box2.appendChild(optGroup);
                        for (i = 0; i < list.length; i += 2) {
                            objOption = document.createElement("option");
                            objOption.innerHTML = list[i];
                            var objVal = list[i + 1];
                            var newObjVal = objVal.replace(moduleName.toUpperCase() + '_', number.toUpperCase() + '_');
                            objOption.value = newObjVal;
                            optGroup.appendChild(objOption);
                        }
                    }
                }
            }
        },
        change_acc_info: function(element) {

// alert(element.value);
            switch (element.value) {
                case "Assigned":
                    document.getElementById('acc_info_div').style.display = 'none';
                    document.getElementById('user_info_div').style.display = 'inline';
                    document.getElementById('logged_user_info_div').style.display = 'none';
                    break;
                case "Logged":
                    document.getElementById('acc_info_div').style.display = 'none';
                    document.getElementById('user_info_div').style.display = 'none';
                    document.getElementById('logged_user_info_div').style.display = 'inline';
                    break;
                default:
                    document.getElementById('acc_info_div').style.display = 'inline';
                    document.getElementById('user_info_div').style.display = 'none';
                    document.getElementById('logged_user_info_div').style.display = 'none';
                    break;
            }
        },
        savePDF: function() {

            var error = 0;

            if (!PDFMaker_EditJs.ControlNumber('margin_top', true) || !PDFMaker_EditJs.ControlNumber('margin_bottom', true) || !PDFMaker_EditJs.ControlNumber('margin_left', true) || !PDFMaker_EditJs.ControlNumber('margin_right', true)) {
                error++;
            }

            if (!PDFMaker_EditJs.CheckCustomFormat()) {
                error++;
            }

            if (error > 0)
                return false;
            else
                return true;
        },
        ControlNumber: function(elid, final)
        {
            var control_number = document.getElementById(elid).value;
            var re = new Array();
            re[1] = new RegExp("^([0-9])");
            re[2] = new RegExp("^[0-9]{1}[.]$");
            re[3] = new RegExp("^[0-9]{1}[.][0-9]{1}$");
            if (control_number.length > 3 || !re[control_number.length].test(control_number) || (final == true && control_number.length == 2)) {
                alert(app.vtranslate("LBL_MARGIN_ERROR"));
                document.getElementById(elid).focus();
                return false;
            } else {
                return true;
            }

        },
        showHideTab: function(tabname)
        {
            
            document.getElementById(selectedTab + '_tab').className = "";
            document.getElementById(tabname + '_tab').className = 'active';
            document.getElementById(selectedTab + '_div').style.display = 'none';
            document.getElementById(tabname + '_div').style.display = '';
            var formerTab = selectedTab;
            selectedTab = tabname;
        },
        showHideTab2: function(tabname)
        {
            document.getElementById(selectedTab2 + '_tab2').className = "dvtUnSelectedCell";
            document.getElementById(tabname + '_tab2').className = 'dvtSelectedCell';
            if (tabname == 'body') {
                document.getElementById('body_variables').style.display = '';
                if (document.getElementById('headerfooter_div').style.display == 'block')
                    PDFMaker_EditJs.showHideTab('properties');
            } else {
                document.getElementById('header_variables').style.display = '';
                document.getElementById('body_variables').style.display = 'none';
                if (document.getElementById('headerfooter_div').style.display == 'none')
                    PDFMaker_EditJs.showHideTab('headerfooter');
            }

            document.getElementById(selectedTab2 + '_div2').style.display = 'none';
            document.getElementById(tabname + '_div2').style.display = 'block';
            var module = document.getElementById('modulename').value;
            var formerTab = selectedTab2;
            selectedTab2 = tabname;
        },
        showHideTab3: function(tabname) {
            document.getElementById(selectedTab2 + '_tab2').className = "";
            document.getElementById(tabname + '_tab2').className = 'active';
            if (tabname == 'body') {
                document.getElementById('body_variables').style.display = '';
                if (document.getElementById('headerfooter_div').style.display == 'block')
                    PDFMaker_EditJs.showHideTab('properties');
            } else {
                document.getElementById('header_variables').style.display = '';
                document.getElementById('body_variables').style.display = 'none';
                if (document.getElementById('headerfooter_div').style.display == 'none')
                    PDFMaker_EditJs.showHideTab('headerfooter');
            }

            document.getElementById(selectedTab2 + '_div2').style.display = 'none';
            document.getElementById(tabname + '_div2').style.display = 'block';
            var module = document.getElementById('modulename').value;
            var formerTab = selectedTab2;
            selectedTab2 = tabname;
        },
        
        refresh_related_blocks_array: function(selected)
        {
            var module = document.getElementById('modulename').value;
            PDFMaker_EditJs.fill_related_blocks_array(module, selected);
        },
        CustomFormat: function()
        {
            var selObj;
            selObj = document.getElementById('pdf_format');

            if (selObj.value == 'Custom')
            {
                document.getElementById('custom_format_table').style.display = 'table';
            }
            else
            {
                document.getElementById('custom_format_table').style.display = 'none';
            }
        },
        hf_checkboxes_changed: function(oChck, oType)
        {
            var prefix;
            var optionsArr;
            if (oType == 'header')
            {
                prefix = 'dh_';
                optionsArr = new Array('allid', 'firstid', 'otherid');
            }
            else
            {
                prefix = 'df_';
                optionsArr = new Array('allid', 'firstid', 'otherid', 'lastid');
            }

            var tmpArr = oChck.id.split("_");
            var sufix = tmpArr[1];
            var i;
            if (sufix == 'allid')
            {
                for (i = 0; i < optionsArr.length; i++)
                {
                    document.getElementById(prefix + optionsArr[i]).checked = oChck.checked;
                }
            }
            else
            {
                var allChck = document.getElementById(prefix + 'allid');
                var allChecked = true;
                for (i = 1; i < optionsArr.length; i++)
                {
                    if (document.getElementById(prefix + optionsArr[i]).checked == false)
                    {
                        allChecked = false;
                        break;
                    }
                }
                allChck.checked = allChecked;
            }
        },
        CheckCustomFormat: function() {
            if (document.getElementById('pdf_format').value == 'Custom') {
                var pdfWidth = document.getElementById('pdf_format_width').value;
                var pdfHeight = document.getElementById('pdf_format_height').value;
                if (pdfWidth > 2000 || pdfHeight > 2000 || pdfWidth < 100 || pdfHeight < 100 || isNaN(pdfWidth) || isNaN(pdfHeight)) {
                    alert(app.vtranslate('LBL_CUSTOM_FORMAT_ERROR'));
                    document.getElementById('pdf_format_width').focus();
                    return false;
                }
            }
            return true;
        }
    }    
}