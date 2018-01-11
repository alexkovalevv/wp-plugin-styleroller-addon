<?php

/**
 * License page is a place where a user can check updated and manage the license.
 */
class OnpSL_Style_LicenseManagerPage extends OnpLicensing000_LicenseManagerPage  {
 
    public $purchasePrice = '$15';
    public $internal = true;
    public $trial = false;
    public $codecanyon = false;
    public $faq = false;
    public $premium = false;
    
    public function configure() {
        $this->menuTitle = 'StyleRoller';
        $this->menuIcon = '';
    }
}

global $styleroller;
FactoryPages000::register($styleroller, 'OnpSL_Style_LicenseManagerPage');