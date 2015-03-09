<?php

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Provide Protected downloads under /PD_Download

 ************************************************************************ */


class PD_Download extends Controller {

    // only this can be accessed directly
    static $allowed_actions = array(
       'index',
    );

    protected $licenseText;
                                     
    function index(SS_HTTPRequest $req){
        $cfg = SiteConfig::current_site_config();

        $email = $req->requestVar('email');
        $item_id = $req->requestVar('item');
        $hash = $req->requestVar('hash');

        $item = DataObject::get_by_id('PD_Item',$item_id);

        if (!$item) {
             $error = ErrorPage::response_for(404);
             throw new SS_HTTPResponse_Exception($error);
         }
        

        if ( $item->makehash($email) != $hash ){
            user_error('bad hash for f:'.$file.' e:'.$email,E_USER_WARNING);
            $response = ErrorPage::response_for(404);
            throw new SS_HTTPResponse_Exception($response);
        }

        # redirect to license screen
        if ($item->LicenseID && $req->requestVar('license') != 'ok') {
            $this->licenseText = $item->License()->License;
            return $this->renderWith(array('PD_License','Page'));
        }

        $path = $item->Asset()->getFullPath();
        // check that file exists and is readable

        if (file_exists($path) && is_readable($path)) {
            // get the file size and send the http headers
            $size = filesize($path);
            $filename = preg_replace('{.*/}','',$path);
            // open the file in binary read-only mode
            // display the error messages if the file can�t be opened
            $file = @ fopen($path, 'rb');
            if ($file) {
                // stream the file and exit the script when complete
                header('Content-Type: application/octet-stream');
                header('Content-Length: '.$size);
                header('Content-Disposition: attachment; filename='.$filename);
                header('Content-Transfer-Encoding: binary');
                fpassthru($file);
                $item->logDownload($email);
                exit;
            } else {
                $response = ErrorPage::response_for(404);
                throw new SS_HTTPResponse_Exception($response);
            }
        } else {
             $response = ErrorPage::response_for(404);
             throw new SS_HTTPResponse_Exception($response);
        }
    }
    
    function getLicense(){
        return $this->licenseText;
    }
}
