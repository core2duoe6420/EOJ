<?php

class OnlineStatusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$SubmitCode=new EOJ_Model_SubmitCode();
		
		//$Result=$SubmitCode->GetResult();
		$this->view->Result=$SubmitCode;
    }


}

