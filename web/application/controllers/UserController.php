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
		$user_acc_rate=$user_submit_count?($user_acc_count/$user_submit_count):'0';
		
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
					$this->view->panel_user_power="题目上传者";
					break;
				case 2:
					$this->view->panel_user_power="题目审核者";
					break;
				case 255:
					$this->view->panel_user_power="系统管理员";
					break;
			}
		}
    }


    public function userinfoAction()
    {
        // action body
		$user_name=$this->_request->getParam('user_name');
		if(!$user_name)
			$this->view->errormsg='无此用户';
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
				$this->view->errormsg='无此用户';
		}
    }


    public function browsecodeAction()
    {
        // action body
		//$this->getHelper('layout')->disableLayout();

		$run_id=$this->_request->getParam('run_id');
		$user= new EOJ_Model_NormalUser();
		$code=$user-> ReturnCode($run_id);
		
		$code=str_replace("&","&amp;",$code);
		$code=str_replace("<","&lt;",$code);
		$code=str_replace(">","&gt;",$code);
		
		$this->view->codeReturn=$code;
    }

}











