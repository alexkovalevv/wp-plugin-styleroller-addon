/**
 * Extends the Locker Editor in order to add the Styling features.
 * 
 * @author Alex Kovalev <alex@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd
 * 
 * @package styleroller
 * @since 1.0.0
 */

;(function($){
    
    if ( !window.bizpanda ) window.bizpanda = {};
    if ( !window.bizpanda.styleroller ) window.bizpanda.styleroller = {};
    
    /**
     * Adds an extra CSS class for the preivew locker to show a current style.
     * 
     * 'filterOptions' is a global callback.
     * @see onpsl.lockerEditor
     * 
     * @param mixed options
     * @returns mixed
     */
    window.bizpanda.lockerEditor.filterOptions = function( options ){        
        options['cssClass'] = 'p' + $('#opanda_style_profile').val();       
        return options;
    };

    /**
     * StyleRoller Extension for Locker Editor
     * 
     * @since 1.0.0
     * @package styleroller
     */
    window.bizpanda.styleroller = {           
        _pluginName: pluginName,            
        _currentSheme: $('#opanda_style').val(),            

        /**
         * Inits the add-on.
         * 
         * @since 1.0.0
         * @returns void
         */
        init: function(){ 
            this.initEvents();
        },

        /**
         * Binds evetns.
         * 
         * @since 1.0.0
         * @returns void
        */
        initEvents: function(){
           var self = this;               
           
           // a click on the Button Edit
           $('.onpSL-profile-edit-button').on('click', function(){
                var profileID = $('#opanda_style_profile').val();                   
                self.editProfile( profileID );                   
                return false;
            });

           // a click on the Button Add
           $('.onpSL-profile-add-button').on('click', function(){
                self.addNewProfile();
                return false;
           });

           // changing a theme           
           $('#opanda_style').unbind().on('change', function(e){
                var theme = $(this).val();                      
                self.refreshContentDropdown( 'default' ); 
                return false;
           });

           // changing a style         
           $('#opanda_style_profile').unbind().on('change', function(e){ 
                self.changeProfile($(this).val());                     
                return false;
           });
           
           // removing the edit button for the default styles
           this.showHideEditButton();
        },

        /**
         * Makes an ajax request.
         * 
         * @since 1.0.0
         * @param mixed options
         * @returns jqXHR
         */
        request: function( options ) {
            var self = this;

            options.type = 'POST';
            options.dataType = 'text';

            var success = options.success;
            var error = options.error;

            options.success = function( text ) {
                var data = {};
                try {
                    data = jQuery.parseJSON( text );
                } catch (ex) {
                    if ( console ) console.log("Unable to parse the response got from the server: " + request.responseText);
                    self.showError("Unable to parse the response got from the server.");

                    error && error( data );
                    return;
                }  

                if ( data['error'] ) {

                    if ( console ) {
                        console.log("The error occured during the ajax request: ");
                        console.log(data);
                    }

                    self.showError(data.error);

                    error && error( data );
                    return;
                }

                success && success( data );
            };

            options.error = function() {
                if ( console ) console.log("Unexpected error occurred during the ajax request: " + request.responseText);
                self.showError("Unexpected error occurred during the ajax request.");
                error && error();
            };

            var request = $.ajax( options );
            return request;
        },

        /**
         * Shows an error.
         * 
         * @since 1.0.0
         * @return void
         */
        showError: function ( title, message ) {
            alert( title + "\n" + message );
        },

        /**
         * Refreshes the list of styles for a current theme.
         * 
         * @since 1.0.0
         * @param string A style if to select after refreshing the dropdown list.
         * @return void
         */
        refreshContentDropdown: function( profileID ){
            var self = this;
            self._currentSheme = $('#opanda_style').val(); 

            $('#opanda_style_profile').add('.onpSL-profile-edit-button')
                    .add('.onpSL-profile-add-button')                       
                    .prop('disabled', true);

            self.request({
                url : window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_get_theme_styles',
                data : {
                   onp_theme_id: self._currentSheme                
                },						
                success: function( data ) {

                    var boxHtml = '';                        
                    for(var key in data ) {                                       
                        boxHtml += '<option value="'+key+'"'+( profileID === key ? ' selected' : '' )+'>'+ data[key] + '</option>';                                        
                    }

                    $('#opanda_style_profile').html(boxHtml);

                    $('#opanda_style_profile').add('.onpSL-profile-edit-button')
                        .add('.onpSL-profile-add-button')                       
                        .prop('disabled', false);

                    self.showHideEditButton($('#opanda_style_profile').val());
                    self.updateFrontPreviewStyle(); 
                }    
            });
        },

        /**
         * Selects a given style.
         * 
         * @since 1.0.0
         * @return void               
        */
        changeProfile: function( styleId ){ 
            this.showHideEditButton( styleId );
            this.updateFrontPreviewStyle();
        },

        /**
         * Hides or shows the Edit Button for a given style id.
         * 
         * @since 1.0.0
         * @param integer styleId A style id for which we're showing or hidding the Edit Button.  
         * @return void     
        */
        showHideEditButton: function(currentProfileID){

            var currentProfileID = $('#opanda_style_profile').val(); 

            if( 'default' === currentProfileID ) {                     
                $('.onpSL-profile-edit-button').hide();
            } else {
                $('.onpSL-profile-edit-button').show();
            }
        },

        /**
         * Opens the Style Editor for a given style id.
         * 
         * @since 1.0.0
         * @return void               
        */
        editProfile: function( styleId ){
            var self = this;        

            self.showStyleEditorLoader();
            self.createFrameBuilder( window.bizpanda.adminUrl + 'admin.php?page=styler-page-builder-' + window.bizpanda.pluginName + '&action=edit&onp_theme_id='+self._currentSheme+'&onp_style_id='+styleId);

            $(".onpSL-profile-frame").load(function(){
               self.frameParentLoad(self);  
            });  
        },

        /**
         * Opens the Style Editor to create a new style for a current theme.
         * 
         * @since 1.0.0
         * @return void               
        */
        addNewProfile:function(){               
             var self = this;             

             self.showStyleEditorLoader();                
             self.createFrameBuilder( window.bizpanda.adminUrl + 'admin.php?page=styler-page-builder-' + window.bizpanda.pluginName + '&action=edit&onp_theme_id='+this._currentSheme);

             $(".onpSL-profile-frame").load(function(){
                self.frameParentLoad(self);  
             }); 
        },

        /**
         * Creates a new frame that will load a given URL.
         * 
         * @since 1.0.0
         * @param requstUrl An URL to load. 
         * @return void               
        */
        createFrameBuilder: function( requstUrl ){                
             $('body').append('<iframe class="onpSL-profile-frame" src="'+requstUrl+'"></iframe>'); 
        },

        /**
         * Called when the Style Editor frame is loaded.
         * 
         * @since 1.0.0
         * @param self A link to the current class.
         * @return void               
        */
        frameParentLoad: function(self){
            var self = this;

            var currentProfile = $('#opanda_style_profile').find('.current').val();

            var iframe = $(".onpSL-profile-frame")[0].contentWindow;
            $('.onpSL-profile-modal').hide();                                        
            $('.onpSL-profile-button').removeClass('active');

            iframe.setOptions( window.bizpanda.lockerEditor.getPreviewOptions() );               

            iframe.styleEditor.callbacks.saveFormData = function( styleId ) {
                self.refreshContentDropdown( styleId );                   
            };

            iframe.styleEditor.callbacks.styleDeleted = function( styleId ) {                    
                self.refreshContentDropdown( styleId ); 
            }; 

            iframe.styleEditor.callbacks.closeWindow = function( ) {                    
                $(".onpSL-profile-frame").fadeOut(400, function(){
                    $('.onpSL-profile-frame').remove();
                    $('body').css('overflow', 'auto');                     
                });
            };

            self.hideStyleEditorLoader();
        },

        /**
         * Shows a loader of the Style Editor.
         * 
         * @since 1.0.0
         * @return void                
        */
        showStyleEditorLoader: function(){
            $('body').css('overflow', 'hidden');
            $('body').append('<div class="onpSL-editor-loader"></div>');      
        },

        /**
         * Hides a loader of the Style Editor.
         * 
         * @since 1.0.0
         * @return void                
        */
        hideStyleEditorLoader: function() {               
           $('.onpSL-editor-loader').fadeOut(400, function(){
               $('.onpSL-editor-loader').remove();
           }); 
        },

        /**
         * Gets and updates CSS styles for a current style.
         * 
         * @since 1.0.0
         * @return void                
        */
        updateFrontPreviewStyle: function() {

            var sendArrayData = {};
                sendArrayData['onp_theme_id'] = $('#opanda_style').val();
                sendArrayData['onp_style_id'] = $('#opanda_style_profile').val(); 

            this.request({         
                url :  window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_get_css',                      
                data : sendArrayData,						
                success: function( data, el, responce ) {
                    
                    if ( data.fonts && data.fonts.length ) {
                        window.bizpanda.previewGoogleFonts = data.fonts;
                    }

                    window.bizpanda.lockerEditor.recreatePreview();
                    var frontFrame = $("#lock-preview-wrap").find('iframe').contents(); 

                    if( frontFrame.find('#slp-print-styles').length > 0 ) {
                        frontFrame.find("#slp-print-styles").text(data['css']);                                  

                    } else {
                        frontFrame.find('head') .append('<style id="slp-print-styles">'+data['css']+'</style>');                                
                    }  
     
                    $("#lock-preview-wrap").find('iframe')[0].contentWindow.alertFrameSize();
                },
                error: function( data, el, responce ) {
                  window.bizpanda.lockerEditor.recreatePreview();   

                  var frontFrame = $("#lock-preview-wrap").find('iframe').contents();  

                  if( frontFrame.find('#slp-print-styles').length )
                      frontFrame.find('#slp-print-styles').remove();
                }
            });       
        } 
    };

    $(document).ready(function(){
         window.bizpanda.styleroller.init();
    });

})(jQuery);



