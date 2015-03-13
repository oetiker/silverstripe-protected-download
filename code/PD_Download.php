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
    static $allowed_actions = array();

    static $url_handlers = array(
       '$TicketKey!' => 'index'
    );
               
               
    protected $licenseText;
                                     
    function index(SS_HTTPRequest $req){
        $cfg = SiteConfig::current_site_config();
        # Debug::dump($req);
        $key = $req->allParams()['TicketKey'];
        $ticket = DataObject::get_one('PD_Ticket',"TicketKey = '".Convert::raw2sql($key)."'");
        # user_error("Got Request for $key", E_USER_WARNING);
         
        if (!$ticket) {
             $error = ErrorPage::response_for(404);
             throw new SS_HTTPResponse_Exception($error);
        }

        $item = $ticket->Item();

        if ($item->ValidDays && $ticket->ValidUntil < (new DateTime)->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s')){
             throw new SS_HTTPResponse_Exception("Ticket has Expired",403);
        }        

        $maxDownloads = $item->MaxDownloads;
        if ($maxDownloads > 0 && $ticket->Downloads > $maxDownloads){
             throw new SS_HTTPResponse_Exception("You may only download this $maxDownloads",403);
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
                $ticket->logDownload();
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
