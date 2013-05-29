<?php

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		if($this->getRequest()->getCookie('user_id'))
		{
			$this->view->result='You have Logged in as ID:'.$this->getRequest()->getCookie('user_id').'<br><a href="/Logout">Log out</a>';
		}
		else if(!$this->getRequest()->isPost())
		{
			$form_user=new EOJ_Form_User();
			$form_user->removeElement('UserPassword2');
			$this->view->formUser=$form_user;
			
		}
		else
		{
			$user_data=$this->getRequest()->getPost();
			$normal_user=new EOJ_Model_NormalUser();
			switch($user_id=$normal_user->Login($user_data['UserName'],$user_data['UserPassword']))
			{
				case -3:
					$this->view->errormsg="Database Query False";
					break;
				case -2:
					$this->view->errormsg="No Such User";
					break;
				case -1:
					$this->view->errormsg="Wrong PassWord";
					break;
			}
			//user_data
			//$user_data['UserName']
			//$user_data['UserPassword']
			
			//use the name and password to login return user_id
			
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

