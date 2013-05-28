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
			echo 'yes';
			echo '<br>';
			echo $this->getRequest()->getCookie('user_id');
			echo '<br>';
			
		}
		if(!$this->getRequest()->isPost())
		{
			$form_user=new EOJ_Form_User();
			$form_user->removeElement('UserID');
			$form_user->removeElement('UserPassword2');
			$this->view->formUser=$form_user;
			
			/*if(($this->getRequest()->isPost()))
			{
				if($form_user->isValid($_POST))
				{
					$data=$form_user->getValues();
					foreach($data as $key=>$value)
					{
						echo $key,'->';
						echo $value,'<br>';
					}
				}
			}*/
		}
		else
		{
			//input the name and password to login return user_id
			$user_id=1;
			if(true)
			{
				echo time(),'<br>';
			//	$cookie = new Zend_Http_Cookie('user_id',$user_id,'eoj.org',NULL);
			//	$this->getResponse()->setHeader('Set-Cookie',$cookie->__toString());
			$cookie = new Zend_Http_Cookie('user_id',NULL,'eoj.org/',NULL,"/");
				//$cookie = new Zend_Http_Cookie('user_id',3,'eoj.org');
				$this->getResponse()->setHeader('Set-Cookie',$cookie->__toString());
				echo 'set';
			}
		}
    }


}

