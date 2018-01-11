<?php
/**
 * Ajax requests linked with editing styles.
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @author Alex Kovalev <alex@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd
 * 
 * @package styleroller 
 * @since 1.0.0
 */

/**
 * Gets styles for a given theme.
 * 
 * @since 1.0.0
 * @return void
*/
function opanda_sr_get_theme_styles(){
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php';  
    
    $themeId = isset( $_POST['onp_theme_id'] ) ? $_POST['onp_theme_id'] : null;
    
    if( !$themeId ) {
        json_encode( array('error' => '[Error] The theme id [onp_theme_id] is not specified.') );
        exit;
    }
    
    $styles = OnpSL_StyleManager::getStylesTitles( $themeId );

    echo json_encode( $styles );
    exit;
} 
add_action("wp_ajax_opanda_sr_get_theme_styles", 'opanda_sr_get_theme_styles');

/**
 * Return css code of a given style.
 * 
 * @since 1.0.0
 * @return void
*/
function opanda_sr_get_css() {
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php';  
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/generator/css-generator.class.php';
        
    $themeId = isset( $_POST['onp_theme_id'] ) ? $_POST['onp_theme_id'] : null;
    $styleId = isset( $_POST['onp_style_id'] ) ? $_POST['onp_style_id'] : null;
    $usePost = isset( $_POST['onp_use_post'] );
    
    if ( !$themeId || !$styleId ) {
        echo json_encode( array('error' => '[Error] The theme id [onp_theme_id] or style id [onp_style_id] are not specified.') );
        exit;
    }

    // if the option "onp_use_post" set, then takes the data from POST,
    // else from the options stored in the database.
    
    if ( $usePost ) {
        
        $startCssGenerator = new OnpSL_CSSGenerator($themeId, $_POST);
        echo json_encode(array(
            'css' => $startCssGenerator->getCSS( $styleId )
        ));
        
    } else {
        
        $style = OnpSL_StyleManager::getStyle($themeId, $styleId); 
        $fonts = OnpSL_StyleManager::getGoogleFonts($themeId, $styleId); 

        echo json_encode(array(
            'css' => isset ( $style['style_cache'] ) ? $style['style_cache'] : '',
            'fonts' => $fonts
        ));
    }
    
    exit();            
}
add_action("wp_ajax_opanda_sr_get_css", 'opanda_sr_get_css');

/**
 * Saves the style.
 * 
 * @since 1.0.0
 * @return void
*/        
function opanda_sr_save_style() {   
    require_once OPANDA_BIZPANDA_DIR . '/includes/classes/themes-manager.class.php';
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-provider.class.php';
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/generator/css-generator.class.php';
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php'; 
 
    $themeId = isset( $_REQUEST['onp_theme_id'] ) ? $_REQUEST['onp_theme_id'] : null;
    $styleId = isset( $_REQUEST['onp_style_id'] ) ? $_REQUEST['onp_style_id'] : null;
    
    if ( !$themeId ) {
        json_encode( array('error' => '[Error] The theme id [onp_theme_id] is not specified.') );
        exit;
    }
    
    $startCssGenerator = new OnpSL_CSSGenerator($themeId, $_POST);    
    
    $_POST['style_cache'] = $startCssGenerator->getCSS( $styleId, true );
    $_POST['style_fonts'] = json_encode( OnpSL_StyleManager::getGoogleFonts($themeId, $styleId, $_POST) );
    
    $provider = new onpStrl_StyleProvider(array(
       'themeId' => $themeId,
       'styleId' => $styleId
    ));
    
    global $bizpanda;
    $form = new FactoryForms000_Form( array(), $bizpanda ); 
    $form->setProvider( $provider ); 

    // adding some virtual field into the form in order to save thier values by the provider
    $form->add( array(
        array(            
            'type'      => 'textbox',
            'name'      => 'profile_title'   
        ),
        array(            
            'type'      => 'textbox',
            'name'      => 'style_cache'   
        ),
        array(
            'type'      => 'textbox',
            'name'      => 'style_fonts'      
        )
     ));

    $themeOptions = OPanda_ThemeManager::getEditableOptions($themeId);
    foreach( $themeOptions as $val ) {
       $form->add($val[2]);                                       
    }

    //сохраняем        
    $styleId = $form->save();   

    echo json_encode(array(
        'styleId' => $styleId
    ));

    exit();          
}
add_action("wp_ajax_opanda_sr_save_style", 'opanda_sr_save_style');

/**
 * Removes a given style.
 * 
 * @since 1.0.0
 * @return void
*/ 
function opanda_sr_remove_style() { 
    require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php'; 

    $themeId = isset( $_REQUEST['onp_theme_id'] ) ? $_REQUEST['onp_theme_id'] : null;
    $styleId = isset( $_REQUEST['onp_style_id'] ) ? $_REQUEST['onp_style_id'] : null; 

    if ( !$themeId || !$styleId ) {
        echo json_encode( array('error' => '[Error] The theme id [onp_theme_id] or style id [onp_style_id] are not specified.') );
        exit;
    }

    $replaceWith = isset( $_REQUEST['onp_selected'] ) ? $_REQUEST['onp_selected'] : null;
    if ( $replaceWith ) {
        OnpSL_StyleManager::removeStyle($themeId, $styleId, $replaceWith);
        
        echo json_encode(array( 'success' => true ));
        exit;
    }

    $result = OnpSL_StyleManager::removeStyle($themeId, $styleId);
    if ( !$result ) {
        $styles = OnpSL_StyleManager::getStylesTitles( $themeId );
        unset($styles[$styleId]);
        
        echo json_encode(array(
            'used' => true,
            'styles' => $styles
        ));
        exit;
    }
    
    echo json_encode(array( 'success' => true ));
    exit;
}
add_action("wp_ajax_opanda_sr_remove_style", 'opanda_sr_remove_style');