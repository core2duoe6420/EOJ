<?php

class SubmitCodeController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$p_id=$this->_request->getParam('p_id');
		$form_code=new EOJ_Form_Code();
		$form_code->getElement('ProblemID')->setValue($p_id);
		
		if($this->getRequest()->isPost())
		{
			if($form_code->isValid($_POST))
			{
				$code_data=$form_code->getValues();
				//index of code_data ProblemID CodeLanguage CodeSource
				
				//upload code
				
				$this->_redirect('/Online-Status');
			}
		}
		
		$this->view->formCode=$form_code;
		
		
    }


}



