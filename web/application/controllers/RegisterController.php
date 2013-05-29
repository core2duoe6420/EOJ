<?php

class RegisterController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		if(!$this->getRequest()->isPost())
		{
			$form_user=new EOJ_Form_User();
			$this->view->result=$form_user;
		}
		else
		{
			$user_data=$this->getRequest()->getPost();
			if($user_data['UserPassword']!=$user_data['UserPassword2'])
			{
				$this->view->result='Two Passwords not match.';
			}
			else
			{
				$normal_user=new EOJ_Model_NormalUser();
				if($normal_user->Register($user_data['UserName'],$user_data['UserPassword']))
					$this->view->result='Same User Name exists';
				else
				{	
					$user_id=$normal_user->Login($user_data['UserName'],$user_data['UserPassword']);
					
					if($user_id>0)
					{
						//$cookie = new Zend_Http_Cookie('user_id',NULL,'eoj.org/',NULL,"/");
						$cookie_id = new Zend_Http_Cookie('user_id',$user_id,'eoj.org');
						$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'eoj.org');
						$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
						$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
						$this->_redirect("/");
					}
				}
			}
			
			
		}
    }


}

