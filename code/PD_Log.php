<?php    

class PD_Log extends DataObject implements PermissionProvider {

    static $api_access = true;
    
    public static $db = array(
        "Timestamp"   	=> "Varchar(255)",
	"eMail"  	=> "Varchar(255)",
        "ItemID" 	=> "Int",
        "Asset" 	=> "Varchar(255)",
        "Action"	=> "Enum('Download,SendKey','Download')",
    );
    
//Fields to show in ModelAdmin table
    static $field_labels = array(
	'Timestamp' => 'Timestamp',
	'eMail' => 'eMail',
        'Asset' => 'Asset',
        'ItemID' => 'ItemID',
        'Action' => 'Action',
    );  

    static $summary_fields = array(
         'Timestamp','eMail','ItemID','Asset','Action'
    );  
             
    static $searchable_fields = array(
         'Timestamp','eMail','Asset','Action'
     );
                             
    public function providePermissions() {
         return array(
            'PD_LOG_VIEW' => 'View ProtectedDownload Log',
            'PD_LOG_DELETE' => 'Delete ProtectedDownload Log',
        );
    }

    public function canView() {
	return Permission::check('PD_LOG_VIEW');
    }

    public function canDelete() {
	return Permission::check('PD_LOG_DELETE');
    }

    public function canCreate() {
	return false;
    }

    public function canEdit() {
	return false;
    }

}
