<?php

class UnCheckedProblemController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
		$user_power=$this->getRequest()->getCookie('user_power');
		if(empty($user_power))
				$this->view->errormsg='Sorry, you do not have the permission to visit this page';
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
					$page_num=ceil(($id_max-$id_min+1)/50);
					$EndID=$id_max-($page-1)*50;
					$StartID=max($EndID-49,$id_min);
					
					$this->view->Page_Num=$page_num;
					$this->view->Result=$problemList->PCGetProblemList($StartID,$EndID);
					break;
				case 255:
					break;
				default:
			}
		}		
    }

    public function detailAction()
    {
        // action body
    }

    public function updateAction()
    {
        // action body
		
		if((1!=$this->getRequest()->getCookie('user_power'))&&(255!=$this->getRequest()->getCookie('user_power')))
			$this->view->errormsg='Sorry, you do not have the permission to visit this page';
		else
		{
			$form_problem=new EOJ_Form_Problem();
			$form_problem->removeElement('ProblemID');
			$form_problem->removeElement('ProblemInputFile');
			$form_problem->removeElement('ProblemOutputFile');
		//	$form_problem->removeElement('ProblemLang');
		//	$form_problem->removeElement('ProblemSpecJudge');
				
			$up_id=$this->_request->getParam('up_id');
			if(!isset($up_id))
				$this->view->errormsg='No such Problem';
			else
			{
				$problem= new EOJ_Model_UnCheckedProblem();
				if(!$problem->SetUploadID($up_id))
					$this->view->errormsg='No such Problem';
				else
				{
					$form_problem->getElement('ProblemTitle')->setValue($problem->GetproblemName());
					$form_problem->getElement('ProblemDesc')->setValue($problem->Getdiscription());
					$form_problem->getElement('ProblemTimelimit')->setValue($problem->GettimeLimit());
					$form_problem->getElement('ProblemMemorylimit')->setValue($problem->GetmemoryLimit());
					$form_problem->getElement('ProblemInputTips')->setValue($problem->GetinputTips());
					$form_problem->getElement('ProblemOutputTips')->setValue($problem->GetoutputTips());
					$form_problem->getElement('ProblemInputSample')->setValue($problem->GetsampleInput());
					$form_problem->getElement('ProblemOutputSample')->setValue($problem->GetsampleOutput());
					$form_problem->getElement('ProblemHint')->setValue($problem->GetHint());
					$form_problem->getElement('ProblemLang')->setValue($problem->Getlanguage());
					$form_problem->getElement('ProblemSpecJudge')->setValue($problem->GetSpecialJudge());
					
	/*return info by SetUploadID list 
	GetUpTime()
	GethandoutStatus()
	GetuploaderID()
	GetuploadID()
	GetproblemName()
	GettimeLimit()
	GetmemoryLimit()
	Getdiscription()
	GetsampleInput()
	GetsampleOutput()
	GetSource()
	GetinputTips()
	GetoutputTips()
	GetHint()
	*/
			
					if($this->getRequest()->isPost())
					{
						if($form_problem->isValid($_POST))
						{
							$problem_data=$form_problem->getValues();
					
							//update problem cannot get enough information from SetUploadID
						
							$uploader=new EOJ_Model_ProblemPublisher();
					
							//$iuploader_id=$this->getRequest()->getCookie('user_id');
							$up_id=$this->getRequest()->getParam('up_id');
							$ip_title=$problem_data['ProblemTitle'];
							$ip_desc=$problem_data['ProblemDesc'];
							$ip_lang=$problem_data['ProblemLang'];
							$ip_tlimt=$problem_data['ProblemTimelimit'];
							$ip_mlimt=$problem_data['ProblemMemorylimit'];
							$ip_input_tips=$problem_data['ProblemInputTips'];
							$ip_output_tips=$problem_data['ProblemOutputTips'];
							$ip_sampleinput=$problem_data['ProblemInputSample'];
							$ip_sampleoutput=$problem_data['ProblemOutputSample'];
							$ip_hint=$problem_data['ProblemHint'];
							$ip_specjg=$problem_data['ProblemSpecJudge'];
							//proc requires up_id not uploader_id
							//
							$result=$uploader->UpdateUploadedProblem($up_id,$ip_title,$ip_desc,$ip_lang,$ip_tlimt,$ip_mlimt,$ip_input_tips,$ip_output_tips,$ip_sampleinput,$ip_sampleoutput,$ip_hint,$ip_specjg);
							
							switch($result)
							{
								case 0:
									$this->view->errormsg='Update Success';
									break;
								case 1:
									$this->view->errormsg='Sql Error';
									break;
								case -1:
									$this->view->errormsg='Null Value';
									break;
								case 2:
									$this->view->errormsg='Same title exists';
									break;
								case 3:
									$this->view->errormsg='Update error';
									break;
							}
						
						}
					}
				$this->view->form_problem=$form_problem;
				}
			}
		}
    }

    public function submitAction()
    {
        // action body
		if((1!=$this->getRequest()->getCookie('user_power'))&&(255!=$this->getRequest()->getCookie('user_power')))
			$this->view->errormsg='Sorry, you do not have the permission to visit this page';
		else
		{
			$form_problem=new EOJ_Form_Problem();
			$form_problem->removeElement('ProblemID');
			$form_problem->removeElement('ProblemInputFile');
			$form_problem->removeElement('ProblemOutputFile');
		
	
			if($this->getRequest()->isPost())
			{
				if($form_problem->isValid($_POST))
				{
					$problem_data=$form_problem->getValues();
					
					$uploader=new EOJ_Model_ProblemPublisher();
					
					$iuploader_id=$this->getRequest()->getCookie('user_id');
					$ip_title=$problem_data['ProblemTitle'];
					$ip_desc=$problem_data['ProblemDesc'];
					$ip_lang=$problem_data['ProblemLang'];
					$ip_tlimt=$problem_data['ProblemTimelimit'];
					$ip_mlimt=$problem_data['ProblemMemorylimit'];
					$ip_input_tips=$problem_data['ProblemInputTips'];
					$ip_output_tips=$problem_data['ProblemOutputTips'];
					$ip_sampleinput=$problem_data['ProblemInputSample'];
					$ip_sampleoutput=$problem_data['ProblemOutputSample'];
					$ip_hint=$problem_data['ProblemHint'];
					$ip_specjg=$problem_data['ProblemSpecJudge'];
				
					$result=$uploader->uploadproblem($iuploader_id,$ip_title,$ip_desc,$ip_lang,$ip_tlimt,$ip_mlimt,$ip_input_tips,$ip_output_tips,$ip_sampleinput,$ip_sampleoutput,$ip_hint,$ip_specjg);
					
				echo $result;
					switch($result)
					{
						case 0:
							$this->view->errormsg='Database Error';
							break;
						case -2:
							$this->view->errormsg='Same Title Exists';
							break;
						default:
							$this->_redirect("/Un-Checked-Problem");
					}
				}
			}
		$this->view->form_problem=$form_problem;
		}
    }

    public function judgeAction()
    {
        // action body
		$user_power=$this->getRequest()->getCookie('user_power');
		if(!isset($user_power))
				$this->view->errormsg='Sorry, you do not have the permission to visit this page';
		else
		{
			if($this->getRequest()->isPost())
			{
				$decision=$this->getRequest()->getPost('decision');
				if($decision)
				{
					$checker=new EOJ_Model_ProblemChecker();
					if($decision=='Accept')
					{
						switch($checker->CheckProblem($this->_request->getParam('up_id'),1))
						{
							case 0:break;
							case 1:$this->view->errormsg='Database error';break;
							case -1:$this->view->errormsg='No such problem';break;
							case 2:$this->view->errormsg='Already checked';break;
							case 3:$this->view->errormsg='Unknown decision';break;
							case 4:$this->view->errormsg='Update status fail';break;
						}
					}
					else if($decision=='Decline')
					{
						switch($checker->CheckProblem($this->_request->getParam('up_id'),0))
						{
							case 0:break;
							case 1:$this->view->errormsg='Database error';break;
							case -1:$this->view->errormsg='No such problem';break;
							case 2:$this->view->errormsg='Already checked';break;
							case 3:$this->view->errormsg='Unknown decision';break;
							case 4:$this->view->errormsg='Update status fail';break;
						}
					}
				}
			}
			
			switch($user_power)
			{
				case 1: 
					$this->view->ifPP=true;
					break;
				case 2:
					$this->view->ifPP=false;
					break;
				case 255:
					$this->view->ifPP=false;
					break;
				default:
			}
				
		
			$up_id=$this->_request->getParam('up_id');
			if(!isset($up_id))
				$this->view->errormsg='No such problem';
			else
			{
				$Problem=new EOJ_Model_UnCheckedProblem();
				if(!$Problem->SetUploadID($up_id))
					$this->view->errormsg='No such problem';
				else
				{
			/*
	GetUpTime()
	GethandoutStatus()
	GetuploaderID()
	GetuploadID()
	GetproblemName()
	GettimeLimit()
	GetmemoryLimit()
	Getdiscription()
	GetsampleInput()
	GetsampleOutput()
	GetSource()
	GetinputTips()
	GetoutputTips()
	GetHint()*/
					switch($Problem->GethandoutStatus())
					{
						case 0:
							$this->view->p_handoutstatus='已通过';
							$this->view->ifjudge=true;
							break;
						case 1:
							$this->view->p_handoutstatus='未通过';
							$this->view->ifjudge=true;
							break;
						case 2:
							$this->view->p_handoutstatus='待审核';
							$this->view->ifjudge=false;
							break;
					}
					$this->view->up_id=$Problem->GetuploadID();
					$this->view->p_uploaderID=$Problem->GetuploaderID();
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
			}
		}
    }


}











