�洢������������
proc��һ��ʹ�ô�д�������name
������������ڱ���Ļ����ϼ�ǰ׺i��o

���exitcode������mysql��׽�����쳣һ�����÷���ֵΪ1
��������ʱ����ֵΪ0
����������Ϊnull valueһ�����÷���ֵΪ-1

proc1 SELPROB -- ����ʹ��

proc2 CHPASSWD
���룺iuser_id INT UNSIGNED�����޸�������û�ID
	  iselectָʾ�޸ĺ����û���1Ϊuploader��2Ϊeojuser
eojlog opcode��ӦֵΪ2
oexitcode 	��Ӧ 0 ���ɹ����
			��Ӧ -1 ����������ģ�null value found
			��Ӧ 1 �� mysql error
			��Ӧ 2 ���������ֵ����
			��Ӧ 3 �� updateδ�ɹ���������mysql error��������δ�ҵ�����
��select��ʽ����oexitcodeֵ
			
proc3 ADDANS
���룺irun_uid INT UNSIGNED�������˵��û�ID
	  irun_pid INT UNSIGNED���������������������ID
eojlog opcode��ӦֵΪ3
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
��select��ʽ����orun_id��oexitcodeֵ

proc4 UPDATEANS
���룺irun_id INT UNSIGNED������ID
	  irun_pid INT UNSIGNED���������������ID
	  irun_uid INT UNSIGNED�������˵��û�ID
	  irun_mcost, irun_tcost, irun_codeloc, 
	  irun_codetype, irun_codel, irun_result
eojlog opcode��ӦֵΪ4
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
			��Ӧ 2 ������run��ʧ�ܣ�����mysql error��������δ�ҵ�����
			��Ӧ 3 ���������run������eojuserʧ�ܣ�ͬǰ������
			��Ӧ 4 ���������run��eojuser������problem��ʧ�ܣ�ͬǰ������
��select��ʽ����oexitcodeֵ

proc5 ADDUPER
���룺iuser_name VARCHAR(255)���û���
	  iuser_passwd CHAR(32)��MD5����32λ��������
	  iuser_info VARCHAR(128)���û��Զ�����Ϣ
	  iuser_privis TINYINT UNSIGNED���û�Ȩ����
eojlog opcode��ӦֵΪ5
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
��select��ʽ����ouploader_id, oexitcodeֵ

proc6 ADDEOJUSER
���룺iuser_name VARCHAR(255)��eojuser�û���
	  iuser_passwd CHAR(32)��eojuser md5���ܺ�����
eojlog opcode��ӦֵΪ6
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
��select��ʽ����oexitcodeֵ

proc7 UPLOAD
���룺iuploader_id��ip_title��ip_desc��ip_lang��
      ip_tlimt��ip_mlimt��ip_input_tips��ip_output_tips��
	  ip_sampleinput��ip_sampleoutput��ip_hint��ip_specjg
eojlog opcode��ӦֵΪ7
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��p_title�Ѿ���uploadit��problem_multilang����
��select��ʽ����oup_id��oexitcodeֵ

proc8 AUDITPROB
���룺iup_id, ijudge
eojlog opcode��ӦֵΪ8
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
			��Ӧ 2 ��handout_status �Ѿ�Ϊ0
			��Ӧ 3 ��handout_status unknown
			��Ӧ 4 �����»�ɾ������ʧ�ܣ�������δ�ҵ�����
��select��ʽ����oexitcodeֵ

proc9 UPDATEUP
���룺iup_id INT UNSIGNED,ip_title VARCHAR(128),ip_desc TEXT,
ip_lang VARCHAR(20),ip_tlimt INT UNSIGNED,ip_mlimt INT UNSIGNED,
ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),ip_sampleinput VARCHAR(1024),
ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024),ip_specjg BOOLEAN
eojlog opcode��ӦֵΪ9
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
			��Ӧ 2 ��p_title already existed
			��Ӧ 3 ��updateʱerror��������δ�ҵ�����
��select��ʽ����oexitcodeֵ

proc10 ADDPROB
���룺ip_tlimt INT UNSIGNED,ip_mlimt INT UNSIGNED,ip_specjg BOOLEAN,
iuploader_id INT UNSIGNED,ip_lang VARCHAR(20),ip_title VARCHAR(128),
ip_desc TEXT,ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),
ip_sampleinput VARCHAR(1024),ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024)
eojlog opcode��ӦֵΪ10
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
			��Ӧ 2 ��p_title already existed
��select��ʽ����op_id,oexitcodeֵ

proc11 ADDMULTIVER
���룺ip_id INT UNSIGNED,ip_lang VARCHAR(20),ip_title VARCHAR(128),
ip_desc TEXT,ip_input_tips VARCHAR(1024),ip_output_tips VARCHAR(1024),
ip_sampleinput VARCHAR(1024),ip_sampleoutput VARCHAR(1024),ip_hint VARCHAR(1024)
eojlog opcode��ӦֵΪ11
oexitcode	��Ӧ 0 ���ɹ����
			��Ӧ 1 ��mysql error
			��Ӧ -1 ��null value
			��Ӧ 2 ��p_title or p_lang already existed
			��Ӧ 3 ��update error��may data not found
��select��ʽ����oexitcodeֵ

