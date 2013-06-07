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
		$page_num=ceil(($id_max-$id_min+1)/20);
		$StartID=$id_min+($page-1)*20;
		$EndID=min($StartID+19,$id_max);
		
		$Result=$CheckedProblem->GetProblemList($StartID,$EndID);
		
		$this->view->Result=$Result;
		$this->view->Page_Num=$page_num;
    }

    public function detailAction()
    {
        // action body
		$p_id=$this->_request->getParam('p_id');
		if(!isset($p_id))
			$this->view->errormsg='无此道题目';
		else
		{
			$Problem=new EOJ_Model_CheckedProblem();
			if($Problem->SetProblemID($p_id))
			{
			$this->view->p_id=$p_id;
			$this->view->p_title=$Problem->GetproblemName();
			$this->view->p_tlimt=$Problem->GettimeLimit();
			$this->view->p_mlimt=$Problem->GetmemoryLimit();
			$this->view->p_disc=$Problem->Getdiscription();
			$this->view->p_sample_input=$Problem->GetsampleInput();
			$this->view->p_sample_output=$Problem->GetsampleOutput();
			$this->view->p_source=$Problem->GetSource();
			$this->view->p_input_tips=$Problem->GetinputTips();
			$this->view->p_output_tips=$Problem->GetoutputTips();
			$this->view->p_hint=$Problem->GetHint();
			}
			else
				$this->view->errormsg='无此道题目';
		}
		
    }

    public function uncheckedAction()
    {
        // action body
		$user_power=$this->getRequest()->getCookie('user_power');
		if(!isset($user_power))
				$this->view->errormsg='对不起，您没有访问此页面的权限';
		else
		{
			$problemList=new EOJ_Model_UnCheckedProblem();
			switch($user_power)
			{
				case 1: 
					$this->view->ifPP=true;
					$this->view->Result=$problemList->PPGetProblemList($this->getRequest()->getCookie('user_id'));
					break;
				case 2:
					$this->view->ifPP=false;
					if($this->_request->getParam('page'))
						$page=$this->_request->getParam('page');
					else
						$page=1;
					$id_min=$problemList->PCGetMinProblemID();
					$id_max=$problemList->PCGetMaxProblemID();
					$page_num=ceil(($id_max-$id_min+1)/20);
					$EndID=$id_max-($page-1)*20;
					$StartID=max($EndID-19,$id_min);
					
					$this->view->Page_Num=$page_num;
					$this->view->Result=$problemList->PCGetProblemList($StartID,$EndID);
					break;
				case 255:
					break;
				default:
			}
		}		
    }
}





