<?php

define('PROTECTED_DOWNLOAD_DIR',basename(dirname(__FILE__)));
ShortcodeParser::get()->register('pd_button', array('PD_Button', 'ShortCodeHandler'));
ShortcodeParser::get()->register('pd_virtual', array('PD_Virtual', 'ShortCodeHandler'));
DataObject::add_extension('SiteConfig', 'PD_SiteConfig');
