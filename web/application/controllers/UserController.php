<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		
		$user_id=$this->getRequest()->getCookie('user_id');
		if(!$user_id)
			$this->_redirect('/Login');
			
		$user_name=$this->getRequest()->getCookie('user_name');
		
		$user=new EOJ_Model_NormalUser();
		$user->getInfo($user_name);
		
		$user_acc_count=$user->ReturnAcceptCount();
		$user_submit_count=$user->ReturnSubmitCount();
		$user_acc_rate=$user_submit_count?($user_acc_count/$user_submit_count):'NaN';
		
		$acc_submit_code=new EOJ_Model_SubmitCode();
		$acc_code_list=$acc_submit_code->GetResult(0,$user_name,1,0);
		
		$this->view->user_id=$user_id;
		$this->view->user_name=$user_name;
		$this->view->user_acc_count=$user_acc_count;
		$this->view->user_submit_count=$user_submit_count;
		$this->view->user_acc_rate=$user_acc_rate;
		$this->view->acc_code_list=$acc_code_list;
    }

    public function panelAction()
    {
        // action body
		
		if($this->getRequest()->getCookie('user_id'))
			$this->view->panel_user_name=$this->getRequest()->getCookie('user_name');
		if($user_power=$this->getRequest()->getCookie('user_power'))
		{
			switch($user_power)
			{
				case 1:
					$this->view->panel_user_power="ProblemUploader";
					break;
				case 2:
					$this->view->panel_user_power="ProblemChecker";
					break;
				case 255:
					$this->view->panel_user_power="SystemAdmin";
					break;
			}
		}
    }


    public function userinfoAction()
    {
        // action body
		$user_name=$this->_request->getParam('user_name');
		if(!$user_name)
			$this->view->errormsg='No such user';
		else
		{
			$user=new EOJ_Model_NormalUser();
			if($user->getInfo($user_name))
			{
				$user_id=$user->ReturnUserID();
				$user_acc_count=$user->ReturnAcceptCount();
				$user_submit_count=$user->ReturnSubmitCount();
				$user_acc_rate=$user_submit_count?($user_acc_count/$user_submit_count):'NaN';
		
				$acc_submit_code=new EOJ_Model_SubmitCode();
				$acc_code_list=$acc_submit_code->GetResult(0,$user_name,1,0);
			
				$this->view->user_id=$user_id;
				$this->view->user_name=$user_name;
				$this->view->user_acc_count=$user_acc_count;
				$this->view->user_submit_count=$user_submit_count;
				$this->view->user_acc_rate=$user_acc_rate;
				$this->view->acc_code_list=$acc_code_list;
			}
			else
				$this->view->errormsg='No such user';
		}
    }

    public function changePasswordAction()
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
				
				echo $OriginPassword,'<br>',$NewPassword1,'<br>',$NewPassword2,'<br>';
				
				if(md5($OriginPassword)!=$this->getRequest()->getCookie('user_password'))
					$this->view->errorMessage="Origin Password Error";
				else
				
				if($NewPassword1!=$NewPassword2)
					$this->view->errorMessage="Password do not match";
				else
				{
					$user= new EOJ_Model_NormalUser();
					//change password
					$errorcode=$user->ChangePassword($user_id,$NewPassword1);
					echo $errorcode;
					if(0!=$errorcode)
						$this->view->errorMassage=$errorcode;
					else
					{
						$this->view->errorMassage="Password Change Success";
						$cookie_password = new Zend_Http_Cookie('user_password',md5($NewPassword1),'eoj.org');
						$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
					}
				}
			}
		}
    }

    public function browsecodeAction()
    {
        // action body
		$run_id=$this->_request->getParam('run_id');
		$user= new EOJ_Model_NormalUser();
		$code=$user-> ReturnCode($run_id);
		
		//$code='#include<stdio.h>\nvoid main(){\nint a,b;\nscanf("%d%d"),&a,&b);\nprintf("%d",a+b);';
		
		$this->view->codeReturn=$code;
    }

}











