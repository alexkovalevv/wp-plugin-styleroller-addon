/**
 * Style Editor
 * 
 * @author Alex Kovalev <alex@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd
 * 
 * @package styleroller
 * @since 1.0.0
 */

;(function($){  
    
    if ( !window.styleEditor ) window.styleEditor = {};

    window.styleEditor.callbacks = {};
    window.styleEditor.methods = {                
         
        /* ---------------------------------------------------------------------- */
        /* Initialization                                                         */
        /* ---------------------------------------------------------------------- */ 
        
        /**
         * Inits the editor.
         */
        init: function() { 
            
            this._initWidgets();
            this._initPreview();
            this._initRealtimeUpdatingPreview();
            this._initControlGroupSelector();
            this._initControlButtons();
            this._initScrollbar();
        },

        /**
         * Inits jquery plugins (widgets).
         */
        _initWidgets: function() {   
            
            $('.slp-chosen', '#slp-modal-editor').chosen();  
            
            $('div[id*="_tab"]', '#slp-modal-editor').each(function(i){              
                if( !i ) $(this).addClass('on');
            });
        },   
        
        /**
         * Inits the preview frame.
         */
        _initPreview: function() {
            var self = this;
            
            // after loading the preview
            $('#slp-profile-preview').load(function(){
                
                $('#slp-profile-preview').hide().css('visibility', 'visible');
                $('#slp-profile-preview').fadeIn(500);
                
                self.updatePreviewPosition();                      
                $( window ).resize(function() { self.updatePreviewPosition(); });
                $("#customize-preview").removeClass("slp-loading");
            });
        },
        
        /**
         * Inits the group selectors of controls.
         */
        _initControlGroupSelector: function() {
            var self = this;
            
            $('.onp-select-control-tab').unbind().change(function(){
                var groupId = $(this).val();
                self.showGroupControl( groupId );
                setTimeout(function(){
                    $(".slp-sidebar-inner").perfectScrollbar('update'); 
                },500);
                return false;
            });
        },
        
        /**
         * Inits events to update the preview in runtime.
         */
        _initRealtimeUpdatingPreview: function() {
            var self = this;
            
            $('select', '#slp-modal-editor').on('change keyup', function(){                        
                self.refreshPreview();
                return false;
            });  
            
            // all input except the color picker input (.factory-color-hex).
            // the color picker is proccessed below
            
            $('input:not(.factory-color-hex)', '#slp-modal-editor').on('change keyup', function(){                        
                self.refreshPreview();
                return false;
            });  
           
            // we created the custom event 'updated.color.factory' becuase for some unknown reasons
            // the event 'change.color.factory' triggers always even the color is not valid

            $(".factory-color", '#slp-modal-editor').bind('updated.color.factory', function(){                        
                self.refreshPreview();
                return false;
            });
            
            $('input[type=checkbox]').change(function(){                
                if( $(this).prop('checked') )
                    $(this).val(1);
                else 
                    $(this).val(0);
            }); 

            $("#profile_title").off("change").off("keyup");
        },
        
        /**
         * Inits control buttons (Save, Close, Delete).
         */
        _initControlButtons: function() {
            var self = this;
            
            // deleting a style
            $('.slp-delete-profile').on('click', function(){                
                self.showRemoveDialog();                  
                return false;
            });
            
            // saving a style
            $('input[name="sl_profile_editor_save"]').on('click', function(){ 
                self.saveFormData();
                return false;
            });
                        
            // closing the editor
            $('.spl-btn-editor-close').on('click', function(){ 
                self.closeWindow();
                return false;
            }); 
        },
       
       /**
        * Inits the scroll bar.
        */
        _initScrollbar: function() {
            var self = this;
            
            var $sidebar = $(".slp-sidebar");
            
            $(".slp-controls").on('show.y.ps', function(){
                $sidebar.addClass("slp-has-scroll");
            });
            
            $(".slp-controls").on('hide.y.ps', function(){
                $sidebar.removeClass("slp-has-scroll");
            });  
            
            this.$scrollbar = $(".slp-controls").perfectScrollbar({
                 wheelSpeed: 40,
                 wheelPropagation: false,
                 suppressScrollY: false,
                 suppressScrollX: true
            });       
            
            // updates scroll while switching tabs
            $(".factory-control-group-nav .factory-control-group-nav-label").on("click", function(){
                self.$scrollbar.perfectScrollbar('update');
            });
            
            // updates scroll while switching accordions
            $(".factory-accordion").on("shown.bs.accordion", function(){
                self.$scrollbar.perfectScrollbar('update');
            });     
            $(".factory-accordion > h3").on("click", function(){
                self.$scrollbar.scrollTop(0);
            }); 
            
            // updates scroll while switching control groups
            $('.slp-sidebar-left').on("shown", function(){
                self.$scrollbar.perfectScrollbar('update');
            }); 
        },
        
        /* ---------------------------------------------------------------------- */
        /* Public Methods                                                         */
        /* ---------------------------------------------------------------------- */ 
            
        /**
         * Updates the locker preview position.
        */
        updatePreviewPosition:function() {
            var windowHeight = $(window).height();
            var parentFrame =  $('#slp-profile-preview'); 
            
            var isBlurringMode = parentFrame.contents().find('.onp-sl-content-wrap').length > 0;
            var isTransparanceMode = parentFrame.contents().find('.onp-sl-content.onp-sl-overlap-mode').length > 0;
            
            if ( isBlurringMode ) {
                var marginTop = ( windowHeight / 2 ) - ( parentFrame.contents().find('.onp-sl-content-wrap').outerHeight() / 2 );
                parentFrame.contents().find('.onp-sl-content-wrap').css('margin-top',  marginTop + 'px');
            } else if ( isTransparanceMode ) {
                var marginTop = ( windowHeight / 2 ) - ( parentFrame.contents().find('.onp-sl-content').outerHeight() / 2 );
                parentFrame.contents().find('.onp-sl-content').css('margin-top',  marginTop + 'px');
            } else {
                var marginTop = ( windowHeight / 2 ) - ( parentFrame.contents().find('.onp-sl').outerHeight() / 2 );   
                parentFrame.contents().find('.onp-sl').css('margin-top',  marginTop + 'px');
            }
        },

        /**
         * Makes an ajax request.
         * 
         * @param mixed options
         * @returns jqXHR
         */
        request: function( options ) {
            var self = this;
            
            self.showLoader();
                        
            options.type = 'POST';
            options.dataType = 'text';
            
            var success = options.success;
            var error = options.error;
            var complete = options.complete;
            
            options.success = function( text ) {
                var data = {};
  
                try {
                    data = $.parseJSON( text );
                } catch (ex) {
                    if ( console ) console.log("Unable to parse the response got from the server: " + request.responseText);
                    self.showError("[Error] Unable to parse the response got from the server. Please try again or contact the support staff if the issue is repeated.");
                    
                    error && error( data );
                    return;
                }  
                
                if ( data && data['error'] ) {

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
                if (request.status === 0 || request.readyState === 0) return;
                
                if ( console ) console.log("Unexpected error occurred during the ajax request: " + request.responseText);
                self.showError("Unexpected error occurred during the ajax request. Please try again or contact the support staff if the issue is repeated.");

                error && error();
            };
            
            options.complete = function() {
                self.hideLoader();
                complete && complete();
            };
            
            var request = $.ajax( options );
            return request;
        },
        
        /**
         * Shows an error.
         */
        showError: function ( message ) {
            var $error = $("#slp-error-container").html( message ).fadeIn(500);
            setTimeout(function(){
                $error.fadeOut(1000);
            }, 5000);
        },

        /**
         * Shows a given group of control.
         */
        showGroupControl: function( groupId ) {
            $self = $(this);

            $('.slp-sidebar-left').stop().animate({marginLeft:-300}, 500, function(){
                $('.factory-accordion', '.slp-control-group.on').find('div.active').hide();
                $('.factory-accordion', '.slp-control-group.on').find('.active').removeClass('active');
                
                $('.slp-control-group').removeClass('on');
                
                var $groupTab = $('#'+groupId+'_tab');
                $groupTab.addClass('on');   
                
                $groupTab.find('.factory-accordion').find('h3:first')
                         .add( $groupTab.find('.factory-accordion').find('div:first') )
                         .show()
                         .addClass('active');
                
                $('.slp-sidebar-left').trigger('shown');
                
                $(this).animate({marginLeft:0}, 500);
            }); 
        },

       /**
        * Shows the global loader.
        */
        showLoader: function() {
            $("#slp-modal-editor").addClass("slp-updating-preview");
        },

       /**
        * Hides the global loader.
        */
        hideLoader: function() {
            $("#slp-modal-editor").removeClass("slp-updating-preview");
        },

        _timerOn: false,
        _timerAgain: false,

        /**
         * Refreshes the preview with a bit delay.
         */
        refreshPreview: function( force ) {           
           var self = this;
           
           self.showLoader();
                       
           if ( self._timerOn && !force ) { 
               self._timerAgain = true; return;
           }
           self._timerOn = true;

           // makes a bit delay for 0.7s
           setTimeout(function(){
              
                if ( self._timerAgain ) { 
                    self._timerAgain = false; self.refreshPreview( true ); 
                } else { 
                    self._timerAgain = false; 
                    self._timerOn = false;  

                    var sendArrayData = self.getFormData();  
                    var framePreview = $('#slp-profile-preview').contents();
                     
                    sendArrayData['onp_use_post'] = true;
                     
                    self.request({             
                        url :  window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_get_css',
                        data : sendArrayData,						
                        success: function( data ) {
                           console.log(data['css']);
                           
                           if( framePreview.find('#slp-print-styles').length ) {                                
                               framePreview.find("#slp-print-styles").text(data['css']);                                  
                           } else {                                  
                               framePreview.find('head').append('<style id="slp-print-styles">'+data['css']+'</style>');
                           }
                           
                           self.updatePreviewPosition();                                               
                        }
                    });                
                }
            }, 700);
        },
            
        /**
         * Returns all data from the form to post.
         */
        getFormData: function() {
            var sendArrayData = {};

            var fromData = $('#slp-editor-form').serializeArray();  
            
            for( i=0; i < fromData.length; i++ ) {
                sendArrayData[fromData[i]['name']] = fromData[i]['value'];                     
            }
   
            if( $('input[type=checkbox]').length ) {
                $('input[type=checkbox]').each(function(){
                    sendArrayData[$(this).attr('name')] = $(this).val();
                });
            }
                       
            return sendArrayData;
        },
        
        /**
         * Saves a current style.
         */
        saveFormData: function() {
            self = this;
            var sendArrayData = self.getFormData();           

            $('input[name="sl_profile_editor_save"]').prop('disabled', true);
            $("#slp-modal-editor").addClass("slp-saving-style");
            
            this.request({          
                url :  window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_save_style',
                data : sendArrayData,
                success: function( data ) {      
                    if ( window.styleEditor.callbacks.saveFormData ) window.styleEditor.callbacks.saveFormData( data['styleId'] );
                    self.closeWindow();
                },
                complete: function() {
                    $('input[name="sl_profile_editor_save"]').prop('disabled', false);
                    $("#slp-modal-editor").removeClass("slp-saving-style");
                }
            });
        },  
         
        /**
         * Shows the remove dialog.
         */
        showRemoveDialog: function(){
            var self = this;
            
            if ( window.styleEditor.isDefault ) {
                this.showUnableToRemove(); 
                return;
            }
            
            if ( !this._isRemoveDialogInited ) {
                this._isRemoveDialogInited = true;
                
                this._dialogRemove = $("#slp-remove-dialog");
                this._dialogRemove.find(".slp-submit-button").click(function(){
                    
                    var dataToSend = {
                        'onp_theme_id': $("#onp_theme_id").val(),
                        'onp_style_id': $("#onp_style_id").val()
                    };

                    self.request({             
                        url :  window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_remove_style',                        
                        data : dataToSend,						
                        success: function( data ) {   
                            
                            if ( data["used"] ) {
                                self._dialogRemove.factoryBootstrap000_modal('hide');
                                self.showReplaceAndDeleteDialog( data['styles'] );
                            } else {
                                if ( window.styleEditor.callbacks.styleDeleted ) window.styleEditor.callbacks.styleDeleted();  
                                self.closeWindow();
                            }
                        }                   
                    });  
                });
            }
            
            this._dialogRemove.factoryBootstrap000_modal('show');
        },     
        
        /**
         * Shows a dialog telling that it's not possible to delete the style.
         */
        showUnableToRemove: function() {
            $("#slp-unable-to-remove-dialog").factoryBootstrap000_modal('show');
        },
        
        /**
         * Shows a dialog offering to select another style for lockers where the current one is used.
         */
        showReplaceAndDeleteDialog: function( styles ) {
            var self = this;
            
            if ( !this._isReplaceAndDeleteDialogInited ) {
                this._isReplaceAndDeleteDialogInited = true;
                
                this._dialogReplaceAndDelete = $("#slp-replace-and-delete-dialog");
                
                // enable the button after selection of the style to replace
                this._dialogReplaceAndDelete.find('.slp-replacewith').change(function(){
                    var $styles = self._dialogReplaceAndDelete.find('.slp-replacewith');
                    if ( $styles.val() ) self._dialogReplaceAndDelete.find(".slp-submit-button").prop('disabled', false);
                });

                // replace and delete the style
                this._dialogReplaceAndDelete.find(".slp-submit-button").click(function(){
                    
                    var dataToSend = {
                        'onp_theme_id': $("#onp_theme_id").val(),
                        'onp_style_id': $("#onp_style_id").val(),
                        'onp_selected': self._dialogReplaceAndDelete.find('.slp-replacewith').val()
                    };

                    self.request({             
                        url :  window.bizpanda.adminUrl + 'admin-ajax.php?action=opanda_sr_remove_style',                        
                        data : dataToSend,						
                        success: function( data ) { 
                            if ( window.styleEditor.callbacks.styleDeleted ) window.styleEditor.callbacks.styleDeleted( dataToSend.onp_selected );
                            self.closeWindow();
                        }                   
                    });  
                });
            }

            // fill up the list
            var pasteOption = '<option value="0" selected="selected">- select style -</option>';
            for( key in styles ) pasteOption += '<option value="'+key+'">'+styles[key]+'</option>';
            this._dialogReplaceAndDelete.find('.slp-replacewith').html(pasteOption);
            self._dialogReplaceAndDelete.find(".slp-submit-button").prop('disabled', 'disabled');

            this._dialogReplaceAndDelete.factoryBootstrap000_modal('show');
        },
        
        /**
         * Closes the editor.
         */
        closeWindow: function() {
            if ( window.styleEditor.callbacks.closeWindow ) window.styleEditor.callbacks.closeWindow();  
        }
    };

    $(document).ready(function(){
        window.styleEditor.methods.init();
    });

})(jQuery);