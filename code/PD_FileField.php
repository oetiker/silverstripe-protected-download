<?php 
/* ************************************************************************   
   Copyright: 2015 OETIKER+PARTNER AG
   License:   GPLv3 or later
   Authors:   Tobi Oetiker <tobi@oetiker.ch>
   Utf8Check: äöü
 
   A special FileField which allows to restrict the File selection to
   a given Folder.

 ************************************************************************ */

class PD_FileField extends FileIFrameField {
	
	protected $folderID = 0;
	function __construct($name, $title = null, $value = null, $form = null, $rightTitle = null, $folderName = null) {
		if(isset($folderName) && $ID = $this->getFolderID($folderName)) $this->folderID = $ID;
	
		parent::__construct($name, $title, $value, $form, $rightTitle, $folderName);
	}
	
	public function getFolderID($FolderName){
		if($Folder = DataObject::get_one('Folder', "Name = '" . $FolderName . "'")){
			return $Folder->ID;
		}
	}
	
	public function EditFileForm() {
		$uploadFile = _t('FileIFrameField.FROMCOMPUTER', 'From your Computer');
		$selectFile = _t('FileIFrameField.FROMFILESTORE', 'From the File Store');
		
		if($this->AttachedFile() && $this->AttachedFile()->ID) {
			$title = sprintf(_t('FileIFrameField.REPLACE', 'Replace %s'), $this->FileTypeName());
		} else {
			$title = sprintf(_t('FileIFrameField.ATTACH', 'Attach %s'), $this->FileTypeName());
		}
		
		$fileSources = array();
		
		if(singleton($this->dataClass())->canCreate()) {
			if($this->canUploadNewFile) {
				$ff = new FileField('Upload', '');
				$fileSources["new//$uploadFile"] = $ff;				
			}
		}
		
		$DropdownField = new TreeDropdownField('ExistingFile', '', 'File');
		$DropdownField->setTreeBaseID($this->foldrID);
		
		$fileSources["existing//$selectFile"] = $DropdownField;

		$fields = new FieldSet (
			new HeaderField('EditFileHeader', $title),
			new SelectionGroup('FileSource', $fileSources)
		);
		
		// locale needs to be passed through from the iframe source
		if(isset($_GET['locale'])) {
			$fields->push(new HiddenField('locale', '', $_GET['locale']));
		}
		
		return new Form (
			$this,
			'EditFileForm',
			$fields,
			new FieldSet(
				new FormAction('save', $title)
			)
		);
	}
}
