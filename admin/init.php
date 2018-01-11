<?php
/**
 * Contains actions for hooks in the admin part.
 * 
 * @author Alex Kovalev <alex@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * 
 * @since 1.0.0
 * @package styleroller
 */

require(OPANDA_SR_PLUGIN_DIR. '/admin/activation.php');

if (onp_license('trial', 'paid')) {
require(OPANDA_SR_PLUGIN_DIR. '/admin/actions.php'); 
}

/**
 * Adds scripts and styles in the admin area.
 * 
 * @see the 'admin_enqueue_scripts' action
 * 
 * @since 1.0.0
 * @return void
 */
function opanda_sr_style_admin_assets( $hook ) {

    // sytles for the plugin notices
    if ( $hook == 'index.php' || $hook == 'plugins.php' || $hook == 'edit.php' )
        wp_enqueue_style( 'onp-sl-style-notices', OPANDA_SR_PLUGIN_URL . '/assets/css/notices.010000.css' ); 
}
add_action('admin_enqueue_scripts', 'opanda_sr_style_admin_assets');

/**
 * Inits the admin actions.
 * 
 * @since 1.0.0
 * @return void
 */
function opanda_sr_style_admin_init() {
    if ( !is_admin() ) return;
    
    if ( !defined( 'LOADING_STYLEROLLER_AS_ADDON' ) )
        require(OPANDA_SR_PLUGIN_DIR . '/admin/pages/license-manager.php');

    require(OPANDA_SR_PLUGIN_DIR . '/admin/pages/style-editor.php');
    require(OPANDA_SR_PLUGIN_DIR . '/admin/ajax/style-editor-actions.php');
}
add_action( 'init', 'opanda_sr_style_admin_init' ); 

/**
 * Returns an URL where we should redirect a user after success activation of the plugin.
 * 
 * @since 1.0.0
 * @return string
 */
function opanda_sr_style_license_manager_success_button( $text = null ) {
    if( !defined('ONP_OP_PLUGIN_ACTIVE') ) return $text;
    return 'Learn how to use the StyleRoller Add-On <i class="fa fa-lightbulb-o"></i>';
}
add_action('onp_license_manager_success_button_styleroller', 'opanda_sr_style_license_manager_success_button');

/**
 * Returns an URL where we should redirect a user after success activation of the plugin.
 * 
 * @since 1.0.0
 * @return string
 */
function opanda_sr_style_license_manager_success_redirect( $url = null ) {
    if ( !defined('ONP_OP_PLUGIN_ACTIVE' ) ) return $url;
    
    global $bizpanda;
    $args = array(
        'post_type' => 'social-locker',
        'page' => 'how-to-use-' . $bizpanda->pluginName,
        'opanda_sr_page' => 'styleroller'
    );
    return admin_url( 'edit.php?' . http_build_query( $args ) );
}
add_action('onp_license_manager_success_redirect_styleroller',  'opanda_sr_style_license_manager_success_redirect');

/**
 * Adds a new help item in the "How to use".
 * 
 * @since 1.0.0
 */
function opanda_sr_style_help_pages( $urls ) {
    global $sociallocker;
    if ( $sociallocker->build === 'premium' || $sociallocker->build == 'offline' ) {
        require_once OPANDA_SR_PLUGIN_DIR . '/admin/pages/help.php';
        array_splice($urls, 1, 0, array( array(
            'name' => 'styleroller',
            'function' => 'opanda_sr_style_styleroller_help',
            'title' => __('StyleRoller Add-on', 'styleroller')
        )));
    }
    return $urls;
}
add_filter('opanda_sr_help_pages', 'opanda_sr_style_help_pages'); 
