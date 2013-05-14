INSERT INTO uploader(user_name,user_passwd,user_info,user_privis)
VALUES('eojadmin',md5('ecust'),'the eoj system super user',255);

COMMIT;

INSERT INTO uploader(user_name,user_info,user_privis)
VALUES('public','the eoj system public user,default passwd 123456',1);

COMMIT;

INSERT INTO problem(p_author)
VALUES(1);
INSERT INTO problem_multilang(p_id)
VALUES(1);
COMMIT;
