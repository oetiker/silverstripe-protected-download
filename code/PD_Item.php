<?php

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   The Asset Storage Items. The items have a bunch of helper Methods.
 ************************************************************************ */
                       
class PD_Item extends DataObject implements PermissionProvider {

    static $api_access = true;
    
    protected $folder = 'ProtectedDownload';
    
    public static $db = array(
	"Protection"  	=> "Enum('eMail,PayPal,Secret','eMail')",
	"MaxDownloads"  => "Int",
	"ValidDays" => "Int",
	"RequireExtraData" => "Boolean",
	"DirectMail" => "Boolean",
	"Price"		=> "Decimal(15,2)",
        "Description" 	=> "Varchar(255)",
        "Secret"        => "Varchar(20)",
    );
    
    static $has_one = array(
        "License" => "PD_License",
        "Asset" => "File",
    );
    
//Fields to show in ModelAdmin table
    static $summary_fields = array(
	'ButtonCode','Protection','Secret','MaxDownloads','ValidDays','RequireExtraData','Description','AssetName','LicenseName'
    );  

    static $searchable_fields = array(
        'Description',
        'Secret',
        'AssetName'
    );
    
    static $field_labels = array(
	'ButtonCode' => 'Button',
	'Protection' => 'Protection Method',
	'MaxDownloads' => 'Ticket Valid for x Downloads',
	'ValidDays'  => 'Ticket Valid for x Days',
	'Secret'     => 'Secret',
	'DirectMail' => 'Send Ticket Directly to Requestor',
	'RequireExtraData' => 'Require Extra User Information (Name,Affilition)',
        'Description' => 'Description',
        'AssetID' => 'Asset',
        'AssetName' => 'Asset',
        'License' => 'License',
        'LicenseName' => 'License'
    );
      
    public function providePermissions() {
         return array(
            'PD_ITEM_VIEW' => 'View ProtectedDownload Items',
            'PD_ITEM_DELETE' => 'Delete ProtectedDownload Items',
            'PD_ITEM_CREATE' => 'Create ProtectedDownload Items',
            'PD_ITEM_EDIT' => 'Edit ProtectedDownload Items'
        );
    }

    public function canView() {
	return Permission::check('PD_ITEM_VIEW');
    }

    public function canDelete() {
	return Permission::check('PD_ITEM_DELETE');
    }

    public function canCreate() {
	return Permission::check('PD_ITEM_CREATE');
    }

    public function canEdit() {
	return Permission::check('PD_ITEM_EDIT');
    }

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $htaccess = ASSETS_PATH . '/' .  $this->folder.'/.htaccess';
        if (! file_exists($htaccess)){
            Folder::findOrMake($this->folder);        
            $fh = fopen($htaccess, 'w');
            fwrite($fh, "# This folder is only accessible via PD_Download\nAllow from none\nDeny from all\n");
            fclose($fh);
        }                
        $file = new PD_FileField('Asset',null,null,null,null,$this->folder);
        $fields->replaceField('Asset',$file);
        return $fields;
    }
    
    function getAssetName(){
        return preg_replace('{\Qassets/'.$this->folder.'/}','',$this->Asset()->Filename);
    }

    function getButtonCode(){
        return '</a>[pd_button]'.$this->ID.'[/pd_button]<a>';
    }

    function getLicenseName(){
        return $this->License()->Name;
    }
    
    function makeTicket($args){
        $ticket = new PD_Ticket;
        $ticket->TicketKey = sha1(rand().microtime());
        $ticket->Generated = (new DateTime)->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s');
        switch ($this->Protection){
            case 'Code':
                if (!isset($args['code'])){
                    throw new SS_HTTPResponse_Exception("Insufficient Data",403);
                }
                $code = explode(':',$args['code'],3);
                if ( sha1($code[0] + ':' + $code[1] + ':' + $this->Secret) != $code[2] ){
                    throw new SS_HTTPResponse_Exception("Invalid Authorization Code",403);
                }
                $ticket->eMail = strtolower($code[0]);
                break;                    
            case 'eMail':
            case 'PayPal':
                $ticket->eMail = strtolower($args['email']);
                if ($this->RequireExtraData){
                    if (! isset($args['first']) || ! isset($args['last']) || !isset($args['affiliation'])){
                        throw new SS_HTTPResponse_Exception("Insufficient Data",403);
                    }
                    $ticket->FirstName = $args['first'];
                    $ticket->LastName = $args['last'];
                    $ticket->Affiliation = $args['affiliation'];
                }
                break;
            default:
                throw new SS_HTTPResponse_Exception("Invalid Protection Type",403);
                break;
        }
        $ticket->Downloads = 0;
        if ($this->ValidDays > 0){
            $ticket->ValidUntil = (new DateTime)->add(new DateInterval('P'.$this->ValidDays.'D'))->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s');
        }
        $ticket->ItemID = $this->ID;
        $ticket->write();
        return $ticket;
    }
}
