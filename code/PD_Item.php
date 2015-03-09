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
	"Protection"  	=> "Enum('eMail,PayPal','eMail')",
	"Price"		=> "Decimal(15,2)",
        "Description" 	=> "Varchar(255)",
        "Key"		=> "Varchar(255)",
    );
    
    static $has_one = array(
        "License" => "PD_License",
        "Asset" => "File",
    );
    
//Fields to show in ModelAdmin table
    static $summary_fields = array(
	'ButtonCode','Protection','Description','AssetName','LicenseName'
    );  

    static $searchable_fields = array(
        'Description'
    );
    static $field_labels = array(
	'ButtonCode' => 'Button',
	'Protection' => 'Protection Method',
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
        $fields->replaceField('Key',new ReadOnlyField('Key'));
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
    
    function onBeforeWrite(){
        if (! $this->ID){
            $this->Key = sha1(rand().microtime());
        }
        return parent::onBeforeWrite();
    }

    function makehash($email){
        return sha1($email.':'.$this->Key);
    }                                            

    protected function log($email,$action){
        $log = new PD_Log;
        $log->Timestamp = (new DateTime)->setTimeZone(new DateTimeZone('GMT'))->format('Y-m-d H:i:s');
        $log->eMail = $email;
        $log->ItemID = $this->ID;
        $log->Asset = $this->Asset()->getFullPath();
        $log->Action = $action;
        $log->write();
    }

    function logDownload($email){
        $this->log($email,'Download');
    }

    function logSendKey($email){
        $this->log($email,'SendKey');
    }        
    
    public function mailLink($email){
        $cfg = SiteConfig::current_site_config();
        $hash = $this->makehash($email);
        $this->logSendKey($email);
        return mail($email, 'Download Link for '.$this->Description, "You can access your download on\n\n".Director::absoluteBaseURL('')."PD_Download?email=$email&item=".$this->ID."&hash=$hash",'From: '.$cfg->PD_adminEmail);
    }
}
