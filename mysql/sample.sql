TRUNCATE TABLE eojdb.run;
TRUNCATE TABLE eojdb.problem_multilang;
TRUNCATE TABLE eojdb.problem;
TRUNCATE TABLE eojdb.eojuser;

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('test',md5('test'));

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('wjx',md5('wjx'));

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('jhh',md5('jhh'));

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('gyf',md5('gyf'));

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('jhl',md5('jhl'));

INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('hjw',md5('hjw'));

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,65536,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,65536,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,65536,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,65536,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,65536,1);

insert into problem_multilang(p_id,p_lang,p_title)
values(1,1,'A+B问题');

insert into problem_multilang(p_id,p_lang,p_title)
values(2,1,'求平均工资');

insert into problem_multilang(p_id,p_lang,p_title)
values(3,1,'字符串求和');

insert into problem_multilang(p_id,p_lang,p_title)
values(4,1,'最小公约数');

insert into problem_multilang(p_id,p_lang,p_title)
values(5,1,'测试问题');

INSERT INTO run(run_uid,run_pid)
VALUES(1,1);

INSERT INTO run(run_uid,run_pid)
VALUES(2,2);

INSERT INTO run(run_uid,run_pid)
VALUES(3,3);

INSERT INTO run(run_uid,run_pid)
VALUES(4,4);

INSERT INTO run(run_uid,run_pid)
VALUES(5,5);
