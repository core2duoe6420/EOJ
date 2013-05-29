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
    }

    public function panelAction()
    {
        // action body
		//$user_id=$this->getRequest()->getCookie('user_name');
		if($user_name=$this->getRequest()->getCookie('user_name'))
			$this->view->panel_user_id=$user_name;
    }

    


}





