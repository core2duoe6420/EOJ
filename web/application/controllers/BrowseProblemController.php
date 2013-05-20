<?php

class BrowseProblemController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$CheckedProblem=new Application_Model_CheckedProblem();
		$Result=$CheckedProblem->GetProblemList();
		$this->view->Result=$Result;
	
    }


}

