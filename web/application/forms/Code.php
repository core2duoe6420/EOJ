<?php

class EOJ_Form_Code extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		//$this->setDisableLoadDefaultDecorators(true);
		//Problem ID
		$p_id=$this->createElement('text','ProblemID');
		$p_id->setLabel('Problem ID')
			->setAttrib('readonly','readonly')
			;
		$p_id->setDisableLoadDefaultDecorators(true);
		$this->addElement($p_id);	
		//Code Language
		$code_lang=$this->createElement('select','CodeLanguage');
		$code_lang->setLabel('Language');
		$code_lang->addMultiOptions(array(
			1=>'GCC',
			2=>'G++'
			));
		$code_lang->setDisableLoadDefaultDecorators(true);
		$this->addElement($code_lang);
		//Source Code
		$code_src=$this->createElement('textarea','CodeSource');
		$code_src->setRequired(true);
		$code_src->addErrorMessage("Are you sure you want to upload EMPTY source code?");
		$code_src->setLabel('Source:')
				->setAttrib('COLS', '110')
				->setAttrib('ROWS', '18');
		/*$code_src->setDisableLoadDefaultDecorators(true);
		$code_src->setDecorators(array(                
            'ViewHelper',
            'Label',
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div','id'=>'code_src'))
			)
		);*/
		$this->addElement($code_src);
		
		//submit
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}

