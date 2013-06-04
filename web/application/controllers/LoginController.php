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
		else 	
		{
			if(!$this->_request->getparam('admin'))
			//normal user
			{
				if(!$this->getRequest()->isPost())
				{
					$form_user=new EOJ_Form_User();
					$form_user->removeElement('UserPassword2');
					$form_user->removeElement('AdminType');
					$this->view->formUser=$form_user;	
				}
				else
				{
					$user_data=$this->getRequest()->getPost();
					if($user_data['UserName'])
					{
						$normal_user=new EOJ_Model_NormalUser();
						switch($user_id=$normal_user->Login($user_data['UserName'],md5($user_data['UserPassword'])))
						{
							case -3:
								$this->view->errormsg="Database Query False";
								break;
							case -2:
								$this->view->errormsg="No Such User";
								break;
							case -1:
								$this->view->errormsg="Wrong Password";
								break;
						}
						if($user_id>0)
						{
						//$cookie = new Zend_Http_Cookie('user_id',NULL,'eoj.org/',NULL,"/");
							$cookie_id = new Zend_Http_Cookie('user_id',$user_id,'eoj.org');
							$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'eoj.org');
							$cookie_password = new Zend_Http_Cookie('user_password',md5($user_data['UserPassword']),'eoj.org');
							$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
							$this->_redirect("/user");
						}
					}
					else
						$this->view->errormsg="User Name Empty";
				}
			}
			else
			//admin user
			{
				if(!$this->getRequest()->isPost())
				{
					$form_user=new EOJ_Form_User();
					$form_user->removeElement('UserPassword2');
					$this->view->formUser=$form_user;	
				}
				else
				{
					$user_data=$this->getRequest()->getPost();
					if($user_data['UserName'])
					{
					
						//$admin_user login
						$admin=new EOJ_Model_Adminstrator();
						$admin_id=$admin->Login($user_data['UserName'],$user_data['UserPassword'],$user_data['AdminType']);
						
						
						//		$this->view->errormsg="No Such User";
						switch($admin_id)
						{
							case -3:
							case -2:
								$this->view->errormsg="No Such User";
								break;
							case -1:
								$this->view->errormsg="Password Error";
								break;
							default:
							//$cookie = new Zend_Http_Cookie('user_id',NULL,'eoj.org/',NULL,"/");
								$cookie_id = new Zend_Http_Cookie('user_id',$admin_id,'eoj.org');
								$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'eoj.org');
								$cookie_password = new Zend_Http_Cookie('user_password',md5($user_data['UserPassword']),'eoj.org');
								$cookie_power= new Zend_Http_Cookie('user_power',$user_data['AdminType'],'eoj.org');
								$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_power->__toString());
								$this->_redirect("/Un-Checked-Problem");
						}
					}	
					else
						$this->view->errormsg="User Name Empty";
				}
			}
		}
    }
}

