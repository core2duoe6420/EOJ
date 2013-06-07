<?php

class EOJ_Form_User extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		
		
		//user_name VARCHAR(20) NOT NULL COMMENT '用户名',
		$user_name=$this->createElement('text','UserName');
		$user_name->setAllowEmpty(false);
		$user_name->addErrorMessage('User Name Empty');
		$user_name->setLabel('登录名:');
		$this->addElement($user_name);
		
		//user_passwd CHAR(32) NOT NULL DEFAULT 'e10adc3949ba59abbe56e057f20f883e' COMMENT '普通答题用户的密码，默认初始为123456，采用md5加密',
		$user_passwd =$this->createElement('password','UserPassword');
		$user_passwd->setAllowEmpty(false);
		$user_passwd->setRequired(true);
		$user_passwd->addErrorMessage('Password Empty');
		$user_passwd->setLabel('登录密码:');
		$this->addElement($user_passwd);
		$user_passwd2=$this->createElement('password','UserPassword2');
		$user_passwd2->setLabel('再输一次:');
		$user_passwd2->addValidator('identical',false,array('token'=>'UserPassword'));
		$user_passwd2->addErrorMessage('Passwords do not match');
		$this->addElement($user_passwd2);
		
		$admin_type=$this->createElement('select','AdminType');
		$admin_type->setlabel('管理员角色:');
		$admin_type->addMultiOptions(
			array(
				'1'=>'题目上传者',
				'2'=>'题目审核者',
				'255'=>'系统管理员'
			)
		);
		$this->addElement($admin_type);
		
		//user_tsubmit INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总答题次数（一个问题重复答题分别计算）',
		//user_acc INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总通过次数',
		//user_status TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '普通答题用户是否可用标志，默认0为可用，其余值分别代表一种用户状态',
		
		
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}

