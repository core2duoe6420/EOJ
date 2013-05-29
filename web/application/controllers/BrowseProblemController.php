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
		
		if($this->_request->getParam('page'))
			$page=$this->_request->getParam('page');
		else
			$page=1;
		
		$CheckedProblem=new EOJ_Model_CheckedProblem();
		
		$id_min=$CheckedProblem->GetMinProblemID();
		$id_max=$CheckedProblem->GetMaxProblemID();
		$page_num=ceil(($id_max-$id_min+1)/50);
		$StartID=$id_min+($page-1)*50;
		$EndID=min($StartID+49,$id_max);
		
		$Result=$CheckedProblem->GetProblemList($StartID,$EndID);
		
		$this->view->Result=$Result;
		$this->view->Page_Num=$page_num;
	
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
		$Problem=new EOJ_Model_CheckedProblem();
		$Problem->SetProblemID($p_id);
		$Result=array(
			'ID'=>$p_id,
			'Title'=>$Problem->GetproblemName(),
			'TimeLimit'=>$Problem->GettimeLimit(),
			'MemoryLimit'=>$Problem->GetmemoryLimit(),
			'Discription'=>$Problem->Getdiscription(),
			'SampleInput'=>$Problem->GetsampleInput(),
			'SampleOutput'=>$Problem->GetsampleOutput(),
			'Source'=>$Problem->GetSource(),
			'InputTips'=>$Problem->GetinputTips(),
			'OutputTips'=>$Problem->GetoutputTips(),
			'Hint'=>$Problem->GetHint()
			);
		$this->view->Result=$Result;
    }


}



