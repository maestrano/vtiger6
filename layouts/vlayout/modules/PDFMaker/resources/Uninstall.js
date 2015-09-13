/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

if (typeof(PDFMaker_Uninstall_Func_Js) == 'undefined') {
    
    var PDFMaker_Uninstall_Func_Js = {
        
        uninstallPDFMaker: function() {

            var message = app.vtranslate('LBL_UNINSTALL_CONFIRM','PDFMaker');
            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function(data) {

                    var progressIndicatorElement = jQuery.progressIndicator({
                        'position' : 'html',
                        'blockInfo' : {
                                'enabled' : true
                        }
                    });

                    AppConnector.request('index.php?module=PDFMaker&action=UninstallPDFMaker').then(
                    function(data) {

                        if (data.result.success == true) {
                            var params = {
                            text:  app.vtranslate('JS_ITEMS_DELETED_SUCCESSFULLY'),
                            type: 'info'
                            };
                            window.location.href = "index.php";
                        } else {
                            var params = {
                            title : app.vtranslate('JS_ERROR'),
                            type: 'error'
                            };
                        }
                        progressIndicatorElement.progressIndicator({'mode':'hide'});

                        params.animation = "show";

                        Vtiger_Helper_Js.showPnotify(params);
                    });
                },
                function(error, err) {
                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                }
            );                                   
                           
	},
        
        registerEvents: function() {
		this.registerActions();
	},        
        
        registerActions : function() {
		
            var thisInstance = this;
		var container = jQuery('#UninstallPDFMakerContainer');

                jQuery('#uninstall_pdfmaker_btn').click(function(e) {
			thisInstance.uninstallPDFMaker();
  		});
	}
    }

}

jQuery(document).ready(function() {
	PDFMaker_Uninstall_Func_Js.registerEvents();
});