<?php
/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Handle incoming PayPal InstantPaymentNotification Messages

 ************************************************************************ */

class PD_IpnReceiver extends Controller {

    function index(SS_HTTPRequest $req){
        // if it is not a postrequest, do not bother
        if (! $req->isPOST()) return;

        $cfg = SiteConfig::current_site_config();

	$listener = new PD_IpnListener();
        $listener->use_sandbox = $cfg->PD_PPsandbox ? true : false;
	$listener->use_curl = true;
	
        try {
            $verified = $listener->processIpn();
        } catch (Exception $e){
            user_error("PayPal Error: ".$e->getMessage(), E_USER_WARNING);
        }
        if ($verified && ( $req->postVar('receiver_id') == $cfg->PD_PPmerchantId || $req->postVar('receiver_email') == $cfg->PD_PPmerchantId) ){
            $this->onValid($listener->getTextReport());
        }
        else {
            $this->onInvalid($listener->getTextReport());
        }
    }

    private function onInvalid($report){
        $cfg = SiteConfig::current_site_config();
        mail($cfg->PD_adminEmail, '[IPN] Invalid', $report,'From: '.$cfg->PD_fromAddress);
    }
    
    private function onValid($report){       
        $cfg = SiteConfig::current_site_config();
        $req = $this->request;
        $vars = $req->postVars();          
        $msg = new PD_IpnMsg();
        foreach ($vars as $key => $value) {
            if ($key == 'payment_date'){
                $value = DateTime::createFromFormat('H:i:s M j, Y T',$value)->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s');
            }
            try {                
                $msg->setField($key,$value);
            } catch (Exception $ex){
       	        user_error("Store Problem: ".$ex->getMessage(), E_USER_WARNING);
            };
            
        }
        $msg->setField('ipn_json',json_encode($vars));
        $msg->set_validation_enabled(false);
        try {
            $msg->write(false,true);
        } catch (ValidationException $e) {
            user_error("PayPal Store Problem: ".$e->getMessage(), E_USER_WARNING);
        }
        if ($cfg->PD_PPipnCopies){
            mail($cfg->PD_adminEmail, '[IPN] Verified for '.$vars['receiver_email'].' - Status '.$vars['payment_status'].' ('.$vars['txn_type'].')', $report,'From: '.$cfg->PD_fromAddress);
        }
        if ($req->postVar('payment_status') == 'Completed'){
            if ($item = DataObject::get_by_id('PD_Item',$req->postVar('item_number')+0)){
                $args = array(
                    'email' => $req->postVar('payer_email'),
                    'first' => $req->postVar('first_name'),
                    'last' => $req->postVar('last_name'),
                    'affiliation' => $req->postVar('custom')                
                );
/*                if ($custom ){
                    if ( $jsonArray = json_decode($req->postVar('custom'),true) ){
                        if (json_last_error() === JSON_ERROR_NONE){
                             $args = $jsonArray;
                        }
                        else {
                            user_error("Store Problem: ".json_last_error(), E_USER_WARNING);
                        }                            
                    }                    
                }
                $args['email'] = $req->postVar('payer_email');
                $args['first'] = $req->postVar('first_name');
                $args['last'] = $req->postVar('last_name');
*/
                try {
                    $ticket = $item->makeTicket($args);
                    $ticket->mailLink();
                } catch (Exception $ex){
       	            user_error("ticket mail problem: ".$ex->getMessage(), E_USER_WARNING);
                };                    
            }
            else {
                user_error("PayPal Error Item not found: ".$req->postVar('item_number'), E_USER_WARNING);
            }
        }
        else {
            user_error("PayPal Payment Status not Completed: ".$req->postVar('payment_status'), E_USER_WARNING);        
        }
    }
}
