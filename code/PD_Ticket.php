<?php    

class PD_Ticket extends DataObject implements PermissionProvider {

    static $api_access = true;
    
    public static $db = array(
        "Generated"   	=> "Varchar(255)",
	"FirstName"  	=> "Varchar(255)",
	"LastName"  	=> "Varchar(255)",
	"Affiliation"  	=> "Varchar(255)",
	"eMail"         => "Varchar(255)",
        "ValidUntil"    => "Varchar(255)",
        "Downloads"     => "Int",
        "TicketKey"		=> "Varchar(255)",
    );

    public static $has_one = array(
        "Item"          => "PD_Item"
    );

    public static $indexes = array(
        'TicketKey' => true,
    );
                    
    static $summary_fields = array(
         'Generated','eMail','FirstName','LastName','Affiliation','ValidUntil','Downloads','AssetName'
    );  
             
    static $searchable_fields = array(
         'eMail','FirstName','LastName','Affiliation'
     );

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->replaceField('TicketKey',new ReadOnlyField('TicketKey'));
        return $fields;
    }

                             
    public function providePermissions() {
         return array(
            'PD_TICKET_VIEW' => 'View ProtectedDownload Tickets',
            'PD_TICKET_DELETE' => 'Delete ProtectedDownload Tickets',
        );
    }

    public function canView() {
	return Permission::check('PD_TICKET_VIEW');
    }

    public function canDelete() {
	return Permission::check('PD_TICKET_DELETE');
    }

    public function canCreate() {
	return false;
    }

    public function canEdit() {
	return false;
    }

    function getAssetName(){
        return preg_replace('{\Qassets/'.$this->folder.'/}','',$this->Item()->Asset()->Filename);
    }

    protected function log($action){
        $item = $this->Item();
        $log = new PD_Log;
        $log->Timestamp = (new DateTime)->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s');
        $log->eMail = $this->eMail . ( $item->DirectMail ? ' via Admin' : '');
        $log->ItemID = $item->ID;
        $log->Asset = $item->Asset()->getFullPath();
        $log->Action = $action;
        $log->write();
    }

    function logDownload(){
        $this->log('Download');
        $this->Downloads = $this->Downloads+1;
        $this->write;
    }

    function logSendKey(){
        $this->log('SendKey');
    }

    public function mailLink(){
        $cfg = SiteConfig::current_site_config();
        $this->logSendKey();
        $bcc = '';
        if($cfg->PD_codeCopies){
            $bcc = "\nBcc: ".$cfg->$cfg->PD_fromAddress;
        }
	$to = $cfg->PD_adminEmail;
	if($this->Item()->DirectMail){
	    $to = $this->eMail;
	}	    
        return mail($to, 
                    'Download Link for '.$this->Description.' ('.$this->eMail.')', 
                    "You can access your download on\n\n".Director::absoluteBaseURL('')."PD_Download/".$this->TicketKey,
                    'From: '.$cfg->PD_fromAddress.$bcc);
    }            
}
