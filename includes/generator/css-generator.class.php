<?php
/**
 * A class to generate CSS by following given rules
 * 
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @author Alex Kovalev <alex@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd
 * 
 * @package styleroller
 * @since 1.0.0
 */

require_once OPANDA_SR_PLUGIN_DIR. '/includes/generator/profile-color.class.php';
require_once OPANDA_SR_PLUGIN_DIR. '/includes/generator/converting.functions.php';

class OnpSL_CSSGenerator { 

    /**
     * A set of work data.
     * 
     * Usually it contains data from $_POST received after submission of a form.
     * 
     * @since 1.0.0
     * @var mixed[] 
     */
    protected $data;

    /**
     * The array of rules to generate CSS.
     * 
     * @see OPanda_ThemeManager::getRulesToGenerateCSS
     * 
     * @since 1.0.0
     * @var mixed[] 
     */
    protected $rules = array();

    /**
     * Inits a new instance of OnpSL_CSSGenerator.
     * 
     * @since 1.0.0
     * @param string $themeId A theme name for which we're going to generate CSS.
     * @param mixed[] $data A set of work data. Usually it's $_POST received after submission of a form.
     */
    public function __construct($themeId, $data) {
        $this->themeId = $themeId;
        $this->data = $data; 
    }

    /**
     * Generates, formats and returns formated CSS.
     * 
     * @since 1.0.0
     * @param string $styleId A style id for which we're generating CSS.
     * @return string Generated and formatted CSS.
     */
    public function getCSS( $styleId, $compress = false ) {
        return $this->formatCss($styleId, $this->generate(), $compress);
    }

    /**
     * Formats given CSS rules.
     * 
     * @since 1.0.0
     * @param string $styleId A style id for which we're formatting CSS.
     * @param string $printStyles A set of CSS rules.
     * @return string Formatted CSS.
     */
    public function formatCss($styleId, $printStyles, $compress = false) { 

        $print_str = '';              
        foreach( $printStyles as $selector => $attr ){

            // split ".class1, class2"
            $parts = explode(',', $selector);
            foreach( $parts as $partIndex => $partSelector ) {
                $parts[$partIndex] = '.p'.$styleId.trim($partSelector);
            }
            $selector = implode(', ', $parts);

            // formats css
            $attr = array_diff($attr, array(''));
            $print_str .= $selector . ( $compress ? '{' : " {\n" );        
            foreach( $attr as $attr_val ) {
                if( !is_array($attr_val) ) {
                    $print_str .= ( $compress ? $attr_val : "\t".$attr_val."\n"); 
                } else {
                    foreach( $attr_val as $in_atr_val ) {                           
                         $print_str .= ( $compress ? $in_atr_val : "\t".$in_atr_val."\n");         
                    }
                }
            }  
            $print_str .=  ( $compress ? '}' : "}\n" );
       } 
       return $print_str;  
    }
          
    /**
     * Generates ready to use CSS styles.
     * 
     * @since 1.0.0
     * @return mixed[] an array of ready to use CSS rules
    */
    public function generate() {
        require_once OPANDA_BIZPANDA_DIR . '/includes/classes/themes-manager.class.php';
        
        $this->rules = OPanda_ThemeManager::getRulesToGenerateCSS( $this->themeId );    
        $this->themeOptions = OPanda_ThemeManager::getEditableOptions( $this->themeId );

        /**
          * Contains an array in the format:
          * ...
          * [control_name_1] => value,
          * [control_name_2] => array(
          *    [__sub_control_name_1] => value,
          *    [__sub_control_name_2] => value,
          * ),
          * [control_name_3] => value
          * ...
         */
        $css_group = $this->splitCssGroup();    

        /**
         * Contains  an array in the format:
         * ...
         * [.css-selector1] => array(
         *     'property1: value1;',
         *     'property2: value1;'
         * ),
         * ...
         */
        $print_styles = array();

        foreach( $css_group as $controlName => $controlValue ){
            if ( !isset( $this->rules[$controlName] ) ) continue;

            $rulesForControl = $this->rules[$controlName];
            if ( isset( $rulesForControl['selector'] ) ) $rulesForControl = array( $rulesForControl );

            foreach( $rulesForControl as $rule ) {
                $print_styles[$rule['selector']][] = $this->getRuleCSS( $rule, $controlValue );
            }
        }

        $print_styles = apply_filters('opanda_sr_theme_print_styles', $print_styles, $this->themeId, $this->data); 
        return $print_styles;
    }

