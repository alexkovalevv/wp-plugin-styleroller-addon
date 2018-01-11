<?php


class StylRollerUpdate010010 extends Factory000_Update {

    public function install() {
        
        if( !defined('OPTINPANDA_PLUGIN_ACTIVE') && !defined('SOCIALLOCKER_PLUGIN_ACTIVE') ) return false;
        if( !defined('OPANDA_ACTIVE') ) return false;  
    
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->options} SET option_name = REPLACE(option_name, 'onp_sl_styles_', 'opanda_sr_styles_') WHERE option_name LIKE 'onp_sl_styles_%'");
		
        require_once(OPANDA_BIZPANDA_DIR. '/includes/classes/themes-manager.class.php'); 
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/generator/css-generator.class.php';
        require_once(OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php');  
        
        $themes = OPanda_ThemeManager::getThemes();
        foreach ( $themes as $themeId => $themeData ) {
            
            $styles = OnpSL_StyleManager::getStyles( $themeId  );
            $dataToSave = array();

            foreach( $styles as $styleId => $stylesData ) {
			
                if ( in_array( $styleId, array( 'default-secrets', 'default-starter', 'default-flat', 'default-dandyish', 'default-glass' )) ) continue;
                if ( empty( $stylesData ) || empty( $stylesData['style_cache'] ) )  continue;
     
                $startCssGenerator = new OnpSL_CSSGenerator($themeId, $stylesData);
                $stylesData['style_cache'] = $startCssGenerator->getCSS( $styleId );
                $dataToSave[$styleId] = $stylesData;
            }
            
            update_option('opanda_sr_styles_' . $themeId, $dataToSave);
        }
    }
}