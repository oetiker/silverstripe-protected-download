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

    static $formId = 0;

    public static function ShortCodeHandler($arguments,$content = null, $parser = null, $tagName) {
        $formId = PD_Button::$formId++;        
        $cfg = SiteConfig::current_site_config();
        $item_id = $content+0;
        Requirements::javascript("mysite/javascript/jquery-1.7.1.min.js");
        if ($item = DataObject::get_by_id('PD_Item',$item_id)){
            $action = '';
	    $submit = '';
	    $hiddencfg = array();
	    $textcfg = array();	    	    
            switch ($item->Protection){
                case 'PayPal':
                    $action = $cfg->PD_PPsandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
                    $hiddencfg = array(
                        'cmd' => '_xclick',
                        'currency_code' => $cfg->PD_PPcurrency,
                        'business' => $cfg->PD_PPmerchantId,
                        'charset' => 'UTF-8',
                        'return' =>  Director::absoluteUrl(''),
                        'amount' => $item->Price,
                        'item_name' => $item->Description,
                        'item_number' => $item->ID,
                        'rm' => 1,
                        'no_shipping' => 2,
                        'no_note' => 1,
                        'notify_url' => Director::absoluteBaseURL('').'PD_IpnReceiver',
                    );
                    
                    $textcfg = array();
	            if ($item->RequireExtraData){
			$hiddencfg = array_merge($hiddencfg,array(
			    'address_override' => 1
                        ));
	                $textcfg = array(
                            'custom' => 'Affiliation',
                            'first_name' => 'First Name',
                            'last_name' => 'Last Name',
                            'email' => 'eMail',
                        );
                    }
                    $price = $item->Price.' '.$cfg->PD_PPcurrency;
                    $submitText = $price.' @ PayPal';
                    break;
                case 'eMail':
                    $action = Director::absoluteBaseURL('').'PD_Mailer';
                    $input  = '';
                    
                    $hiddencfg = array(
                        'item' => $item->ID,
                    );

                    $textcfg = array(
                        'email' => 'eMail',
		    );

		    if ($item->RequireExtraData){
			$textcfg = array_merge($textcfg,array(		    
        	            'affiliation' => 'Affiliation',
                	    'first' => 'First Name',
                            'last' => 'Last Name',
                        ));
                     }
                    
                    $submitText = "Get Download Link";
                    break;
                case 'Code':
                    $action = Director::absoluteBaseURL('').'PD_Mailer';
                    $input  = '';
                    
                    $hiddencfg = array(
                        'item' => $item->ID,
                    );

                    $textcfg = array(
                        'code' => 'Authorization Code',
		    );
                   
                    $submitText = "Get Download Link";
                    break;
		default:
		    return "Invalid Protection for button $item_id";		
            }
            $input = '<fieldset>';
            foreach ($hiddencfg as $key => $value){
                $input .= '<input id="PD_'.$key.'_'.$formId.'" type="hidden" name="'.$key.'" value="'.$value.'">'."\n";
            }
            $script = 'jQuery("#PD_Form_'.$formId.'").submit(function(e){ var $ok = true; var $item;'."\n";
            foreach ($textcfg as $key => $value){
                $id = 'PD_'.$key.'_'.$formId;
                $input .= '<div class="field text"><label class="left" for='.$id.'">'.$value.'*</label>'."\n"
                . '<div class="middleColumn"><input class="text" type="text" id="'.$id.'" name="'.$key.'" value="" /></div></div>'."\n";
                $script .= '    $item = jQuery("#'.$id.'");'."\n";
                $script .= '    if ($item.val() == ""){ $item.css({borderStyle: "solid", borderColor: "red", borderWidth: "1px"}); $ok = false; }'."\n";
            }
            $script .= 'if (!$ok) { e.preventDefault(); }';
            $script .= '});';
            Requirements::customScript($script);
	    $input .= '</fieldset>';
            $input .= '<div class="Actions"><input class="action" type="submit" name="submit" value="'.$submitText.'" title="'.$submitText.'" /></div>';
   	    return '<form id="PD_Form_'.$formId.'" method="post" action="'.$action.'" target="_top">'.$input.'</form>';	
        }
	return "Item ".$item_id." is unknown";              
    }
}


