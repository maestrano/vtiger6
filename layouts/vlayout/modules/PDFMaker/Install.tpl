{*<!--
/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<div class="contentsDiv marginLeftZero" >
    <div class="padding1per">
        <div class="editContainer" style="padding-left: 3%;padding-right: 3%"><h3>{vtranslate('LBL_MODULE_NAME','PDFMaker')} {vtranslate('LBL_INSTALL','PDFMaker')}</h3>    
            <hr>
            <div id="breadcrumb">             
                <ul class="crumbs marginLeftZero">
                    <li class="first step active" style="z-index:9;"  id="steplabel1"><a><span class="stepNum">1</span><span class="stepText">{vtranslate('LBL_DOWNLOAD','PDFMaker')}</span></a></li>    

                    <li class="last step {if $CURRENT_STEP eq $TOTAL_STEPS}active{/if}" style="z-index:7;"  id="steplabel{$TOTAL_STEPS}"><a><span class="stepNum">{$TOTAL_STEPS}</span><span class="stepText">{vtranslate('LBL_FINISH','PDFMaker')}</span></a></li>
                </ul>
            </div>
            <div class="clearfix">
            </div>
            <form name="install" id="editLicense"  method="POST" action="index.php" class="form-horizontal">
                <input type="hidden" name="module" value="PDFMaker"/>
                <input type="hidden" name="view" value="List"/>

                <div id="step1" class="padding1per" style="border:1px solid #ccc;" >     
                    <input type="hidden" name="installtype" value="download_src"/>                                      
                    <div class="controls">
                        <div>
                            <strong>{vtranslate('LBL_DOWNLOAD_SRC','PDFMaker')}</strong>
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="controls">
                        <div>
                            {vtranslate('LBL_DOWNLOAD_SRC_DESC','PDFMaker')}
                            {if $MB_STRING_EXISTS eq 'false'}
                                <br>{vtranslate('LBL_MB_STRING_ERROR','PDFMaker')}
                            {/if}
                        </div>
                        <br>
                        <div class="clearfix">
                        </div>
                    </div>          
                    <div class="controls">
                        <button type="button" id="download_button" class="btn btn-success"/><strong>{vtranslate('LBL_DOWNLOAD','PDFMaker')}</strong></button>&nbsp;&nbsp;  
                    </div>
                </div>
                <div id="step{$TOTAL_STEPS}" class="padding1per" style="border:1px solid #ccc; {if $STEP neq "2"}display:none;{/if}" >
                    <input type="hidden" name="installtype" value="redirect_recalculate" />
                    <div class="controls">
                        <div>{vtranslate('LBL_INSTALL_SUCCESS','PDFMaker')}</div>
                        <div class="clearfix">
                        </div>
                    </div> 
                    <br>
                    <div class="controls">
                        <button type="button" id="next_button" class="btn btn-success"/><strong>{vtranslate('LBL_FINISH','PDFMaker')}</strong></button>&nbsp;&nbsp;
                    </div>
                </div>
            </form> 
        </div> 
    </div>
</div>
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
    PDFMaker_FreeLicense_Js.registerInstallEvents();
});
</script>			