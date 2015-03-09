<?php

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Mail download links to interested parties for 'free' downloads.
 ************************************************************************ */
                       

class PD_Mailer extends Controller {

    // only this can be accessed directly
    static $allowed_actions = array(
       'index',
    );
                                 
    function index(SS_HTTPRequest $request){
        $email = $request->postVar('email');
        $item_id = $request->postVar('item');

        if ($item = DataObject::get_by_id('PD_Item',$item_id)){
            if ($item->Protection != 'eMail'){
                throw new SS_HTTPResponse_Exception("Item has not eMail protection",405);
            }           
            $item->mailLink($email);
            return $this->renderWith(array('PD_MailerConfirmation','Page'));
        }
        throw new SS_HTTPResponse_Exception("Item not found",404);
    }
    
    function email(){        
        return $this->request->postVar('email');
    }       
}


