<?php

class SubmitProblemController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$form_problem=new EOJ_Form_Problem();
		$this->view->form_problem=$form_problem;
    }
}