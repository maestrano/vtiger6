/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
if (typeof(PDFMaker_Actions_Js) == 'undefined'){
    
    PDFMaker_Actions_Js = {
        getPDFBreaklineDiv: function (rootElm, id){
            new Ajax.Request(
                    'index.php',
                    {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: "module=PDFMaker&action=PDFMakerAjax&file=breaklineSelect&return_id=" + id,
                        onComplete: function(response){
                            document.getElementById('PDFBreaklineDiv').innerHTML = response.responseText;
                            fnvshobj(rootElm, 'PDFBreaklineDiv');

                            var PDFBreakline = document.getElementById('PDFBreaklineDiv');
                            var PDFBreaklineHandle = document.getElementById('PDFBreaklineDivHandle');
                            Drag.init(PDFBreaklineHandle, PDFBreakline);
                        }
                    }
            );
        },

        getPDFImagesDiv: function (rootElm, id){
            new Ajax.Request(
                    'index.php',
                    {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: "module=PDFMaker&action=PDFMakerAjax&file=imagesSelect&return_id=" + id,
                        onComplete: function(response){
                            document.getElementById('PDFImagesDiv').innerHTML = response.responseText;
                            fnvshobj(rootElm, 'PDFImagesDiv');

                            var PDFImages = document.getElementById('PDFImagesDiv');
                            var PDFImagesHandle = document.getElementById('PDFImagesDivHandle');
                            Drag.init(PDFImagesHandle, PDFImages);
                        }
                    }
            );
        },

        savePDFBreakline: function (){
            var record = document.DetailView.record.value;
            var frm = document.PDFBreaklineForm;
            var url = 'module=PDFMaker&action=PDFMakerAjax&file=SavePDFBreakline&pid=' + record + '&breaklines=';
            var url_suf = '';
            var url_suf2 = '';
            if (frm != 'undefined'){
                for (i = 0; i < frm.elements.length; i++){
                    if (frm.elements[i].type == 'checkbox'){
                        if (frm.elements[i].name == 'show_header' || frm.elements[i].name == 'show_subtotal'){
                            if (frm.elements[i].checked)
                                url_suf2 += '&' + frm.elements[i].name + '=true';
                            else
                                url_suf2 += '&' + frm.elements[i].name + '=false';
                        } else {
                            if (frm.elements[i].checked)
                                url_suf += frm.elements[i].name + '|';
                        }
                    }
                }

                url += url_suf + url_suf2;
                new Ajax.Request(
                        'index.php',
                        {queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: url,
                            onComplete: function(response){
                                fninvsh('PDFBreaklineDiv');
                            }
                        }
                );

            }
        },

        savePDFImages: function (){
            var record = document.DetailView.record.value;
            var frm = document.PDFImagesForm;
            var url = 'module=PDFMaker&action=PDFMakerAjax&file=SavePDFImages&pid=' + record;
            var url_suf = '';
            if (frm != 'undefined'){
                for (i = 0; i < frm.elements.length; i++){
                    if (frm.elements[i].type == 'radio'){
                        if (frm.elements[i].checked){
                            url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
                        }
                    } else if (frm.elements[i].type == 'text'){
                        url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
                    }
                }

                url += url_suf;
                new Ajax.Request(
                        'index.php',
                        {queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: url,
                            onComplete: function(response){
                                fninvsh('PDFImagesDiv');
                            }
                        }
                );
            }
        },

        checkIfAny: function () {
            var frm = document.PDFBreaklineForm;
            if (frm != 'undefined'){
                var j = 0;
                for (i = 0; i < frm.elements.length; i++){
                    if (frm.elements[i].type == 'checkbox' && frm.elements[i].name != 'show_header' && frm.elements[i].name != 'show_subtotal'){
                        if (frm.elements[i].checked){
                            j++;
                        }
                    }
                }
                if (j == 0){
                    frm.show_header.checked = false;
                    frm.show_subtotal.checked = false;
                    frm.show_header.disabled = true;
                    frm.show_subtotal.disabled = true;
                } else {
                    frm.show_header.disabled = false;
                    frm.show_subtotal.disabled = false;
                }
            }
        },
        loadPDFCSS: function (filename){
            return;
        },

        downloadNewRelease: function (type, url, alertLbl){
            var ans = confirm(alertLbl);

            if (ans == true){
                new Ajax.Request(
                        'index.php',
                        {queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: "module=PDFMaker&action=PDFMakerAjax&file=AjaxRequestHandle&handler=download_release&type=" + type + "&url=" + url,
                            onComplete: function(response){
                                alert(response.responseText);
                                window.location.reload();
                            }
                        }
                );
            }
        },
    }
}

PDFMakerCommon = {
    showproductimages: function(record) {
        AppConnector.request('index.php?module=PDFMaker&view=imagesSelect&return_id=' + encodeURIComponent(record)).then(
                function(data) {
                    app.showModalWindow(data);
                }
        );
    },
    saveproductimages: function(record) {
        var frm = document.PDFImagesForm;
        var url = 'index.php?module=PDFMaker&action=SavePDFImages&record=' + encodeURIComponent(record);
        var url_suf = '';
        if (frm != 'undefined') {

            for (i = 0; i < frm.elements.length; i++) {
                if (frm.elements[i].type == 'radio') {
                    if (frm.elements[i].checked) {
                        url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
                    }
                } else if (frm.elements[i].type == 'text') {
                    url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
                }
            }

            url += url_suf;
            AppConnector.request(url).then(
                    function(data) {
                        app.hideModalWindow();
                    }
            );
        }
    },
    
        showPDFBreakline: function(record){
        AppConnector.request('index.php?module=PDFMaker&view=IndexAjax&mode=PDFBreakline&return_id=' + encodeURIComponent(record)).then(
                function(data) {
                    app.showModalWindow(data);
                }
        );
    },
    savePDFBreakline: function(record){
        var frm = document.PDFBreaklineForm;
        var url = 'index.php?module=PDFMaker&action=IndexAjax&mode=savePDFBreakline&record=' + encodeURIComponent(record) + '&breaklines=';
        var url_suf = '';
        var url_suf2 = '';
        if (frm != 'undefined'){
            for (i = 0; i < frm.elements.length; i++){
                if (frm.elements[i].type == 'checkbox'){
                    if (frm.elements[i].name == 'show_header' || frm.elements[i].name == 'show_subtotal'){
                        if (frm.elements[i].checked)
                            url_suf2 += '&' + frm.elements[i].name + '=true';
                        else
                            url_suf2 += '&' + frm.elements[i].name + '=false';
                    } else {
                        if (frm.elements[i].checked) {
                            if (url_suf != "") url_suf += '|';
                            url_suf += frm.elements[i].name;
                        }
                     }
                }
            }
            url += url_suf + url_suf2;
            AppConnector.request(url).then(
                function(data) {
                    app.hideModalWindow();
                }
            );
        }
    }
}
