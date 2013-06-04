DELIMITER //
CREATE PROCEDURE SELPROB(
IN ip_id INT UNSIGNED, IN ip_lang VARCHAR(20) CHARACTER SET utf8, 
OUT op_title VARCHAR(128) CHARACTER SET utf8, OUT op_desc TEXT CHARACTER SET utf8, 
OUT op_lang VARCHAR(20) CHARACTER SET utf8, OUT op_langnum TINYINT UNSIGNED,
OUT op_tlimt INT UNSIGNED, OUT op_mlimt INT UNSIGNED, 
OUT op_input_tips VARCHAR(1024) CHARACTER SET utf8, OUT op_output_tips VARCHAR(1024) CHARACTER SET utf8, 
OUT op_sampleinput VARCHAR(1024) CHARACTER SET utf8, OUT op_sampleoutput VARCHAR(1024) CHARACTER SET utf8,
OUT op_hint VARCHAR(1024) CHARACTER SET utf8, OUT op_specjg BOOLEAN, 
OUT op_create_time TIMESTAMP, OUT op_tsubmit INT UNSIGNED, 
OUT op_acc INT UNSIGNED, OUT op_author INT UNSIGNED,
OUT oexitcode TINYINT)
proc1: BEGIN
  -- will not use any more

  /*declare variables*/
  DECLARE vp_id INT UNSIGNED;
  DECLARE vrow_c INT;
  DECLARE vp_lang VARCHAR(20) CHARACTER SET utf8;

  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	ROLLBACK;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed return a problem-full-info record that [p_id] ',ip_id, 'When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF ip_id IS NULL
  THEN
    ROLLBACK;
    SET oexitcode=-1;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(3,CONCAT('failed because of a null pid'),2);
	COMMIT;
	
	LEAVE proc1;
  ELSE
    SET vp_id=ip_id;
  END IF;
  
  IF ip_lang IS NULL
  THEN
    SET vp_lang='简体中文';
  ELSE
    SET vp_lang=ip_lang;
  END IF;
  
  /* start execution */
  SET vrow_c=-1;
  
  SELECT b.p_title, b.p_desc, b.p_lang, a.p_langnum, a.p_tlimt, a.p_mlimt, 
         b.p_input_tips, b.p_output_tips, b.p_sampleinput, b.p_sampleoutput,
		 b.p_hint, a.p_specjg, a.p_create_time, a.p_tsubmit, a.p_acc, a.p_author
  INTO   op_title, op_desc, op_lang, op_langnum, op_tlimt, op_mlimt, op_input_tips, op_output_tips,
         op_sampleinput, op_sampleoutput, op_hint, op_specjg,
		 op_create_time,op_tsubmit,op_acc,op_author
  FROM problem a, problem_multilang b
  WHERE a.p_id = b.p_id 
  AND b.p_lang = vp_lang
  AND a.p_id = vp_id;
  
  -- judge whether the select statement is ok
  SELECT found_rows() INTO vrow_c;
  
  IF vrow_c <> 1
  THEN
    ROLLBACK;
    SET oexitcode=2;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(3,CONCAT('failed because of data not found, check whether the [p_id] ', ip_id, 'or [p_lang]', ip_lang, 'is correct and existed'),2);
	COMMIT;
	
	LEAVE proc1;
  END IF;
	
  -- set exitcode to php user
  SET oexitcode=0;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(1,CONCAT('successful return a problem-full-info record that [p_id] ',ip_id),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;