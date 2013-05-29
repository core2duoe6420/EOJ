<?php

class LogoutController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$cookie_id= new Zend_Http_Cookie('user_id',NULL,'eoj.org');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
		$cookie_name= new Zend_Http_Cookie('user_name',NULL,'eoj.org');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
		$this->view->result='You have successfully Logged out';
		
		$this->_redirect("/");
		
    }


}

