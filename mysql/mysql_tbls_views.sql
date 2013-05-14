set character_set_client=utf8;
set character_set_connection=utf8;
set character_set_database=utf8;
set character_set_results=utf8;
set character_set_server=utf8;

CREATE USER eojapp IDENTIFIED BY 'ecust';

CREATE DATABASE eojdb DEFAULT CHARSET=utf8;
GRANT all privileges on eojdb.* 
TO eojapp@'%';

use eojdb;

CREATE TABLE uploader
(
  uploader_id INT UNSIGNED PRIMARY KEY auto_increment COMMENT '上传者唯一对应ID',
  user_name VARCHAR(255) NOT NULL COMMENT '上传者用户名',
  user_passwd CHAR(32) NOT NULL DEFAULT 'c22c08f230c41ac47d20d0fc8c4eb35b' COMMENT '上传者密码初始为CS10,采用md5 32位加密',
  user_info VARCHAR(128) COMMENT '上传者自定义描述信息，可填入个人简介等信息',
  user_status TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '上传者用户是否可用标志，默认0为可用，其余值分别代表一种用户状态',
  user_privis TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '上传者权限划分, 0为无任何权限，eojadmin为255最高权限拥有者'
) ENGINE=INNODB COMMENT='上传者表，不提供对外开放注册，只提供内部添加接口';

CREATE TABLE uploadit
(
  up_id INT UNSIGNED auto_increment PRIMARY KEY COMMENT '对应唯一上传记录的上传号',
  uploader_id INT UNSIGNED NOT NULL COMMENT '对应提交题目的上传者ID',
  p_title VARCHAR(128)  NOT NULL DEFAULT '无标题' COMMENT '题目的标题',
  p_desc TEXT CHARACTER SET utf8 COMMENT '题目的描述段',
  p_lang VARCHAR(20) NOT NULL DEFAULT '简体中文' COMMENT '题目内容描述所用自然语言',
  p_tlimt INT UNSIGNED COMMENT '题目的时间性能要求，单位为MS',
  p_mlimt INT UNSIGNED COMMENT '题目的内存性能要求，单位为K',
  p_input_tips VARCHAR(1024) COMMENT '题目的输入格式帮助', 
  p_output_tips VARCHAR(1024) COMMENT '题目的输出格式帮助',
  p_sampleinput VARCHAR(1024) COMMENT '由出题人给出的题目的样例输入',
  p_sampleoutput VARCHAR(1024) COMMENT '由出题人给出的题目的样例输出',
  p_hint VARCHAR(1024) DEFAULT '无' COMMENT '题目的编程提示',
  p_specjg BOOLEAN NOT NULL DEFAULT false COMMENT '如果为true代表题目答案不唯一',
  handout_status TINYINT UNSIGNED NOT NULL DEFAULT 2 COMMENT '题目提交处理状态的标志，默认为2，即上传后待审核，1则为未通过，0为通过审核，审核通过后将会自动迁移记录到problem表',
  up_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '题目的上传时间',
  FOREIGN KEY(uploader_id) references uploader(uploader_id)
) ENGINE=INNODB COMMENT='上传记录表，每次上传均插入到本表，有审核通过的记录则移交到problem及相应附属表中';

CREATE TABLE problem
(
  p_id INT UNSIGNED auto_increment COMMENT '题目号对应唯一的题目',
  p_tlimt INT UNSIGNED COMMENT '题目的时间要求，单位为MS',
  p_mlimt INT UNSIGNED COMMENT '题目的内存要求，单位为K',
  p_langnum TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '题目的描述语种数目，默认为1',
  p_tsubmit INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '题目的答题总次数',
  p_acc INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '题目的答题总通过次数',
  p_specjg BOOLEAN NOT NULL DEFAULT false COMMENT '如果为true代表题目答案不唯一',
  p_create_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '题目创建时间，插入新纪录时赋予默认初值为当前时间（精确到秒）',
  p_author INT UNSIGNED NOT NULL COMMENT '题目作者即上传者ID',
  PRIMARY KEY(p_id),
  FOREIGN KEY(p_author) references uploader(uploader_id)
) ENGINE=INNODB COMMENT='题库表，保存题目的主体信息，其余描述性文本信息放置副表';

