ProtectedDownloads
------------------

With this module your SilverStripe 2.4 site can provide individually
authenticated downloads. Downloads can either be authenticated via eMail or
sold via PayPal.

An integrated IPN Receiver keeps track of PayPal PaymentNotifications and a
Log keeps track of all Downloads and Key mailouts.

Each downloadable asset can be protected by an additional license screen.

Inside Pages you can then use the pd_button shortcode with the
id of the download item you want to provide access to.

  [pd_button]3[/pd_button]

Depending on the configuration of the item, the email address of
the user will be requested OR a paypal button will show.

Users get a download link by email. Once they download they have to
agree to the license conditions if a license is configured with the
item.

In the Site Config section under the Protected Download tab you can
enter your PayPal MercahntId and enable PayPal sandbox mode for
testing.

2015-03-09 
Tobi Oetiker <tobi@oetiker.ch>
