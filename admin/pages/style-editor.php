<?php

/**
 * Style Editor
 *
 * @author Alex Kovalev <paul@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd

 * @package styleroller
 * @since 1.0.0
 */
class OPanda_SR_StylerPage extends FactoryPages000_AdminPage  {

    /**
     * Capabilities for roles that have access to work with this page.
     * Leave it empty to use inherited capabilities for custom post type menu.
     * @link http://codex.wordpress.org/Roles_and_Capabilities
     * @var array An array of the capabilities.
     */
    public $capabilitiy = 'manage_options';

    public $id = 'styler-page-builder';
    public $internal = true;

    /**
     * Requests assets (js and css) for this page.
     *
     * @see FactoryPages000_AdminPage
     *
     * @since 1.0.0
     * @return void
     */
    public function assets($scripts, $styles) {

        $this->scripts->request( array(
            'global.color-functions',
            'bootstrap.transition',
            'bootstrap.modal',
            'bootstrap.accordion',
            'plugin.color',
            'plugin.iris',
            'plugin.chosen',
            'plugin.gradient-picker',
            'plugin.nouislider',
            'plugin.paddings-editor',
            'control.pattern',
            'control.color',
            'control.integer',
            'control.checkbox',
            'control.color-and-opacity',
            'control.gradient',
            'control.fonts',
            'holder.conrol-group'
        ), 'bootstrap' );

        $this->styles->request( array(
          'bootstrap.core',
          'bootstrap.accordion',
          'plugin.gradient-picker',
          'plugin.chosen',
          'plugin.nouislider',
          'plugin.paddings-editor',
          'control.color',
          'control.checkbox',
          'control.pattern',
          'control.integer',
          'control.color-and-opacity',
          'holder.conrol-group',
          'control.fonts'
        ), 'bootstrap' );

        // Assets for Wordpress Media Manager
        if( function_exists( 'wp_enqueue_media' ) ){
            wp_enqueue_media();
        }else{
            wp_enqueue_style('thickbox');
            $this->scripts->request('media-upload');
            $this->scripts->request('thickbox');
        }

        // Base assets
        $this->styles->add(OPANDA_SR_PLUGIN_URL . '/assets/css/style-editor.css');
        $this->styles->add(OPANDA_SR_PLUGIN_URL . '/assets/css/perfect-scrollbar.css');

        $this->scripts->add(OPANDA_SR_PLUGIN_URL . '/assets/js/libs/jquery.mousewheel.js');
        $this->scripts->add(OPANDA_SR_PLUGIN_URL . '/assets/js/libs/jquery.perfect-scrollbar.js');
        $this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/preview.js');
        $this->scripts->add(OPANDA_SR_PLUGIN_URL . '/assets/js/style-editor.js');
    }

