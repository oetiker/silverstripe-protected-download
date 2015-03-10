<?php

/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Add SiteWide Configuration Variables to handle ProtectedDownloads

   Enabled by adding this to the _config.php file
   DataObject::add_extension('SiteConfig', 'PD_SiteConfig');

 ************************************************************************ */

class PD_SiteConfig extends DataObjectDecorator {
     
    function extraStatics() {
        return array(
            'db' => array(
                'PD_adminEmail' => 'Varchar(60)',
                'PD_fromAddress' => 'Varchar(60)',
		'PD_PPmerchantId' => 'Varchar(60)',
		'PD_PPcurrency' => 'Varchar(3)',
		'PD_PPsandbox' => 'Boolean',
		'PD_PPipnCopies' => 'Boolean',
		'PD_codeCopies' => 'Boolean',
            ),
        );
    }
  
    public function updateCMSFields(FieldSet &$fields) {
        $fields->addFieldsToTab("Root.ProtectedDownload", array(
	    new TextField("PD_fromAddress","From Address"),
	    new TextField("PD_adminEmail","Admin eMail"),
	    new CheckBoxField("PD_codeCopies","Get eMail copies of Download Code Messages"),
	    new TextField("PD_PPmerchantId","PayPal MerchantId"),
	    new TextField("PD_PPcurrency","PayPal Currency"),
	    new CheckBoxField("PD_PPsandbox","Run in PayPal Sandbox Mode"),
	    new CheckBoxField("PD_PPipnCopies","Get eMail copies of IPN Messages"),
	));
    }
     
}
