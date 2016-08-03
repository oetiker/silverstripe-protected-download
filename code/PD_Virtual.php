<?php
/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   Add the [pd_virtual]code[/pd_virtual] short code.

   hides all existing input fields for code and fills them with whatever information is entered here
   
   ShortcodeParser::get()->register('pd_virtualn', array('PD_Virtual', 'ShortCodeHandler'));
 ************************************************************************ */

class PD_Virtual {
    public static function ShortCodeHandler($arguments,$content = null, $parser = null, $tagName) {
        return <<<HTML_END
        <input id="PDV_$content" type="text" width="30"/>
        <script>
        jQuery(document).ready(function(){
            jQuery('input[name="$content"]')
               .parent().parent().hide() /* fieldset */
               .parent().parent().css({display: 'inline-block'}) /* form */
               .find('.Actions').css({marginLeft: '0px'}); /* action button */
        });
        jQuery('#PDV_$content').pm('change keyup paste',function(){
             jQuery('input[name="$content"]').val(jQuery(this).val());
        });
        </script>
HTML_END;
   }
}