    public function editAction() {
        require_once OPANDA_BIZPANDA_DIR . '/includes/classes/themes-manager.class.php';
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php';
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-provider.class.php';

        global $bizpanda;
        $form = new FactoryForms000_Form( array(), $bizpanda );

        $themeId = isset( $_GET['onp_theme_id'] ) ? $_GET['onp_theme_id'] : null;
        $styleId = isset( $_GET['onp_style_id'] ) ? $_GET['onp_style_id'] : null;

        if ( !$themeId ) {
            die("The theme id is not specified.");
        }

        $formProvider = new OnpStrl_StyleProvider(array(
            'themeId' => $themeId,
            'styleId' => $styleId,
        ));

        $form->setProvider( $formProvider );

        // loads a theme file which contains hooks required to edit a current theme
        $profile_data = OPanda_ThemeManager::getEditableOptions( $themeId );

        if ( $profile_data ) {

            foreach( $profile_data as $val ){
                $form->add(
                     array(
                         'type'   => 'div',
                         'id'     => $val[1]."_tab",
                         'class'  => 'slp-control-group',
                         'items'  => $val[2]
                     )
               );
            }
        }

        $profile_title = __('New Style', 'styleroller');
        if( isset( $styleId ) ){
            $style = OnpSL_StyleManager::getStyle($themeId, $styleId);
            if ( isset( $style['profile_title'] )) $profile_title = $style['profile_title'];
        }

        $profiles_defaults = get_option('opanda_sr_defaults');

        $isDefault = isset( $profiles_defaults[$themeId] ) && $profiles_defaults[$themeId] == $styleId;
        if ( $isDefault ) die( __( 'Sorry, editing the default styles is  not allowed.', 'styleroller') );

        if ( !$styleId ) $styleId = uniqid();
    ?>
    <script>
        if ( !window.styleEditor ) window.styleEditor = {};
        window.styleEditor.themeId = '<?php echo $themeId ?>';
        window.styleEditor.styleId = '<?php echo ( $styleId ? $styleId : '' ); ?>';
        window.styleEditor.isDefault = <?php echo ( $isDefault ? 'true' : 'false' ) ?>;

        if ( ! window.bizpanda )  window.bizpanda = {};
         window.bizpanda.adminUrl = "<?php echo get_admin_url() ?>";

        //Устанавливает опции для конструктора локеров
        window.setOptions = function ( options ) {
            var url = '<?php echo admin_url('admin-ajax.php') . '?action=onp_sl_preview'.( $styleId ? '&onp_style_id='. $styleId : '' ).'&onp_theme_id='. $themeId ?>';
            var name = 'preview';
            if ( window.styleEditor.styleId ) options.cssClass = 'p' + window.styleEditor.styleId;
             window.bizpanda.preview.refresh(url, name, options);
        }
    </script>

    <div id="slp-modal-editor" class="factory-bootstrap-000 factory-fontawesome-000">

        <div id="slp-loader"></div>
        <div id="slp-error-container"></div>

        <div class="slp-sidebar slp-sidebar-left">
            <form id="slp-editor-form" method="post" action="">

                <div id="slp-saving-loader"></div>

                <div class="slp-controls">
                    <div class="slp-inner-wrap">

                        <div class="slp-header">
                            <?php if( sizeof($profile_data) > 1 ): ?>
                                <label for="slp-select-elements"><?php _e('Select elemens to customize:', 'styleroller' ) ?></label>
                                <select name="select_control_tab" class="slp-chosen onp-select-control-tab" id="slp-select-elements">
                                <?php foreach( $profile_data as $val ): ?>
                                    <option value="<?php echo $val[1]; ?>"><?php echo $val[0]; ?></option>
                                <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="slp-sidebar-elements-inner-wrap">
                            <?php if ( false === $profile_data ) { ?>
                                <div style="padding: 0 30px;">
                                    <p><strong><?php _e('Sorry, your version of the StyleRoller doesn\'t support editing this theme.', 'styleroller') ?></strong></p>
                                    <p><?php _e('Maybe you\'re using the old version of the StyleRoller.', 'styleroller') ?></p>
                                    <p><?php _e('Or the theme you try to edit has been created recently. In this case, please wait until updating the StyleRoller.', 'styleroller') ?></p>
                                </div>
                            <?php } else { ?>
                                <?php  $form->html(); ?>
                            <?php } ?>
                        </div>
                    </div>

                </div>

                <div class="slp-sidebar-footer">

                    <div class="slp-style-title slp-disabled">
                        <label for="profile_title">
                            <span class="slp-title-edit"></span>
                            <input type="text" class="factory-input-text" id="profile_title" name="profile_title" value="<?php echo $profile_title; ?>" />
                        </label>
                        <input type="hidden" id="onp_theme_id" name="onp_theme_id" value="<?php echo $themeId ?>">
                        <input type="hidden" id="onp_style_id" name="onp_style_id" value="<?php echo $styleId ?>">
                    </div>

                    <div class="slp-sidebar-footer-left">
                        <input type="submit" name="sl_profile_editor_save" class="button button-primary" value="<?php _e('Save', 'styleroller' ) ?>">
                        <a class="button button-secondary spl-btn-editor-close" href="#"><?php _e('Close', 'styleroller' ) ?></a>
                    </div>
                    <div class="slp-sidebar-footer-right">
                        <a href="#" class="slp-delete-profile"><i class="fa fa-trash-o"></i> <?php _e('Delete', 'styleroller' ) ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div id="customize-preview" class="wp-full-overlay-main slp-loading">
            <div class="slp-preview-inner-wrap">
                <iframe
                    id="slp-profile-preview"
                    allowtransparency="1"
                    frameborder="0"
                    hspace="0"
                    marginheight="0"
                    marginwidth="0"
                    name="preview"
                    vspace="0"
                 >
                    Your browser doen't support the iframe tag.
                </iframe>
            </div>
        </div>

        <div class="modal fade" id="slp-unable-to-remove-dialog" tabindex="-1" role="dialog" aria-labelledby="unableToDeleteDialog" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="unableToDeleteDialog"><?php _e('Sorry, it\'s not possible', 'styleroller') ?></h4>
                </div>
                <div class="modal-body">
                    <?php _e('You\'re trying delete the default style for one of themes. Sorry, it\'s not possible.', 'styleroller') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
                </div>
              </div>
            </div>
        </div>

        <div class="modal fade" id="slp-remove-dialog" tabindex="-1" role="dialog" aria-labelledby="removeDialog" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="removeDialog"><?php _e('Deleting Style', 'styleroller') ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php _e('Are you sure you want to delete this style?', 'styleroller') ?></p>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'styleroller') ?></button>
                <button type="button" class="btn btn-primary slp-submit-button"><?php _e('Yes, I\'m sure', 'styleroller') ?></button>
                </div>
              </div>
            </div>
        </div>

        <div class="modal fade" id="slp-replace-and-delete-dialog" tabindex="-1" role="dialog" aria-labelledby="repalceAndDeleteDialog" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title" id="repalceAndDeleteDialog"><?php _e('Deleting Style', 'styleroller') ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php _e('This style is used by one of lockers. Please select a style that will be used as a replacement for them.', 'styleroller') ?></p>
                    <select class="slp-replacewith">
                        <option>Select profile</option>
                    </select>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'styleroller') ?></button>
                <button type="button" class="btn btn-primary slp-submit-button"><?php _e('Ok, replace and delete', 'styleroller') ?></button>
                </div>
              </div>
            </div>
        </div>

    </div>

    <?php
    }
}

global $styleroller;
FactoryPages000::register($styleroller, 'OPanda_SR_StylerPage');