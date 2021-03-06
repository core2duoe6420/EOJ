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
		if(!$this->getRequest()->getCookie('user_id'))
			$this->_redirect('/Login');
		if($this->getRequest()->getCookie('user_power'))
			$this->view->errormsg='作为一个管理员用户，您不能答题';
		else
		{
		$form_code=new EOJ_Form_Code();
		
		if($p_id=$this->_request->getParam('p_id'))
		{
			$form_code->getElement('ProblemID')->setValue($p_id);
		}	
		else 
		{
			@$form_code->getElement('ProblemID')->setAttrib('readonly');
		}
		if($this->getRequest()->isPost())
		{
			if($form_code->isValid($_POST))
			{
				$code_data=$form_code->getValues();
				
				$submit_code=new EOJ_Model_SubmitCode();
				$result=$submit_code->SubmitCode($code_data['CodeSource'],$this->getRequest()->getCookie('user_id'),$code_data['ProblemID'],$code_data['CodeLanguage']);
				//echo $result;
				/*if($result=="Succeed") 
				{
					echo '2';
					//echo '<script type="text/javascript">document.redirect(/Online-Status);</script>';
				}
					//$this->_redirect("Online-Status");
				else*/
					$this->view->errormsg=$result;
			}
		}
		$this->view->formCode=$form_code;
		}
    }
}