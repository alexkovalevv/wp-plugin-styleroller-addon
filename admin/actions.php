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

	/**
	 * Adds loading the add-on assets on the locker editor page.
	 *
	 * @see the 'opanda_sr_sociallocker_type_assets' hook.
	 *
	 * @since 1.0.0
	 * @param Factory000_ScriptList $scripts
	 * @param Factory000_StyleList $styles
	 * @return mixed[]
	 */
	function opanda_sr_type_assets($scripts, $styles)
	{

		$scripts->add(OPANDA_SR_PLUGIN_URL . '/assets/js/item-edit.010100.js');
		$styles->add(OPANDA_SR_PLUGIN_URL . '/assets/css/item-edit.010100.css');
	}

	add_action('bizpanda_panda-item_edit_assets', 'opanda_sr_type_assets', 10, 2);

	/**
	 * Prints styles for the admin preview.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_style_print_preview_assets()
	{
		require_once OPANDA_SR_PLUGIN_DIR . '/includes/style-manager.class.php';

		$cssToPrint = '';
		if( isset ($_REQUEST['onp_theme_id']) && isset ($_REQUEST['onp_style_id']) ) {
			$style = OnpSL_StyleManager::getStyle($_REQUEST['onp_theme_id'], $_REQUEST['onp_style_id']);
			$cssToPrint = isset($style['style_cache'])
				? $style['style_cache']
				: '';
		}

		?>
		<style id="slp-print-styles">
			<?php echo $cssToPrint; ?>
		</style>
	<?php
	}

	add_action('opanda_preview_print_scripts', 'opanda_sr_style_print_preview_assets', 10, 1);

	/**
	 * Adds the theme and style variables to the locker preivew.
	 *
	 * @since 1.0.0
	 * @return string An a query string of the locker preview.
	 */
	function opanda_sr_style_add_variables_to_preview($query_string)
	{
		if( isset($_GET['post']) && !empty($_GET['post']) ) {
			$currentProfileID = get_post_meta($_GET['post'], 'opanda_style_profile', true);
			$opanda_style = get_post_meta($_GET['post'], 'opanda_style', true);
			$query_string = $query_string . '&onp_style_id=' . $currentProfileID . '&onp_theme_id=' . $opanda_style;
		}

		return $query_string;
	}

	add_filter('bizpanda_preview_url', 'opanda_sr_style_add_variables_to_preview', 10, 1);

	/*
	 * Adds javascript variable into the haed sectoion of locker editor page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_style_sociallocker_type_admin_head()
	{
		global $styleroller;

		?>
		<script>
			if( !window.bizpanda ) {
				window.bizpanda = {};
			}
			window.bizpanda.adminUrl = "<?php echo get_admin_url() ?>";
			window.bizpanda.pluginName = "<?php echo $styleroller->pluginName ?>";
		</script>
	<?php
	}

	add_action('factory_opanda-item_type_admin_head', 'opanda_sr_style_sociallocker_type_admin_head');

	/**
	 * Registers a new form control, the Style Selector.
	 *
	 * It replaces the standard dropdown control with which the user was able to select a theme.
	 * @see the 'Factory Forms' module
	 *
	 * @return void
	 */
	function opanda_sr_style_register_factory_forms_controls($plugin)
	{

		global $bizpanda;
		if( $plugin != $bizpanda ) {
			return;
		}

		$bizpanda->forms->registerControls(array(
			array(
				'type' => 'opanda_sr_styler_style_selector',
				'class' => 'OnpSL_Styler_StyleSelector',
				'include' => OPANDA_SR_PLUGIN_DIR . '/includes/factory-forms/style-selector.class.php'
			)
		));
	}

	add_action('factory_forms_register_controls', 'opanda_sr_style_register_factory_forms_controls');

	/**
	 * Replaces the default theme selector with the theme + style selector.
	 *
	 * @see factory_form_items
	 *
	 * @since 1.0.0
	 * @return mixed[]
	 */
	function opanda_sr_style_add_style_selector($items, $form_name)
	{
		if( $form_name !== 'OPanda_BasicOptionsMetaBox' ) {
			return $items;
		}

		foreach($items as $key => $val) {
			if( isset($val['name']) && $val['name'] == 'style' && $val['type'] == 'dropdown' ) {
				$themes = $val['data'];
				$items[$key] = array(
					'type' => 'opanda_sr_styler_style_selector',
					'name' => 'style',
					'data' => $themes,
					'title' => $val['title'],
					'hint' => $val['hint'],
					'default' => array('secrets', 'default-secrets')
				);
			}
		}

		return $items;
	}

	add_filter('factory_form_items', 'opanda_sr_style_add_style_selector', 10, 2);

	/**
	 * Cache for the function opanda_sr_style_set_patterns.
	 *
	 * @see opanda_sr_style_set_patterns
	 *
	 * @var mixed
	 * @since 1.0.0
	 */
	global $_opanda_sr_style_set_patterns_cache;
	$_opanda_sr_style_set_patterns_cache = array();

	/**
	 * Defines background patterns available by defaut for the pattern selector in the locker editor.
	 *
	 * @see the 'factory_forms_000_patterns' hook
	 *
	 * @since 1.0.0
	 * @return mixed[]
	 */
	function opanda_sr_style_set_patterns($patterns)
	{
		global $_opanda_sr_style_set_patterns_cache;
		if( !empty($_opanda_sr_style_set_patterns_cache) ) {
			return $_opanda_sr_style_set_patterns_cache;
		}

		$patterns = array();
		$folders = array();

		$groups = array(
			'abstract' => __('Abstract Patterns', 'styleroller'),
			'paper' => __('Paper & Fabric Patterns', 'styleroller'),
			'wood' => __('Woods Patterns', 'styleroller'),
			'vivid' => __('Retro Patterns', 'styleroller'),
		);

		foreach($groups as $groupKey => $groupOptions) {

			$groupPatterns = array();

			$handle = opendir(OPANDA_SR_PLUGIN_DIR . "/assets/img/patterns/$groupKey/");
			if( $handle ) {
				while( false !== ($entry = readdir($handle)) ) {
					if( $entry != "." && $entry != ".." ) {
						$folders[] = $entry;
					}
				}
				closedir($handle);
			}
			sort($folders);

			foreach($folders as $entry) {

				$ext = 'jpg';
				if( !file_exists(OPANDA_SR_PLUGIN_DIR . "/assets/img/patterns/$groupKey/$entry/$entry.jpg") ) {
					$ext = 'png';
				}

				$pattern = OPANDA_SR_PLUGIN_URL . "/assets/img/patterns/$groupKey/$entry/$entry.$ext";
				$pattern2X = OPANDA_SR_PLUGIN_URL . "/assets/img/patterns/$groupKey/$entry/$entry" . "_@2X.$ext";

				if( !file_exists(OPANDA_SR_PLUGIN_DIR . "/assets/img/patterns/$groupKey/$entry/$entry.$ext") ) {
					continue;
				}
				if( !file_exists(OPANDA_SR_PLUGIN_DIR . "/assets/img/patterns/$groupKey/$entry/$entry" . "_@2X.$ext") ) {
					$pattern2X = false;
				}

				$groupPatterns[] = array(
					'preview' => $pattern,
					'pattern' => $pattern,
					'pattern@2x' => $pattern2X
				);
			}

			$patterns[$groupKey] = array(
				'title' => $groupOptions,
				'patterns' => $groupPatterns
			);
		}

		$_opanda_sr_style_set_patterns_cache = $patterns;

		return $_opanda_sr_style_set_patterns_cache;
	}

	add_filter('factory_forms_000_patterns', 'opanda_sr_style_set_patterns');

