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
			$this->view->result='You have Logged in as <br>ID:'
			.$this->getRequest()->getCookie('user_id')
			.'   Name:'
			.$this->getRequest()->getCookie('user_name')
			.'<br><a href="/Logout">Log out</a>';
		}
		else
		{
			$user_form=new EOJ_Form_User();
			$user_form->removeElement('UserPassword2');
			$user_form->setAction('/Login');
			$this->view->result=$user_form;
		}
    }


}



