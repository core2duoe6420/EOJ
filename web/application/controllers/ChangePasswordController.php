<?php

class ChangePasswordController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		if(!$user_id=$this->getRequest()->getCookie('user_id'))
			$this->_redirect('/Login');
		$user_name=$this->getRequest()->getCookie('user_name');
		if($this->getRequest()->isPost())
		{
			if($changeData=$this->getRequest()->getPost())
			{
				$OriginPassword=$changeData['OriginPassword'];
				$NewPassword1=$changeData['NewPassword1'];
				$NewPassword2=$changeData['NewPassword2'];
				
				if(md5($OriginPassword)!=$this->getRequest()->getCookie('user_password'))
					$this->view->errorMessage="原密码输入错误";
				
				else if(!preg_match("/^[a-zA-Z]\w{5,17}$/",$NewPassword1))
				{
					$this->view->errorMessage="新密码不符合密码格式";
				}
				
				else if($NewPassword1!=$NewPassword2)
					$this->view->errorMessage="两次密码输入不匹配";
					
				else
				{
					$user= new EOJ_Model_NormalUser();
					//change password
					$errorcode=$user->ChangePassword($user_id,$NewPassword1);
					
					if(0!=$errorcode)
						$this->view->errorMessage=$errorcode;
					else
					{
						$this->view->errorMessage="更改密码成功";
						$cookie_password = new Zend_Http_Cookie('user_password',md5($NewPassword1),'www.ecustoj.info');
						$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
					}
				}
			}
		}
    }


}