    /**
     * Groups data by control name and removes data for inactive controls.
     * 
     * @since 1.0.0
     * @return mixed[] See the function body to learn about the output format.
     */
    private function splitCssGroup() {

           // Contains an arraw in format:

           /**
            * Will contain an array in the format:
            * ...
            * [control_name_1] => value,
            * [control_name_2] => array(
            *    [__sub_control_name_1] => value,
            *    [__sub_control_name_2] => value,
            * ),
            * [control_name_3] => value
            * ...
            */
           $contolValues = array();

           foreach( $this->rules as $controlName => $attr ){                  
              foreach( $this->data as $formDataName => $value ) {                        
                  if( preg_match("#^".$controlName."(__[A-z0-9]+)#", $formDataName, $math) && $this->data[$controlName."_is_active"] == 1 ) {                           
                      $contolValues[$controlName][trim( $math[1], '_' )] = $value;   
                  }                   
              } 

              if( !isset($contolValues[$controlName]) && 
                   isset( $this->data[$controlName."_is_active"] ) && 
                   $this->data[$controlName."_is_active"] == 1  )

                      $contolValues[$controlName] = $this->data[$controlName];  
           }  

           foreach($contolValues as $contolName => $values) {
               if( $values == '' ) $values = 0;
               $controlOptions = null;

               foreach($this->themeOptions as $sliderOptions ) {                        
                  $controlOptions = FactoryForms000_FormHelpers::extractControlOptions( $contolName, $sliderOptions[2] );
                  if(!empty($controlOptions)) break;                       
               }

               if ( $controlOptions ) {     
                   if( isset( $controlOptions['default'] ) ) {

                       if( is_array( $controlOptions['default'] ) ) { 
                           
                           $same = true;

                           foreach ( $controlOptions['default'] as $defaultValueKey => $defaultValue ) {
      
                               if ( !isset( $values[$defaultValueKey] ) 
                                    || ( (string)$defaultValue !== stripcslashes( trim( $values[$defaultValueKey] ) ) ) ) {

                                   $same = false;
                                   break;
                               }
                           }
             
                           if ( $same ) unset( $contolValues[$contolName] );
 
                       } else {

                            if( trim( $controlOptions['default']) == stripcslashes( trim( $values ) ) ) {
                                unset($contolValues[$contolName]);
                            }
                       }
                   }                     
               }
           }

           return $contolValues;           
    } 
                   
    /**
     * Generates CSS for a given rule.
     * 
     * @since 1.0.0   
     * @return mixed
     */
    private function getRuleCSS( $rule, $controlValue ) {  

       // if a rule has several CSS properties, then merge them into one
       $cssProperty = $rule['css'];
       if ( is_array( $rule['css'] ) ) $cssProperty = implode(' ', $cssProperty);

       $replacements = array();

       if ( preg_match_all('/\{([a-z0-9_-]+)(\|([a-z0-9_-]+))?\}/i', $cssProperty, $matches, PREG_SET_ORDER ) ) {
   
           foreach($matches as $match) {

               // if we have already processed a current term, skip it
               $term = $match[0];
               if ( isset($replacements[$term]) ) continue;

               $varKey = $match[1];
               if ( $varKey === 'value' ) {
                   $varValue = $controlValue;
               } elseif ( is_array( $controlValue ) && isset( $controlValue[$varKey] ) ) {
                   $varValue = $controlValue[$varKey];
               } elseif ( isset( $rule[$varKey] ) ) {
                   $varValue = $this->processVariable( $rule[$varKey], $controlValue );
               } else {
                   $varValue = null;
               }

               // filters found, {value|filter1|filter2}
              if ( !empty( $match[3] ) ) { 

                   // applying filters
                   for( $i = 3; $i < count( $match ); $i++ ) {
                       $replacements[$term] = call_user_func( $match[$i], $varValue );
                   }

              } else {
                  $replacements[$term] = $varValue;
              }
           }
       }

       // doing replacement
       foreach( $replacements as $term => $value ) {
           $cssProperty = str_replace($term, $value, $cssProperty);
       }

       return $cssProperty;
    }
    
    /**
     * Returns a result value for a given variable rule.
     * 
     * @since 1.0.0   
     * @return string A result value.
     */
    private function processVariable( $variableRule, $controlValue ) {

        if ( isset( $variableRule['function'] ) ) {
            foreach ( $variableRule['args'] as $index => $arg ) {
                if (is_array( $arg ) ) continue;
                
                if ( substr($arg, 0, 1) == '{' && substr($arg, -1) == '}' ) {
                    
                    $innerVarKey = trim( $arg, '{}' );
                    if ( $innerVarKey === 'value' ) {
                        $varValue = $controlValue;
                    } elseif ( is_array( $controlValue ) && isset( $controlValue[$innerVarKey] ) ) {
                        $varValue = $controlValue[$innerVarKey];
                    } else {
                        $varValue = null;
                    }
                    
                    $variableRule['args'][$index] = $varValue; 
                }
            }
            return call_user_func_array( $variableRule['function'], $variableRule['args'] );
        }
        
        return null;
    }
}