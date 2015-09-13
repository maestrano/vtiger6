/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

if (typeof(PDFMaker_FreeLicense_Js) == 'undefined'){
    
    PDFMaker_FreeLicense_Js = {
	
	registerInstallEvents: function(){
		this.registerActions();
	},

        registerActions : function(){
		
            var thisInstance = this;

            jQuery('#download_button').click(function(e){
                    thisInstance.downloadMPDF();
            });
            
            jQuery('#next_button').click(function(e){
                    window.location.href = "index.php?module=PDFMaker&view=List";
            });
	},
        
        downloadMPDF : function(){
             
             var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                        'enabled' : true
                }
            });

            var url = "index.php?module=PDFMaker&action=IndexAjax&mode=downloadMPDF"; 
            AppConnector.request(url).then(
                function(data){
                    
                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                    
                    var response = data['result'];
                    var result = response['success'];

                    if(result == true){
                        jQuery('#step1').hide();
                        jQuery('#step2').show();

                        jQuery('#steplabel1').removeClass("active");
                        jQuery('#steplabel2').addClass("active");
                    } else {
                        alert(response['message']); 
                        var params = {
                                    text: app.vtranslate(response['message'])
                            };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(error,err){
                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                }
            );
        },
        
        showMessage : function(customParams){
            var params = {};
            params.animation = "show";
            params.type = 'info';
            params.title = app.vtranslate('JS_MESSAGE');

            if(typeof customParams != 'undefined'){
                    var params = jQuery.extend(params,customParams);
            }
            Vtiger_Helper_Js.showPnotify(params);
	}
    }
}