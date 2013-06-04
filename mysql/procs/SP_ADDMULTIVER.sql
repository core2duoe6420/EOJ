DELIMITER //
CREATE PROCEDURE ADDMULTIVER(
IN ip_id INT UNSIGNED,
IN ip_lang VARCHAR(20),
IN ip_title VARCHAR(128),
IN ip_desc TEXT,
IN ip_input_tips VARCHAR(1024),
IN ip_output_tips VARCHAR(1024),
IN ip_sampleinput VARCHAR(1024),
IN ip_sampleoutput VARCHAR(1024),
IN ip_hint VARCHAR(1024))
proc11: BEGIN
  -- this proc help php add a record to multi-lang tbl
  DECLARE vp_title VARCHAR(128);
  DECLARE vp_lang VARCHAR(20);
  DECLARE vrow_c INT;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	ROLLBACK;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(11,CONCAT('failed add a multi-lang record ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF ip_id IS NULL
  THEN
    SET oexitcode=-1;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(11,CONCAT('failed because of a null pid'),2);
	COMMIT;
	
	LEAVE proc11;
  END IF;
  
  IF ip_title IS NULL or ip_title = '无标题'
  THEN
	SET vp_title='无标题';
  ELSE
    SET vp_title=ip_title;
	
	SELECT COUNT(*)
	INTO vrow_c
	FROM eojdb.problem_multilang a
	WHERE a.p_title = vp_title;
	
	-- judge whether it's unique title
	IF vrow_c != 0
	THEN
	  SET oexitcode=2;
	  SELECT oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	  VALUES(11, CONCAT('failed because of a same problem [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc11;
	END IF;	
  END IF;
  
  IF ip_lang IS NULL
  THEN
    SET oexitcode=-1;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(11,CONCAT('failed because of a null lang value'),2);
	COMMIT;
	
	LEAVE proc11;
  ELSE
    SET vp_lang=ip_lang;
	SET vrow_c=-1;
	
	SELECT count(*) 
	INTO vrow_c
	FROM problem_multilang
	WHERE p_id=ip_id
	AND p_lang=vp_lang;
	
	IF vrow_c != 0
	THEN
	  SET oexitcode=2;
	  SELECT oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
      VALUES(11,CONCAT('failed because of a same lang-version record existed'),2);
	  COMMIT;
	
	  LEAVE proc11;
	END IF;
  END IF;
  
  /* start execution */
  START TRANSACTION;
  INSERT INTO eojdb.problem_multilang(p_id, p_lang, p_title, p_desc, 
  p_input_tips, p_output_tips, p_sampleinput, p_sampleoutput, p_hint)
  VALUES(ip_id, vp_lang, vp_title, ip_desc, ip_input_tips, ip_output_tips,
         ip_sampleinput, ip_sampleoutput, ip_hint);
		 
  -- update the problem langnum value
  UPDATE eojdb.problem
  SET p_langnum=p_langnum+1
  WHERE p_id=ip_id;
  
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=3;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(11,CONCAT('failed to update a problem record because of data not found, check whether the pid is correct and existed'),2);
	COMMIT;
    
	LEAVE proc11;
  END IF;
  
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(11,CONCAT('successful add a multi-lang record that [p_id] ', ip_id, ', language version is ', ip_lang),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;