CREATE TABLE problem_multilang
(
  p_id INT UNSIGNED COMMENT '题目号对应唯一的题目',
  p_lang VARCHAR(20) NOT NULL DEFAULT '简体中文' COMMENT '题目描述所用自然语言，默认为简体中文，这里与题目号标志最终的唯一的一个题目版本',
  p_title VARCHAR(128) NOT NULL DEFAULT '无标题' COMMENT '题目标题',
  p_desc TEXT CHARACTER SET utf8 COMMENT '题目的内容描述',
  p_input_tips VARCHAR(1024) COMMENT '题目的输入格式帮助',
  p_output_tips VARCHAR(1024) COMMENT '题目的输出格式帮助',
  p_sampleinput VARCHAR(1024) COMMENT '题目的样例输入',
  p_sampleoutput VARCHAR(1024) COMMENT '题目的样例输出',
  p_hint VARCHAR(1024) COMMENT '题目的编程实现提示',
  PRIMARY KEY(p_id ,p_lang),
  FOREIGN KEY(p_id) references problem(p_id)
) ENGINE=INNODB COMMENT='题库副表，保存题目的文本型信息，允许多语言版本，由题目号和语言类别两个字段唯一标识';

CREATE TABLE eojuser
(
  user_id INT UNSIGNED auto_increment COMMENT '普通答题用户ID',
  user_name VARCHAR(20) NOT NULL COMMENT '用户名',
  user_passwd CHAR(32) NOT NULL DEFAULT 'e10adc3949ba59abbe56e057f20f883e' COMMENT '普通答题用户的密码，默认初始为123456，采用md5加密',
  user_tsubmit INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总答题次数（一个问题重复答题分别计算）',
  user_acc INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户的总通过次数',
  user_status TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '普通答题用户是否可用标志，默认0为可用，其余值分别代表一种用户状态',
  PRIMARY KEY (user_id)
) ENGINE=INNODB COMMENT='eoj普通答题用户表';

CREATE TABLE run
(
  run_id INT UNSIGNED auto_increment COMMENT '唯一标识一条答题记录的答题流水号',
  run_uid INT UNSIGNED NOT NULL COMMENT '答题人的用户ID',
  run_pid INT UNSIGNED NOT NULL COMMENT '对应问题的问题ID',
  run_submitt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '用户submit答案的时间，默认为当前时间（精确到秒）',
  run_mcost INT UNSIGNED COMMENT '用户答案所需内存消耗，单位为K',
  run_tcost INT UNSIGNED COMMENT '用户答案所需时间消耗，单位为MS',
  run_codeloc VARCHAR(255) COMMENT '用户的解决方案代码源文件所在路径',
  run_codetype TINYINT UNSIGNED COMMENT '用户的解决方案源代码类型, gcc c值为1',
  run_codel INT UNSIGNED COMMENT '用户的解决方案源代码文件长度，单位为B，最长1M',
  run_result SMALLINT COMMENT '用户代码执行结果，值为1则accepted',
  PRIMARY KEY(run_id),
  FOREIGN KEY(run_uid) references eojuser(user_id),
  FOREIGN KEY(run_pid) references problem(p_id)
) ENGINE=INNODB COMMENT='答题记录表，记录每一次答题的过程';

CREATE TABLE EOJLOG
(
  log_id BIGINT UNSIGNED auto_increment PRIMARY KEY COMMENT 'eojdb应用级别执行日志ID',
  log_genetime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '此条日志产生时间',
  opcode TINYINT UNSIGNED COMMENT '对应操作码',
  opmesg VARCHAR(1024) COMMENT '详细操作信息',
  op_tag TINYINT UNSIGNED COMMENT '操作标签：0 普通，1 error，2 warning'
) ENGINE=MYISAM COMMENT='eojdb应用日志，每个访问eojdb操作都应记录下相关信息到此日志表';
