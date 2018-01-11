<?php
	/**
	 * Plugin Name: {comp:styleroller}
	 * Plugin URI: {comp:pluginUrl}
	 * Description: {comp:description}
	 * Author: OnePress
	 * Version: 2.0.0
	 * Author URI: http://byoneress.com/
	 */

	define('ONP_OP_STYLER_PLUGIN_ACTIVE', true);
	define('OPANDA_SR_PLUGIN_URL', plugins_url(null, __FILE__));
	define('OPANDA_SR_PLUGIN_DIR', dirname(__FILE__));
	define('ONP_OP_STYLER_PLUGIN', __FILE__);

	$asPlugin = !defined('LOADING_STYLEROLLER_AS_ADDON');

	#comp remove
	// the following constants are used to debug features of diffrent builds
	// on developer machines before compiling the plugin

	if( !defined('BUILD_TYPE') ) {
		define('BUILD_TYPE', 'premium');
	}
	if( !defined('LANG_TYPE') ) {
		define('LANG_TYPE', 'ru_RU');
	}
	if( !defined('LICENSE_TYPE') ) {
		define('LICENSE_TYPE', 'paid');
	}

	#endcomp

	#comp remove
	// the compiler library provides a set of functions like onp_build and onp_license
	// to check how the plugin work for diffrent builds on developer machines

	if( $asPlugin ) {
		require('libs/onepress/compiler/boot.php');
	}
	#endcomp

	load_plugin_textdomain('styleroller', false, dirname(plugin_basename(__FILE__)) . '/langs');

	if( $asPlugin ) {

		// creating a plugin via the factory
		require('libs/factory/core/boot.php');
		global $styleroller;

		if( onp_lang('ru_RU') ) {
			$styleroller = new Factory000_Plugin(__FILE__, array(
				'name' => 'styleroller-rus',
				'title' => __('StyleRoller Add-On', 'styleroller'),
				'version' => '2.0.0',
				'assembly' => BUILD_TYPE,
				'api' => 'http://api.byonepress.com/1.1/',
				'premium' => "http://sociallocker.ru/styleroller",
				'account' => 'http://accounts.byonepress.com/',
				'updates' => OPANDA_SR_PLUGIN_DIR . '/includes/updates/',
				'tracker' => /*@var:tracker*/
					'C74F86F208C466B66E5057A3C347CF78'/*@*/
			));
		} else {
			$styleroller = new Factory000_Plugin(__FILE__, array(
				'name' => 'styleroller',
				'title' => __('StyleRoller Add-On', 'styleroller'),
				'version' => '2.0.0',
				'assembly' => BUILD_TYPE,
				'api' => 'http://api.byonepress.com/1.1/',
				'premium' => 'http://sociallocker.org/styleroller',
				'account' => 'http://accounts.byonepress.com/',
				'updates' => OPANDA_SR_PLUGIN_DIR . '/includes/updates/',
				'tracker' => /*@var:tracker*/
					'C74F86F208C466B66E5057A3C347CF78'/*@*/
			));
		}

		// requires factory modules
		$styleroller->load(array(
			array('libs/factory/bootstrap', 'factory_bootstrap_000', 'admin'),
			array('libs/factory/font-awesome', 'factory_fontawesome_000', 'admin'),
			array('libs/factory/forms', 'factory_forms_000', 'admin'),
			array('libs/factory/notices', 'factory_notices_000', 'admin'),
			array('libs/factory/pages', 'factory_pages_000', 'admin'),
			array('libs/onepress/api', 'onp_api_000'),
			array('libs/onepress/licensing', 'onp_licensing_000'),
			array('libs/onepress/updates', 'onp_updates_000')
		));
	} else {

		global $styleroller;
		global $bizpanda;
		global $sociallocker;

		$styleroller = $sociallocker;
	}

	if( is_admin() ) {
		require(OPANDA_SR_PLUGIN_DIR . '/admin/init.php');
	}

	/**
	 * Inits the frontend actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_styler_init()
	{

		if( !defined('OPTINPANDA_PLUGIN_ACTIVE') && !defined('SOCIALLOCKER_PLUGIN_ACTIVE') ) {
			return false;
		}
		if( !defined('OPANDA_ACTIVE') ) {
			return false;
		}

		if( onp_license('trial', 'paid') ) {
			require(OPANDA_SR_PLUGIN_DIR . '/includes/fronted-actions.php');
		}
	}

	add_action('init', 'opanda_sr_styler_init');

