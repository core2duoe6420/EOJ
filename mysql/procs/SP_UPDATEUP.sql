DELIMITER //
CREATE PROCEDURE UPDATEUP(
IN iup_id INT UNSIGNED,
IN ip_title VARCHAR(128),
IN ip_desc TEXT,
IN ip_lang VARCHAR(20),
IN ip_tlimt INT UNSIGNED,
IN ip_mlimt INT UNSIGNED,
IN ip_input_tips VARCHAR(1024),
IN ip_output_tips VARCHAR(1024),
IN ip_sampleinput VARCHAR(1024),
IN ip_sampleoutput VARCHAR(1024),
IN ip_hint VARCHAR(1024),
IN ip_specjg BOOLEAN)
proc9: BEGIN
  -- this proc help php update the upload problem message
  DECLARE vrow_c INT;
  DECLARE vp_lang VARCHAR(20);
  DECLARE vp_specjg BOOLEAN;
  DECLARE vp_title VARCHAR(128);
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
    VALUES(9,CONCAT('failed update a uploadit record details info ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  SET vrow_c=-1;
  
  -- judge whether exist null value
  IF iup_id IS NULL
  THEN
    SET oexitcode=-1;
	SELECT oexitcode;
	
    -- generate log
	INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	VALUES(9, CONCAT('failed because of a null value'), 1);
	COMMIT;
	
	LEAVE proc9;
  END IF;
  
  IF ip_title IS NULL OR ip_title='无标题'
  THEN
	SET vp_title='无标题';
  ELSE
    SET vp_title=ip_title;
	
	SELECT COUNT(*) 
	INTO vrow_c
	FROM eojdb.UPLOADIT a
	WHERE a.p_title = vp_title;
	
	-- judge whether it's unique title
	IF vrow_c != 0
	THEN
	  SET oexitcode=2;
	  SELECT oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	  VALUES(9, CONCAT('failed because of a same upload [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc9;
	END IF;
	
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
	  VALUES(9, CONCAT('failed because of a same problem [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc9;
	END IF;	
  END IF;
  
  IF ip_lang IS NULL
  THEN
    SET vp_lang='简体中文';
  ELSE
    SET vp_lang=ip_lang;
  END IF;
  
  IF ip_specjg IS NULL
  THEN
    SET vp_specjg=false;
  ELSE
    SET vp_specjg=ip_specjg;
  END IF; 
  
  /* start execution */
  START TRANSACTION;
  UPDATE eojdb.UPLOADIT
  SET p_title=vp_title, p_desc=ip_desc, p_lang=vp_lang, p_tlimt=ip_tlimt,
      p_mlimt=ip_mlimt, p_input_tips=ip_input_tips, p_output_tips=ip_output_tips,
	  p_sampleinput=ip_sampleinput, p_sampleoutput=ip_sampleoutput, p_hint=ip_hint,p_specjg=vp_specjg
  WHERE up_id=iup_id;

  -- judge whether it is work ok
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=3;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(9,CONCAT('failed to update a uploadit record because of update data not found error, check whether the input judge and id is correct and existed'),2);
	COMMIT;
	
	LEAVE proc9;
  END IF;
  
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(9,CONCAT('successful update a uploadit record that [up_id] ', iup_id, ', some message about this up_problem changed.'),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;