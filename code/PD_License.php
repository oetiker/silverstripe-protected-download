<?php    

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Store Licenses
 ************************************************************************ */


class PD_License extends DataObject implements PermissionProvider {

    static $api_access = true;
    
    public static $db = array(
        "Name"   	=> "Varchar(255)",
	"License"  	=> "HTMLText",
    );
    
//Fields to show in ModelAdmin table
    static $field_labels = array(
	'Name' => 'Name',
	'License' => 'License',
    );  

    static $summary_fields = array(
         'Name'
    );  
             
    static $searchable_fields = array(
         'Name'
     );
                             
    public function providePermissions() {
         return array(
            'PD_LICENSE_VIEW' => 'View ProtectedDownload License',
            'PD_LICENSE_DELETE' => 'Delete ProtectedDownload License',
            'PD_LICENSE_CREATE' => 'Create ProtectedDownload License',
            'PD_LICENSE_EDIT' => 'Edit ProtectedDownload License',
        );
    }

    public function canView() {
        return Permission::check('PD_LICENSE_VIEW');
    }

    public function canDelete() {
        return Permission::check('PD_LICENSE_DELETE');
    }

    public function canCreate() {
        return Permission::check('PD_LICENSE_CREATE');
    }

    public function canEdit() {
        return Permission::check('PD_LICENSE_EDIT');
    }
                                                                
}
