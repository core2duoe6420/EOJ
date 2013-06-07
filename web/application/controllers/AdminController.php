<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		if($this->getRequest()->getCookie('user_power')!=255)
			$this->view->errormsg='对不起，您没有访问此页面的权限';
		else
		{
			$adminform=new EOJ_Form_User();
			$adminform->removeElement('UserPassword2');
			$adminform->removeElement('提交');
			$formUserInfo=$adminform->createElement('textarea','user_info');
			$formUserInfo->setLabel('描述信息')
				//->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
			$adminform->addElement($formUserInfo);
			$submit=$adminform->createElement('submit','提交');
			$adminform->addElement($submit);
			
			$this->view->adminform=$adminform;
			
			if($this->getRequest()->isPost())
			{
				$admin_data=$this->getRequest()->getPost();
				if(empty($admin_data['UserName']))
					$this->view->result="用户名不能为空";
				else
				{
					$admin=new EOJ_Model_SystemAdmin();
					$userName=$admin_data['UserName'];
					$passWord=$admin_data['UserPassword'];
					$userInformation=$admin_data['user_info'];
					$power=$admin_data['AdminType'];
					switch($admin->AppointAdminstrator($userName,$passWord,$userInformation,$power))
					{
						case -1:
							$this->view->result="数据库错误";
							break;
						case -2:
							$this->view->result="已存在同名用户";
							break;
						case -3:
							$this->view->result="存在非法空值";
							break;
						default:
							$this->view->result="成功指派用户";
					}
				}
			}
		}
    }


}

