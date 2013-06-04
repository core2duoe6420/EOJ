存储过程命名规则：
proc名一律使用大写，表意的name
输入输出变量在本意的基础上加前缀i或o

输出exitcode在遇到mysql捕捉到的异常一律设置返回值为1
正常结束时返回值为0
因输入数据为null value一律设置返回值为-1

proc1 SELPROB -- 不再使用

proc2 CHPASSWD
输入：iuser_id INT UNSIGNED即需修改密码的用户ID
	  iselect指示修改何类用户，1为uploader，2为eojuser
eojlog opcode对应值为2
oexitcode 	对应 0 即成功完成
			对应 -1 即（不允许的）null value found
			对应 1 即 mysql error
			对应 2 即输入参数值有误
			对应 3 即 update未成功，但不算mysql error，可能是未找到数据
以select形式返回oexitcode值
			
proc3 ADDANS
输入：irun_uid INT UNSIGNED即答题人的用户ID
	  irun_pid INT UNSIGNED即答题人所答问题的问题ID
eojlog opcode对应值为3
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
以select形式返回orun_id、oexitcode值

proc4 UPDATEANS
输入：irun_id INT UNSIGNED即答题ID
	  irun_pid INT UNSIGNED即所答问题的问题ID
	  irun_uid INT UNSIGNED即答题人的用户ID
	  irun_mcost, irun_tcost, irun_codeloc, 
	  irun_codetype, irun_codel, irun_result
eojlog opcode对应值为4
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
			对应 2 即更新run表失败，不算mysql error，可能是未找到数据
			对应 3 即更新完成run表后更新eojuser失败，同前面理由
			对应 4 即更新完成run和eojuser表后更新problem表失败，同前面理由
以select形式返回oexitcode值

proc5 ADDUPER
输入：iuser_name VARCHAR(255)即用户名
	  iuser_passwd CHAR(32)即MD5加密32位长度密文
	  iuser_info VARCHAR(128)即用户自定义信息
	  iuser_privis TINYINT UNSIGNED即用户权限码
eojlog opcode对应值为5
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
以select形式返回ouploader_id, oexitcode值

proc6 ADDEOJUSER
输入：iuser_name VARCHAR(255)即eojuser用户名
	  iuser_passwd CHAR(32)即eojuser md5加密后密文
eojlog opcode对应值为6
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
以select形式返回oexitcode值

proc7 UPLOAD
输入：iuploader_id、ip_title、ip_desc、ip_lang、
      ip_tlimt、ip_mlimt、ip_input_tips、ip_output_tips、
	  ip_sampleinput、ip_sampleoutput、ip_hint、ip_specjg
eojlog opcode对应值为7
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即p_title已经在uploadit、problem_multilang出现
以select形式返回oup_id、oexitcode值

proc8 AUDITPROB
输入：iup_id, ijudge
eojlog opcode对应值为8
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
			对应 2 即handout_status 已经为0
			对应 3 即handout_status unknown
			对应 4 即更新或删除数据失败，可能是未找到数据
以select形式返回oexitcode值

proc9 UPDATEUP
输入：iup_id INT UNSIGNED,ip_title VARCHAR(128),ip_desc TEXT,
ip_lang VARCHAR(20),ip_tlimt INT UNSIGNED,ip_mlimt INT UNSIGNED,
ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),ip_sampleinput VARCHAR(1024),
ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024),ip_specjg BOOLEAN
eojlog opcode对应值为9
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
			对应 2 即p_title already existed
			对应 3 即update时error，可能是未找到数据
以select形式返回oexitcode值

proc10 ADDPROB
输入：ip_tlimt INT UNSIGNED,ip_mlimt INT UNSIGNED,ip_specjg BOOLEAN,
iuploader_id INT UNSIGNED,ip_lang VARCHAR(20),ip_title VARCHAR(128),
ip_desc TEXT,ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),
ip_sampleinput VARCHAR(1024),ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024)
eojlog opcode对应值为10
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
			对应 2 即p_title already existed
以select形式返回op_id,oexitcode值

proc11 ADDMULTIVER
输入：ip_id INT UNSIGNED,ip_lang VARCHAR(20),ip_title VARCHAR(128),
ip_desc TEXT,ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),
ip_sampleinput VARCHAR(1024),ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024)
eojlog opcode对应值为11
oexitcode	对应 0 即成功完成
			对应 1 即mysql error
			对应 -1 即null value
			对应 2 即p_title or p_lang already existed
			对应 3 即update error，may data not found
以select形式返回oexitcode值

