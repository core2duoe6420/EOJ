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
		$CheckedProblem=new EOJ_Model_CheckedProblem();
		$Result=$CheckedProblem->GetProblemList();
		
		$this->view->Result=$Result;
	
		/*//test
		$form_problem=new EOJ_Form_Problem();
		$this->view->formProblem=$form_problem;
		if(($this->getRequest()->isPost()))
		{
			if($form_problem->isValid($_POST))
			{
				$data=$form_problem->getValues();
				foreach($data as $value)
				{
					echo $value;
				}
			}
		}*/
    }

    public function detailAction()
    {
        // action body
		$p_id=$this->_request->getParam('p_id');
		$Result=array(
			'ID'=>$p_id,
			'Title'=>'aaa',
			'TimeLimit'=>'100',
			'MemoryLimit'=>111
			);
		$this->view->Result=$Result;
    }


}



