<?php

class IndexController extends Zend_Controller_Action
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
			$result='<br>ID:'
			.$this->getRequest()->getCookie('user_id')
			.'<br>登录名:'
			.$this->getRequest()->getCookie('user_name')
			.'<br><a href="/Logout">Log out</a>';
			switch($this->getRequest()->getCookie('user_power'))
			{
				case 1:
					$result='题目上传者'.$result;
					break;
				case 2:
					$result='题目审核者'.$result;
					break;
				case 255:
					$result='系统管理员'.$result;
					break;
			}
			$this->view->result=$result;
		}
		else
		{
			$user_form=new EOJ_Form_User();
			$user_form->removeElement('UserPassword2');
			$user_form->removeElement('AdminType');
			$user_form->setAction('/Login');
			$this->view->result=$user_form;
		}
    }
}



