<?php
/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Add the [pd_button]X[/pd_button] short code.

   Enable in _config.php with
   ShortcodeParser::get()->register('pd_button', array('PD_Button', 'ShortCodeHandler'));
 ************************************************************************ */

class PD_Button {
    public static function ShortCodeHandler($arguments,$content = null, $parser = null, $tagName) {
        $cfg = SiteConfig::current_site_config();
        $item_id = $content+0;
        if ($item = DataObject::get_by_id('PD_Item',$item_id)){
            switch ($item->Protection){
                case 'PayPal':
                    $button = array(
                        'button' => 'buynow',
                        'currency' => $cfg->PD_PPcurrency,
                        // this will  change in the second edition of the plugin!
                        'env' => $cfg->PD_PPsandbox ? 'sandbox':'www',
                        'callback' => Director::absoluteBaseURL('').'PD_IpnReceiver',
                        'no_shipping' => 2,
                        'no_note' => 1,
                        'rm' => 1,
                        'return' => Director::absoluteUrl(''),
                        'amount' => $item->Price,
                        'item_name' => $item->Description,
                        'item_number' => $item->ID
                    );
                    $merchantId = $cfg->PD_PPmerchantId;

                    $return = '<script async src="mysite/javascript/paypal-button.min.js?merchant='.$merchantId.'"';
                    foreach ($button as $key => $value){
                        $return .= "\n".'  data-'.$key.'="'.$value.'"';
                    }        
                    $return .= "\n  async>\n</script>";
                    return $return;
                    break;
                case 'eMail':
                    $return .= '<form method="post" '
			. 'action="'.Director::absoluteBaseURL('').'PD_Mailer" class="download-button" target="_top">'
			. '<label for="download_button_'.$item_id.'">eMail:</label>'
			. '<input type="text" name="email" id="download_button_'.$item_id.'">'
			. '<input type="hidden" name="item" value="'.$item->ID.'">'
			. '<input type="hidden" name="return" value="'.Director::absoluteUrl('').'">'
			. '<button type="submit" class="button">Send Download Link</button></form>';
		    return $return;
                    break;
            }
            return "No Button for ".$item->Protection." Items";
        }
	return "Item ".$item_id." is unknown";              
    }
}


