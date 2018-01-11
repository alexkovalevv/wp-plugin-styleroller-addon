<?php
/**
 * Theme & Style Selector for Forms Factory 
 * 
 * @author Alex Kovalev <paul@byonepress.com>
 * @author Paul Kashtanoff <paul@byonepress.com>
 * @copyright (c) 2014, OnePress Ltd

 * @package styleroller
 * @since 1.0.0
 */
class OnpSL_Styler_StyleSelector extends FactoryForms000_Control 
{
    public $type = 'opanda_sr_styler_style_selector';
    
     public function getName() {
        return array(
            $this->options['name'],
            $this->options['name'] . '_profile',
        );
    }
    
    /**
     * Returns a set of available items for the list.
     * 
     * @since 1.0.0
     * @return mixed[]
     */
    private function getItemsStyle() {
        $data = $this->getOption('data', array());
        
        // if the data options is a valid callback for an object method
        if (
            is_array($data) && 
            count($data) == 2 && 
            gettype($data[0]) == 'object' ) {
            
            return call_user_func($data);
        
        // if the data options is a valid callback for a function
        } elseif ( gettype($data) == 'string' ) {  
            
            return $data();
        }
        
        // if the data options is an array of values
        return $data;
    }   
    
   
    /**
     * Shows the html markup of the control.
     * 
     * @since 1.0.0
     * @return void
     */
    public function html( ) {
        require_once OPANDA_SR_PLUGIN_DIR. '/includes/style-manager.class.php';  
            
        $name = $this->getNameOnForm();
        $items = $this->getItemsStyle();
        $value = $this->getValue();

        $get_profiles = OnpSL_StyleManager::getStyles( $value[0] );
        if( !isset( $get_profiles[$value[1]] ) ) $value[1] = null;
                   
        $googleFonts = array();        
        if( empty($value[1]) ) $value[1] = 'default';
       
        ?>
        <select class="form-control profile-dropdown" id="<?php echo $name[0]; ?>" name="<?php echo $name[0]; ?>"/>
        <?php foreach($items as $item) {
            $selected = ( $item['value'] == $value[0] ) ? 'selected="selected"' : '';
            ?>
            <option value="<?php echo $item['value'] ?>" <?php echo $selected ?>>
                <?php echo $item['title'] ?>
            </option>
        <?php } ?>
        </select>
        <select class="form-control profile-dropdown" id="<?php echo $name[1]; ?>" name="<?php echo $name[1]; ?>"/>
        <?php foreach($get_profiles as $key => $p_item) {            
 
            $selected = '';
            if ( ( $key == $value[1] ) ) {
                 $selected = 'selected="selected"';
                 $googleFonts = OnpSL_StyleManager::getGoogleFonts( $value[0], $key );
            }
            
            ?>
            <option value="<?php echo $key; ?>" <?php echo $selected ?>>
                <?php echo $p_item['profile_title']; ?>
            </option>
        <?php } ?>
        </select>         
        <button <?php if( empty($value[1]) || $value[1] === 'default' ): ?>style="display:none;" <?php endif; ?>class="onpSL-profile-edit-button"></button>
        <button class="onpSL-profile-add-button"></button>
        <div class="clearfix"></div>
        <?php
        
        if ( !empty( $googleFonts ) ) {
            ?>
            <script>
                if ( !window.bizpanda ) window.bizpanda = {};
                window.bizpanda.previewGoogleFonts = <?php echo json_encode( $googleFonts ); ?>;
            </script>
            <?php
        }
    }
}
