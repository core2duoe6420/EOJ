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
		if($this->getRequest()->getCookie('user_id'))
		{
			$this->view->result='You have Logged in as ID:'.$this->getRequest()->getCookie('user_id').'<br><a href="/Logout">Log out</a>';
		}
		else 
		{
			$form_user=new EOJ_Form_User();
			$form_user->removeElement('AdminType');
			$this->view->form_user=$form_user;
			
			$user_data=$this->getRequest()->getPost();
				
			if(empty($user_data['UserName']))
				$this->view->result="User Name can not be empty";
				
			else 
			//check password
			if(!preg_match("/^[a-zA-Z]\w{5,17}$/",$user_data['UserPassword']))
				$this->view->result="Password do not match the requirement";
				
			else if($user_data['UserPassword']!=$user_data['UserPassword2'])
				$this->view->result='Two Password not match.';
				
			else
			//start register
			{
				$normal_user=new EOJ_Model_NormalUser();
				if($normal_user->Register($user_data['UserName'],$user_data['UserPassword']))
					$this->view->result='Same User Name exists';
				else
				{	
					echo $user_data['UserName'],'<br>';
					echo $user_data['UserPassword'],'<br>';
					$user_id=$normal_user->Login($user_data['UserName'],md5($user_data['UserPassword']));
					echo $user_id,'<br>';
						
						if($user_id>0)
						{
							$cookie_id = new Zend_Http_Cookie('user_id',$user_id,'eoj.org');
							$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'eoj.org');
							$cookie_password = new Zend_Http_Cookie('user_password',md5($user_data['UserPassword']),'eoj.org');
							$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
							$this->_redirect("/user");
						}
				}
			}
			
			
			
		}
    }


}

