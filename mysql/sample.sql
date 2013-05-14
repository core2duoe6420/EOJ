INSERT INTO eojdb.eojuser(user_name,user_passwd)
VALUES('test',md5('test'));

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,1000,1);

INSERT INTO run(run_uid,run_pid)
VALUES(1,1);
