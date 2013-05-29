<?php

class EOJ_Form_User extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		
		//user_id INT UNSIGNED auto_increment COMMENT '普通答题用户ID',
		$user_id=$this->createElement('text','UserID');
		$user_id->setLabel('User ID');
		$user_id->setRequired(true);
		$this->addElement($user_id);
		//user_name VARCHAR(20) NOT NULL COMMENT '用户名',
		$user_name=$this->createElement('text','UserName');
		$user_name->setLabel('User Name');
		$user_name->setRequired(true);
		$this->addElement($user_name);
		//user_passwd CHAR(32) NOT NULL DEFAULT 'e10adc3949ba59abbe56e057f20f883e' COMMENT '普通答题用户的密码，默认初始为123456，采用md5加密',
		$user_passwd =$this->createElement('password','UserPassword');
		$user_passwd->setLabel('User Password');
		$user_passwd->setRequired(true);
		$this->addElement($user_passwd);
		$user_passwd2=$this->createElement('password','UserPassword2');
		$user_passwd2->setLabel('User Password2');
		$user_passwd2->addValidator('identical',false,array('token'=>'UserPassword'));
		$user_passwd2->addErrorMessage('Passwords do not match');
		$this->addElement($user_passwd2);
		//user_tsubmit INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总答题次数（一个问题重复答题分别计算）',
		//user_acc INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总通过次数',
		//user_status TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '普通答题用户是否可用标志，默认0为可用，其余值分别代表一种用户状态',
		
		
		$submit=$this->createElement('submit','提交');
		$this->addElement($submit);
    }


}
