<?php

ShortcodeParser::get()->register('pd_button', array('PD_Button', 'ShortCodeHandler'));
DataObject::add_extension('SiteConfig', 'PD_SiteConfig');
