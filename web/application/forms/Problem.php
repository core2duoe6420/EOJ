<?php

class EOJ_Form_Problem extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		//p_id INT UNSIGNED auto_increment COMMENT '题目号对应唯一的题目',
		$p_id=$this->createElement('text','ProblemID');
		$p_id->setLabel('题目号:');
		$this->addElement($p_id);
		
		//p_title VARCHAR(128) NOT NULL DEFAULT '无标题' COMMENT '题目标题',
		$p_title=$this->createElement('text','ProblemTitle');
		$p_title->setLabel('题目标题:');
		$p_title->setRequired(true);
		$this->addElement($p_title);
		
		//p_desc TEXT CHARACTER SET utf8 COMMENT '题目的内容描述',
		$p_desc=$this->createElement('textarea','ProblemDesc');
		$p_desc->setLabel('题目描述:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_desc);
		
		//p_lang VARCHAR(20) NOT NULL DEFAULT '简体中文' COMMENT '题目描述所用自然语言，默认为简体中文，这里与题目号标志最终的唯一的一个题目版本',
		$p_lang=$this->createElement('select','ProblemLang');
		$p_lang->setLabel('题目描述语言:');
		$p_lang->addMultiOptions(array(
			'简体中文'=>'简体中文',
			'English'=>'English'
			));
		$p_lang->setRequired(true);
		$this->addElement($p_lang);
		
		//p_tlimt INT UNSIGNED COMMENT '题目的时间要求，单位为MS',
		$p_tlimt=$this->createElement('text','ProblemTimelimit');
		$p_tlimt->setLabel('时间限制:');
		$this->addElement($p_tlimt);
		
		//p_mlimt INT UNSIGNED COMMENT '题目的内存要求，单位为K',
		$p_mlimt=$this->createElement('text','ProblemMemorylimit');
		$p_mlimt->setLabel('内存限制:');
		$this->addElement($p_mlimt);
		
		
		//file
		$p_inputfile=$this->createElement('file','ProblemInputFile');
		$p_inputfile->setLabel('输入文件:');
		$this->addElement($p_inputfile);
		$p_outputfile=$this->createElement('file','ProblemOutputFile');
		$p_outputfile->setLabel('输出文件:');
		$this->addElement($p_outputfile);
		
		//p_input_tips VARCHAR(1024) COMMENT '题目的输入格式帮助',
		$p_input_tips=$this->createElement('textarea','ProblemInputTips');
		$p_input_tips->setLabel('输入格式:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_input_tips);
		
		//p_output_tips VARCHAR(1024) COMMENT '题目的输出格式帮助',
		$p_output_tips=$this->createElement('textarea','ProblemOutputTips');
		$p_output_tips->setLabel('输出格式:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_output_tips);
		
		//p_sampleinput VARCHAR(1024) COMMENT '题目的样例输入',
		$p_sampleinput=$this->createElement('textarea','ProblemInputSample');
		$p_sampleinput->setLabel('输入样例:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_sampleinput);
		
		//p_sampleoutput VARCHAR(1024) COMMENT '题目的样例输出',
		$p_sampleoutput=$this->createElement('textarea','ProblemOutputSample');
		$p_sampleoutput->setLabel('输出样例:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_sampleoutput);
		//p_hint VARCHAR(1024) COMMENT '题目的编程实现提示',
		
		$p_hint=$this->createElement('textarea','ProblemHint');
		$p_hint->setLabel('问题提示:')
				->setAttrib('COLS', '40')
				->setAttrib('ROWS', '4');
		$this->addElement($p_hint);
		
		//p_specjg BOOLEAN NOT NULL DEFAULT false COMMENT '如果为true代表题目答案不唯一',
		$p_specjg=$this->createElement('checkbox','ProblemSpecJudge');
		$p_specjg->setLabel('是否有特殊答案?');
		$this->addElement($p_specjg);
		
		//submit
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}

