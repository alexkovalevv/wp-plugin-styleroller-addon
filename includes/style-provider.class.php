<?php
/**
 * Style Provider
 * 
 * @author Alex Kovalev <alex@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2013-2014, OnePress Ltd
 *  
 * @since 1.0.0
 */
class OnpStrl_StyleProvider implements IFactoryForms000_ValueProvider
{    
    private $data = array();
    private $loaded = false;
    
    public function __construct( $options = array() ) {  
       $this->scheme_id = isset($options['themeId']) ? $options['themeId'] : null; 
       $this->profile_id = isset($options['styleId']) ? $options['styleId'] : null;  
    }
    
    public function init() {
       return true;
    }
    
    /**
     * Gets a single value.
     * 
     * @since 1.0.0
     * @return mixed      
    */
    public function getValue( $name, $default = null, $multiple = false ) {   
        if ( !$this->loaded ) $this->loadData(); 
        return isset( $this->data[$name] ) ? $this->data[$name] : $default;
    }
    
    /**
     * Gets allo values.
     * 
     * @since 1.0.0
     * @return mixed[]      
    */
    public function getValues() {   
        if ( !$this->loaded ) $this->loadData(); 
        return $this->data;
    }
    
    /**
     * Loads the style options from database.
     * 
     * @since 1.0.0
     * @return void
     */
    private function loadData() {
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php';  

        $this->loaded = true;
        if( !$this->scheme_id ) return;
        $this->data = OnpSL_StyleManager::getStyle( $this->scheme_id, $this->profile_id );     
    }
    
    /**
     * Sets a single value for a given name.
     * 
     * @since 1.0.0
     * @return void      
    */
    public function setValue( $name, $value ) {
        $this->data[$name] = $value;       
    }
    
    /**
     * Saves all values.
     * 
     * @since 1.0.0
     * @return void      
    */
    public function saveChanges() { 
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php'; 

        return OnpSL_StyleManager::updateStyle($this->scheme_id, $this->data, $this->profile_id);   
    }
}
?>