DELIMITER //
CREATE TRIGGER T_UPPROB AFTER UPDATE ON eojdb.uploadit
FOR EACH ROW 
BEGIN
	IF NEW.handout_status = 0
	THEN
	    INSERT INTO eojdb.problem(p_tlimt, p_mlimt, p_specjg, p_author)
		VALUES(NEW.p_tlimt, NEW.p_mlimt, NEW.p_specjg,NEW.uploader_id);
        
		SET @pid=LAST_INSERT_ID();
		INSERT INTO eojdb.problem_multilang(p_id, p_lang, p_title, p_desc, 
			p_input_tips, p_output_tips, p_sampleinput, p_sampleoutput, p_hint)
		VALUES(@pid, NEW.p_lang, NEW.p_title, NEW.p_desc,
		NEW.p_input_tips, NEW.p_output_tips, NEW.p_sampleinput,
		NEW.p_sampleoutput, NEW.p_hint);
		
		INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
		VALUES(1,CONCAT('successful use trigger add a problem and problem-multilang record that [p_id] ',@pid),0);
	END IF;
END
//
DELIMITER ;
