/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.filebrowserBrowseUrl = 'kcfinder/browse.php?type=images';
	config.filebrowserUploadUrl = 'kcfinder/upload.php?type=images';
         
        config.enterMode = CKEDITOR.ENTER_BR;
        config.shiftEnterMode = CKEDITOR.ENTER_P;
        
        config.skin = 'moonocolor'; 
       // config.removePlugins = 'templates,Save,NewPage,autosave,flash,forms,oembed,iframe,gg,bbcode';
        //config.addPlugins = 'resize';
        //config.extraPlugins = 'image,specialchar';
        
        config.resize_enabled = true;

        config.toolbarCanCollapse = true;
        config.allowedContent = true;
        
        config.toolbar = [
        { name: 'document', items: [ 'Source' ] },
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl'] },
        { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
        { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'] },
        { name: 'about', items: [ 'About' ] },
        '/',
        { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' , '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock','-','NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
        
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
        { name: 'others', items: [ '-' ] }
        ];
    
        config.font_names =
            'Arial/Arial, Helvetica, sans-serif;' +
            'Comic Sans MS;' +
            //'Comic Sans MS/Comic Sans MS, cursive;' +
            'Courier New/Courier New, Courier, monospace;' +
            'DejaVu Sans;' +
            'DejaVu Sans Condensed;' +
            'DejaVu Sans Mono;' +
            'DejaVu Serif;' +
            'DejaVu Serif Condensed;' +
            'Georgia;' +
            'Lucida Sans Unicode;' +
            //'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
            'Tahoma;' +
            //'Tahoma/Tahoma, Geneva, sans-serif;' +
            'Times New Roman/Times New Roman, Times, serif;' +
            'Trebuchet MS;' +
            // 'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
            'Verdana';     
};
