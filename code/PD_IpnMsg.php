<?php    

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Store IPN Messages

 ************************************************************************ */
                       
class PD_IpnMsg extends DataObject implements PermissionProvider {

    static $api_access = true;
    

    // https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
    public static $db = array(
        "txn_type" => "Varchar(255)",
	"txn_id"=>"Varchar(255)",
        "receiver_id" => "Varchar(255)",
        "receiver_email" => "Varchar(255)",
	"charset" => "Varchar(255)",
	"custom" => "Varchar(255)",
	"parent_txn_id" => "Varchar(255)",
	"receipt_id"=>"Varchar(255)",
        "test_ipn"=>"Varchar(255)",
	"verify_sign"=>"Varchar(255)",
	"payer_id"=>"Varchar(255)",
	"payer_email"=>"Varchar(255)",
	"payer_business_name"=>"Varchar(255)",
	"payer_status"=>"Varchar(255)",
	"address_status"=>"Varchar(255)",
	"first_name"=>"Varchar(255)",
	"last_name"=>"Varchar(255)",
	"contact_phone"=>"Varchar(255)",
	"address_street"=>"Varchar(255)",
	"address_zip"=>"Varchar(255)",
	"address_city"=>"Varchar(255)",
	"address_state"=>"Varchar(255)",
	"address_country"=>"Varchar(255)",
	"quantity"=>"Int",
	"item_number"=>"Varchar(255)",
	"item_name"=>"Varchar(255)",
	"mc_gross"=>"Decimal(15,2)",
	"mc_fee"=>"Decimal(15,2)",
	"mc_currency" => "Varchar(255)",
	"payment_status"=>"Varchar(255)",
	"payment_type"=>"Varchar(255)",
	"payment_date"=>"Varchar(20)",
	"ipn_json"=>"Text",
    );

    static $field_labels = array(
	'txn_type' => 'TxnType',
	'test_ipn' => 'Test',
        'payment_status' => 'Status',
	'txn_id' => 'TxnId',
	'payment_date' => 'PaymentDate',
        'first_name' => 'FirstName',
        'last_name' => 'LastName',
        'payer_email' => 'eMail',
	'payer_business_name' => 'Business',
	'quantity' => 'Qt',
	'item_number' => 'ItemId',
	'item_name' => 'Item',	
        'payment_status' => 'Status',
	'payment_type' => 'Type',
	'mc_gross' => 'Amount',
	'mc_fee' => 'Fee',
	'mc_currency' => 'Currency'
    );
//Fields to show in ModelAdmin table
    static $summary_fields = array(
        'txn_type','test_ipn','payment_status','txn_id','payment_date','first_name','last_name','payer_email','payer_business_name','quantity','item_number','item_name','payment_status','payment_type','mc_gross','mc_fee'
    );  

    static $searchable_fields = array(
        'payment_date','test_ipn','payer_email','item_number','txn_type','payment_status'
    );
    
    public function providePermissions() {
         return array(
            'PD_IPN_VIEW' => 'View PayPal IPN Entries',
            'PD_IPN_DELETE' => 'Delete PayPal IPN Entries',
        );
    }

    public function canView() {
	return Permission::check('PD_IPN_VIEW');
    }

    public function canDelete() {
	return Permission::check('PD_IPN_DELETE');
    }

    public function canCreate() {
         return false;
    }

    public function canEdit() {
         return false;
    }
 
}
