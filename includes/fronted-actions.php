<?php
	/**
	 * Contains actions for jooks in the admin part.
	 *
	 * @author Alex Kovalev <alex@byonepress.com>
	 * @author Paul Kashtanoff <paul@byonepress.com>
	 *
	 * @since 1.0.0
	 * @package styleroller
	 */

	/**
	 * Adds a style id as a css class to the locker options.
	 *
	 * @see opanda_sr_locker_options
	 *
	 * @since 1.0.0
	 * @return mixed[]
	 */
	function opanda_sr_styler_add_css_class($params, $post_id)
	{

		$theme = get_post_meta($post_id, 'opanda_style', true);
		$style = get_post_meta($post_id, 'opanda_style_profile', true);

		$params['cssClass'] = 'p' . $style;

		// adding required fonts to load

		if( !empty($style) ) {
			require_once OPANDA_SR_PLUGIN_DIR . '/includes/style-manager.class.php';

			$styleData = OnpSL_StyleManager::getStyle($theme, $style);

			if( isset($styleData['style_fonts']) && !empty($styleData['style_fonts']) ) {
				$theme = $params['theme'];
				if( !is_array($theme) ) {
					$params['theme'] = array(
						'name' => $theme,
						'fonts' => json_decode($styleData['style_fonts'])
					);
				} else {
					$theme['fonts'] = json_decode($styleData['style_fonts']);
				}
			}
		}

		return $params;
	}

	add_filter('bizpanda_item_options', 'opanda_sr_styler_add_css_class', 10, 2);

	/**
	 * Prints styles for all lockers.
	 *
	 * @see opanda_sr_dynamic_themes_print_scripts
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_styler_print_all_locker_styles()
	{
		require_once OPANDA_SR_PLUGIN_DIR . '/includes/style-manager.class.php';

		$posts = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'social-locker',
			'post_status' => 'publish'
		));

		$css = '';
		$added = array();

		foreach($posts as $post) {

			$themeId = get_post_meta($post->ID, 'opanda_style', true);
			$styleId = get_post_meta($post->ID, 'opanda_style_profile', true);
			if( empty($themeId) || empty($styleId) ) {
				continue;
			}

			if( isset($added[$themeId][$styleId]) ) {
				continue;
			}
			$added[$themeId][$styleId] = true;

			$style = OnpSL_StyleManager::getStyle($themeId, $styleId);
			if( isset ($style['style_cache']) ) {
				$css .= $style['style_cache'];
			}
		}

		if( empty($css) ) {
			return;
		}
		?>
		<style><?php echo $css; ?></style><?php
	}

	add_action('opanda_print_dynamic_theme_options', 'opanda_sr_styler_print_all_locker_styles');

	/**
	 * Prints all style for a given locker.
	 *
	 * @see onp_front_preview_print_scripts
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_styler_print_locker_styles($lockerId, $lockerData)
	{

		if( !isset($lockerData['_theme']) || !isset($lockerData['_style']) ) {
			return;
		}
		opanda_sr_print_locker_styles_for($lockerData['_theme'], $lockerData['_style']);
	}

	add_action('bizpanda_print_locker_assets', 'opanda_sr_styler_print_locker_styles', 10, 2);
	add_action('opanda_print_batch_locker_assets', 'opanda_sr_styler_print_locker_styles', 10, 2);

	/**
	 * A cache for the function 'opanda_sr_print_locker_styles_for'.
	 *
	 * @see opanda_sr_print_locker_styles_for
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	global $opanda_sr_print_locker_styles_for_cache;
	$opanda_sr_print_locker_styles_for_cache = array();

	/**
	 * Prints once CSS for a given style.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_sr_print_locker_styles_for($themeId, $styleId)
	{

		global $opanda_sr_print_locker_styles_for_cache;
		if( isset($opanda_sr_print_locker_styles_for_cache[$themeId][$styleId]) ) {
			return;
		}
		$opanda_sr_print_locker_styles_for_cache[$themeId][$styleId] = true;

		require_once OPANDA_SR_PLUGIN_DIR . '/includes/style-manager.class.php';

		$style = OnpSL_StyleManager::getStyle($themeId, $styleId);
		$css = isset ($style['style_cache'])
			? $style['style_cache']
			: null;

		if( empty($css) ) {
			return;
		}
		?>
		<style><?php echo $css; ?></style><?php
	}

