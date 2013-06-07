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
		$cookie_id= new Zend_Http_Cookie('user_id',NULL,'www.ecustoj.info');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_id->__toString());
		$cookie_name= new Zend_Http_Cookie('user_name',NULL,'www.ecustoj.info');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_name->__toString());
		$cookie_password= new Zend_Http_Cookie('user_password',NULL,'www.ecustoj.info');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_password->__toString());
		$cookie_power= new Zend_Http_Cookie('user_power',NULL,'www.ecustoj.info');
		$this->getResponse()->setHeader('Set-Cookie',$cookie_power->__toString());
		$this->view->result='您已成功登出';
		
		$this->_redirect("/");	
    }
}

