<?php
/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   This Class creates the 'Protected Download' 
   section in the SilverStripe CMS.
   
 ************************************************************************ */

 
class PD_Admin extends ModelAdmin {
    
   public static $managed_models = array(
      'PD_Item' => array('title' => 'Items'),
      'PD_License' => array('title' => 'Licenses'),
      'PD_IpnMsg' => array('title' => 'Payments'),
      'PD_Ticket' => array('title' => 'Tickets'),
      'PD_Log' => array('title' => 'Log Entries'),
   );
 
   static $url_segment = 'pd_adm'; // will be linked as /admin/poicategory
   static $menu_title = 'Protected Download';
   public $showImportForm = false;
}
