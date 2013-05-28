<?php

class EOJ_Form_Code extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		//Problem ID
		$p_id=$this->createElement('text','ProblemID');
		$p_id->setLabel('Problem ID')
			->setAttrib('readonly','readonly');
		$this->addElement($p_id);	
		//Code Language
		$code_lang=$this->createElement('select','CodeLanguage');
		$code_lang->setLabel('Language');
		$code_lang->addMultiOptions(array(
			0=>'C',
			1=>'Java'
			));
		$this->addElement($code_lang);
		//Source Code
		$code_src=$this->createElement('textarea','CodeSource');
		$code_src->setRequired(true);
		$code_src->addErrorMessage("Are you sure you want to upload EMPTY source code?");
		$code_src->setLabel('Source:');
		//		->setAttrib('COLS', '40')
		//		->setAttrib('ROWS', '4');
		$this->addElement($code_src);
		
		//submit
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}

