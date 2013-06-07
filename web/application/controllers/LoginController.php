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
			$this->view->result='您已按<br>ID:'.$this->getRequest()->getCookie('user_id').'<br>登录<a href="/Logout">Log out</a>';
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
								$this->view->errormsg="数据库查询出错";
								break;
							case -2:
								$this->view->errormsg="无此用户";
								break;
							case -1:
								$this->view->errormsg="密码错误";
								break;
						}
						if($user_id>0)
						{
						//$cookie = new Zend_Http_Cookie('user_id',NULL,'www.ecustoj.info/',NULL,"/");
							$cookie_id = new Zend_Http_Cookie('user_id',$user_id,'www.ecustoj.info');
							$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'www.ecustoj.info');
							$cookie_password = new Zend_Http_Cookie('user_password',md5($user_data['UserPassword']),'www.ecustoj.info');
							$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
							$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
							$this->_redirect("/user");
						}
					}
					else
						$this->view->errormsg="用户名不能为空";
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
								$this->view->errormsg="无此用户";
								break;
							case -1:
								$this->view->errormsg="密码错误";
								break;
							default:
							//$cookie = new Zend_Http_Cookie('user_id',NULL,'www.ecustoj.info/',NULL,"/");
								$cookie_id = new Zend_Http_Cookie('user_id',$admin_id,'www.ecustoj.info');
								$cookie_name = new Zend_Http_Cookie('user_name',$user_data['UserName'],'www.ecustoj.info');
								$cookie_password = new Zend_Http_Cookie('user_password',md5($user_data['UserPassword']),'www.ecustoj.info');
								$cookie_power= new Zend_Http_Cookie('user_power',$user_data['AdminType'],'www.ecustoj.info');
								$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
								$this->getResponse()->setHeader('Set-Cookie',$cookie_power->__toString());
								$this->_redirect("/Un-Checked-Problem");
						}
					}	
					else
						$this->view->errormsg="用户名不能为空";
				}
			}
		}
    }
}

