/*
 * Copyright (C) www.vtiger.com. All rights reserved.
 * @license Proprietary
 */

jQuery.Class("ExtensionStore_ExtensionStore_Js", {}, {
    
    /**
     * Function to register events for banner
     */
    registerEventsForBanner : function(){
        var bxTarget = jQuery('.bxslider');
        var items = jQuery('li', bxTarget);
        if (items.length) {
            bxTarget.bxSlider({
                mode: 'fade',
                auto: true,
                pager: items.length > 1,
                speed: items.length > 1 ? 1500 : 0,
                pause: 2000,
                onSlideBefore : function(){
                    jQuery('.bx-viewport').css({'height': '150px', 'overflow': 'hidden'});
                }
            });
        }
    },
    
    /**
     * Function to getPromotions from marketplace
     */
    getPromotions : function(){
        var thisInstance = this;
        var params = {
            'module': 'ExtensionStore',
            'view': 'Listings',
            'mode': 'getPromotions'
        };
        AppConnector.request(params).then(
            function(data) {
                if((typeof data != 'undefined') && (jQuery(data).find('img').length > 0)){
                    jQuery('.dashboardHeading').append(data);
                    thisInstance.registerEventsForBanner();
                }else{
                    jQuery('.togglePromotion').trigger('click');
                }
            },
            function(error) {}
        );
    },
    
    /**
     * Function to request get promotions from market place based on promotion closed date
     */
    getPromotionsFromMarketPlace : function(promotionClosedDate){
        var thisInstance = this;
        if(promotionClosedDate != null){
            var maxPromotionParams = {
                'module' : 'ExtensionStore',
                'action' : 'Promotion',
                'mode'   : 'maxCreatedOn'
            };
            AppConnector.request(maxPromotionParams).then(
                function(data) {
                    var date = data['result'];
                    var dateObj = new Date(date);
                    var closedDate = new Date(promotionClosedDate);
                    var dateDiff = ((dateObj.getTime()) - (closedDate.getTime()))/(1000*60*60*24);
                    if(dateDiff > 0){
                        thisInstance.getPromotions();
                    }
                });
        }else if(promotionClosedDate == null){
            thisInstance.getPromotions();
        }
    },
    
    registerEventsForTogglePromotion : function() {
        var thisInstance = this;
        jQuery('.togglePromotion').on('click', function(e){
            var element = jQuery(e.currentTarget);
            var bannerContainer = jQuery(".banner-container");
            
            if(element.hasClass('up')){
                 bannerContainer.slideUp();
                 element.find('.icon-chevron-up').addClass('hide');
                 element.find('.icon-chevron-down').removeClass('hide');
                 element.addClass('down').removeClass('up');
                 //Caching closed date value
                 var date = new Date();
                 var currentDate = date.getUTCFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();
                 app.cacheSet('ExtensionStore_Promotion_CloseDate', currentDate);
            }else if(element.hasClass('down')){
                if(bannerContainer.find('img').length <= 0){
                    thisInstance.getPromotionsFromMarketPlace(null);
                }
                bannerContainer.slideDown();
                element.find('.icon-chevron-down').addClass('hide');
                element.find('.icon-chevron-up').removeClass('hide');
                element.addClass('up').removeClass('down');
                app.cacheClear('ExtensionStore_Promotion_CloseDate');
            }
        });
    },
    
    insertTogglePromotionHtml : function(){
        var toggleHtml = '<span class="btn-group">'+
                        '<button class="btn addButton togglePromotion up">'+
                             '<span id="hide" class="icon icon-chevron-up"></span>'+
                             '<span id="show" class="icon icon-chevron-down hide"></span>'+
                        '</button>'+
                    '</span>';
        jQuery('.dashboardHeading').find('.btn-toolbar').append(toggleHtml);
    },
    
    registerEvents: function() {
        var thisInstance = this;
        var moduleName = app.getModuleName();
        var promotionClosedDate = app.cacheGet('ExtensionStore_Promotion_CloseDate', null);
        var getPromotion = false;
        thisInstance.insertTogglePromotionHtml();
        if(promotionClosedDate == null){
            getPromotion = true;
        }else if(promotionClosedDate.length > 0){
           var closedDate = promotionClosedDate.split("-"); 
           var closedOn = new Date(parseInt(closedDate[0]), parseInt(closedDate[1]), parseInt(closedDate[2]));
           var currentDate = new Date();
           var diff = (currentDate.getTime()) - (closedOn.getTime());
           var days = diff/(1000*60*60*24);
           if(days >= 7){
               getPromotion = true;
           }else {
               getPromotion = false;
           }
        }
        
        if ((moduleName == "Home") && getPromotion) {
            thisInstance.getPromotionsFromMarketPlace(promotionClosedDate);
        }else if((moduleName == "Home") && !getPromotion){
            jQuery('.togglePromotion').find('.icon-chevron-up').addClass('hide');
            jQuery('.togglePromotion').find('.icon-chevron-down').removeClass('hide');
            jQuery('.togglePromotion').addClass('down').removeClass('up');
        }
        thisInstance.registerEventsForTogglePromotion();
    }
});

jQuery(document).ready(function() {
    var moduleName = app.getModuleName();
    if (moduleName == "Home") {
        var instance = new ExtensionStore_ExtensionStore_Js();
        instance.registerEvents();
    }
});
