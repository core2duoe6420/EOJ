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
		
		
		$filter_problem_id=$this->_request->getParam('filter_problem_id');
		if(!(isSet($filter_problem_id)and($filter_problem_id!=0)))
			$filter_problem_id=0;
			
		$filter_user_name=$this->_request->getParam('filter_user_name');
		if((empty($filter_user_name)))
			$filter_user_name=0;
			
		$filter_result=$this->_request->getParam('filter_result');
		if(!isSet($filter_result))
			$filter_result=0;
			
		$filter_language=$this->_request->getParam('filter_language');
		if(!isSet($filter_language))
			$filter_language=0;
			
		if($filter_problem_id==0 and $filter_user_name==0 and $filter_result==0 and $filter_language==0)
		{
			if($this->_request->getParam('page'))
						$page=$this->_request->getParam('page');
			else
				$page=1;
			$id_min=$SubmitCode->GetMinRunID();
			$id_max=$SubmitCode->GetMaxRunID();
			$page_num=ceil(($id_max-$id_min+1)/20);
			$EndID=$id_max-($page-1)*20;
			$StartID=max($EndID-19,$id_min);
					
			$this->view->Page_Num=$page_num;
			$this->view->Result=$SubmitCode->GetResult($filter_problem_id,$filter_user_name,$filter_result,$filter_language,$EndID,$StartID);
		}
		else
		{
			$this->view->Result=$SubmitCode->GetResult($filter_problem_id,$filter_user_name,$filter_result,$filter_language);
		}
    }
}

