<?php
/**
 * Style Manager
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd

 * @package styleroller
 * @since 1.0.0
 */
class OnpSL_StyleManager { 
    
    public static function getStyles( $themeId ) {
        
        $default = array();
        $default['default'] = array( 'profile_title' => __('Default', 'styleroller') );
        
        $styles = get_option( "opanda_sr_styles_$themeId", array() );   
        
        return array_merge($default, $styles);
    }
    
    public static function getStylesTitles( $themeId ) {
        $styles = self::getStyles( $themeId );
        
        $result = array();
        foreach($styles as $styleId => $styleOptions) {
            $result[$styleId] = $styleOptions['profile_title'];
        } 
        
        return $result;
    }
    
    public static function getStyle( $themeId, $styleId ) {
        
        $styles = self::getStyles( $themeId );
        if ( !isset( $styles[$styleId] )) return array();
        
        $style = $styles[$styleId];
        
        $style['theme_id'] = $themeId;
        $style['style_id'] = $styleId;
        
        return $style;
    }
    
    public static function updateStyle( $themeId, $options, $styleId = false ) {
        
        $styles = self::getStyles( $themeId );
        if ( empty( $styleId ) ) $styleId = uniqid();
        
        $options['theme_id'] = $themeId;
        $options['style_id'] = $styleId;

        if ( empty( $options['profile_title'] ) ) $options['profile_title'] = __('New Style', 'opanda_styler');
        $styles[$styleId] = $options;
        
        update_option( "opanda_sr_styles_$themeId", $styles );   
        return $styleId;
    }
    
    public static function removeStyle( $themeId, $styleId, $replaceWith = false ) {
        global $wpdb;
        
        if ( !$replaceWith ) {

            $count = $wpdb->get_var("
                SELECT COUNT(*) AS usedCount FROM {$wpdb->postmeta}
                WHERE meta_key = 'opanda_style_profile' and meta_value = '" . $styleId . "'"
            );               
                
            if ( $count > 0 ) return false;
        }
        
        if ( $replaceWith ) {
            
            $wpdb->update( 'wp_postmeta',
                  array( 'meta_value' => $replaceWith ),
                  array( 'meta_key' => 'opanda_style_profile', 'meta_value' => $styleId ),
                  array( '%s' ),
                  array( '%s', '%s' )
            );        
        }

        $styles = self::getStyles( $themeId );
        unset($styles[$styleId]);
        
        update_option( "opanda_sr_styles_$themeId", $styles ); 
        return true;
    }
    
    /**
     * Returns the list of used fonts.
     * @since 1.1.1 
     */
    public static function getGoogleFonts( $themeId, $styleId, $styleData = null ) {
        require_once OPANDA_BIZPANDA_DIR . '/includes/classes/themes-manager.class.php';
        
        $editableOptions = OPanda_ThemeManager::getEditableOptions( $themeId );
        if ( false === $editableOptions ) return array();
        
        $fontOptions = self::getEditableOptionsForGoogleFonts( $editableOptions );
        
        if ( empty( $fontOptions ) ) return false;
        
        if ( empty( $styleData ) ) $styleData = self::getStyle( $themeId, $styleId );
        $fonts = array();

        foreach ( $fontOptions as $fontItem ) {
            $name = $fontItem['name'] . '__google_font_data';
            if ( isset( $styleData[$name] ) && !empty( $styleData[$name] )) {
                $fonts[] = json_decode(urldecode(base64_decode($styleData[$name])));
            }
        }
        
        return $fonts;
    }
    
    /**
     * Extracts only editable options for google fonts.
     * @since 1.1.1
     */
    protected static function getEditableOptionsForGoogleFonts( $editableOptions ) {
        
        $fontOptions = array();
        
        foreach( $editableOptions as $item ) {
            
            if ( !isset( $item['type'] ) && count( $item ) == 3 ) {
                $itemFontOptions = self::getEditableOptionsForGoogleFonts( $item[2] );
                $fontOptions = array_merge( $fontOptions, $itemFontOptions );
                continue;
            }
            
            if ( isset( $item['type'] ) && $item['type'] === 'google-font' ) {
                $fontOptions[] = $item;
                continue;
            }
            
            if ( isset( $item['items'] ) ) {
                $itemFontOptions = self::getEditableOptionsForGoogleFonts( $item['items'] );
                $fontOptions = array_merge( $fontOptions, $itemFontOptions );
                continue;
            }
        } 
        
        return $fontOptions;
    }
    
    
}