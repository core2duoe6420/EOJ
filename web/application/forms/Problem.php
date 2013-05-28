<?php

class EOJ_Form_Problem extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		//p_id INT UNSIGNED auto_increment COMMENT '题目号对应唯一的题目',
		$p_id=$this->createElement('text','ProblemID');
		$p_id->setLabel('Problem ID:');
		$this->addElement($p_id);
		
		//p_tlimt INT UNSIGNED COMMENT '题目的时间要求，单位为MS',
		$p_tlimt=$this->createElement('text','ProblemTimelimit');
		$p_tlimt->setLabel('Time Limit:');
		$this->addElement($p_tlimt);
		//p_mlimt INT UNSIGNED COMMENT '题目的内存要求，单位为K',
		$p_mlimt=$this->createElement('text','ProblemMemorylimit');
		$p_mlimt->setLabel('Memory Limit:');
		$this->addElement($p_mlimt);
		//p_specjg BOOLEAN NOT NULL DEFAULT false COMMENT '如果为true代表题目答案不唯一',
		$p_specjg=$this->createElement('checkbox','ProblemSpecJudge');
		$p_specjg->setLabel('Have Special Judgement?');
		$this->addElement($p_specjg);
		
		
		//file
		$p_inputfile=$this->createElement('file','ProblemInputFile');
		$p_inputfile->setLabel('Input File:');
		$this->addElement($p_inputfile);
		$p_outputfile=$this->createElement('file','ProblemOutputFile');
		$p_outputfile->setLabel('Output File:');
		$this->addElement($p_outputfile);
		
		//p_lang VARCHAR(20) NOT NULL DEFAULT '简体中文' COMMENT '题目描述所用自然语言，默认为简体中文，这里与题目号标志最终的唯一的一个题目版本',
		$p_lang=$this->createElement('select','ProblemLang');
		$p_lang->setLabel('Problem Description Language:');
		$p_lang->addMultiOptions(array(
			'en'=>'English',
			'zh-cn'=>'简体中文'
			));
		$p_lang->setRequired(true);
		$this->addElement($p_lang);
		//p_title VARCHAR(128) NOT NULL DEFAULT '无标题' COMMENT '题目标题',
		$p_title=$this->createElement('text','ProblemTitle');
		$p_title->setLabel('Problem Title:');
		$p_title->setRequired(true);
		$this->addElement($p_title);
		//p_desc TEXT CHARACTER SET utf8 COMMENT '题目的内容描述',
		$p_desc=$this->createElement('textarea','ProblemDesc');
		$p_desc->setLabel('Problem Description:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_desc);
		//p_input_tips VARCHAR(1024) COMMENT '题目的输入格式帮助',
		$p_input_tips=$this->createElement('textarea','ProblemInputTips');
		$p_input_tips->setLabel('Problem Input Tips:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_input_tips);
		//p_output_tips VARCHAR(1024) COMMENT '题目的输出格式帮助',
		$p_output_tips=$this->createElement('textarea','ProblemOutputTips');
		$p_output_tips->setLabel('Problem Output Tips:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_output_tips);
		//p_sampleinput VARCHAR(1024) COMMENT '题目的样例输入',
		$p_sampleinput=$this->createElement('textarea','ProblemInputSample');
		$p_sampleinput->setLabel('Problem Input Sample:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_sampleinput);
		//p_sampleoutput VARCHAR(1024) COMMENT '题目的样例输出',
		$p_sampleoutput=$this->createElement('textarea','ProblemOutputSample');
		$p_sampleoutput->setLabel('Problem Output Sample:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_sampleoutput);
		//p_hint VARCHAR(1024) COMMENT '题目的编程实现提示',
		$p_hint=$this->createElement('textarea','ProblemHint');
		$p_hint->setLabel('Problem Hint:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_hint);
		
		//submit
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}

