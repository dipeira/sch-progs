<?php

// version 1.5
class Formitable {

	// these vars determine whether to use default input type or an alternate based on field size
	// 'enum' field default is SELECT, alternate is RADIO
	// 'set' field default is MULTISELECT, alternate is CHECKBOX
	// 'blob' or 'text' field default is TEXTAREA, alternate is TEXT
	var $enumField_toggle = 3;
	var $setField_toggle = 4;
	var $strField_toggle = 70;

	// these vars determine form input size attributes
	var $textInputLength = 50;
	var $textareaRows = 4;
	var $textareaCols = 50;
	var $multiSelectSize = 4;
	var $fileInputLength = 50;

	// these vars hold the string returned on success or fail of record INSERT/UPDATE
	var $msg_insertSuccess = '<center><label class="font">Form has been submitted successfully.</label></center>';
	var $msg_insertFail = '<center><label class="font"><strong>An error occurred.</strong><br/>Form submission failed.</label></center>';
	var $msg_updateSuccess = '<center><label class="font">Record has been updated successfully.</label></center>';
	var $msg_updateFail = '<center><label class="font"><strong>An error occurred.</strong><br/>Record update failed.</label></center>';

	// these vars hold the strings output before and after error messages
	var $err_pre = '<br/><span class="err">';
	var $err_post = '</span>';

	// these vars hold the strings output before and after the error box
	var $err_box_pre = '<center><div class="errbox">';
	var $err_box_post = '</div></center>';
	
	var $feedback = 'both';

	// these vars set the string ouput after option labels, field labels, and fields
	var $optionBreak = "<br/>\n";
	var $labelBreak = "<br/>\n";
	var $fieldBreak = "<br/><br/>\n\n";

	// toggle print or return output
	var $returnOutput = false;
	
	public $skip;

	// class constructor sets form name and gets table info. Args are DB link, DB name, table name.
	function Formitable(&$conn,$DB,$table){

		$this->conn = $conn;

		$this->DB = $DB;

		$this->table = $table;

		$this->formName = is_array($table) ? implode('_',$table) : $table;

		$this->_multi = false;

		$this->pkey = "";

		$this->submitted = 0;

		$this->_magic_quotes = get_magic_quotes_gpc();

		$this->enableFieldSets = true;

		$this->hasFiles = false;

		$this->skipFields( array('formitable_signature','formitable_multipage','formitable_setcheck',
								 'pkey','submit','x','y','MAX_FILE_SIZE') );
		
		// template delimeters
		$this->tplStart = '{';
		$this->tplEnd = '}';

		$this->mysql_errors = false;

		// process $_POST
		$this->_post = $this->_processPost();
		
		// if multi-table then initialize child Formitable objects
		if( is_array($table) ){

			$this->_multi = 'root';
			foreach($table as $form){
				
				// create child objects, set type, and reference back to parent
				$this->tables[$form] = new Formitable($conn,$DB,$form);
				$this->tables[$form]->_multi = 'node';
				$this->tables[$form]->_root = &$this;

				// process $_POST again as a node
				$this->tables[$form]->_post = $this->tables[$form]->_processPost();

				// empty msgs
				$this->tables[$form]->msg_insertSuccess = 
				$this->tables[$form]->msg_updateSuccess = 
				$this->tables[$form]->msg_insertFail = 
				$this->tables[$form]->msg_updateFail = '';
			}

		// otherwise setup as normal
		} else {

			$fields = $this->query('SELECT * FROM `'.$table.'` LIMIT 1');
			$this->columns = @mysql_num_fields($fields);

			// loop through all fields and gather info about each
			for($n=0; $n < $this->columns; $n++){
				
				$name = @mysql_field_name($fields,$n);
				
				$fieldInfo = array('length'=>@mysql_field_len($fields,$n));

				/* mysql_field_flags - "not_null", "primary_key", "unique_key",
				   "multiple_key", "blob", "unsigned", "zerofill", "binary",
				   "enum", "set", "auto_increment" and "timestamp"  <-- values not in mysql_fetch_field  */
				$flags = explode( ' ', @mysql_field_flags($fields,$n) );
				foreach($flags as $flag){
					if( in_array($flag, array('enum','set','timestamp')) ){
						$fieldInfo['subtype'] = $flag;
						// get options for enum or set
						if($flag !== 'timestamp'){
							$fieldInfo['options'] = $this->_mysql_enum_values($name);
						}
					} else if($flag) {
						$fieldInfo[$flag] = true;
					}
				}
				
				// add a few more flags not available from mysql_field_flags
				$flags = @mysql_fetch_field($fields,$n);
				foreach( $flags as $flag=>$value ){
					if($value && !array_key_exists($flag, $fieldInfo)){
						$fieldInfo[$flag=='def'?'default':$flag] = $value;
					}
				}
				
				// automatically detect and store primary key if available
				if( in_array('primary_key',$fieldInfo) ){
					$this->setPrimaryKey($name);
				}
				
				// remove redundant values and save field info
				unset($fieldInfo['name'],$fieldInfo['table'], $fieldInfo['blob']);
				$this->fields[$name] = array_diff_assoc($fieldInfo, array('name','table','blob'));
			}

			// store cross references for field number and field name
			$this->fieldNames = array_keys($this->fields);
			$this->fieldNums = array_flip($this->fieldNames);
			
			//print '<pre>'.print_r($this->fields,1).'</pre>';
			//print '<!-- '.str_replace("=>\n","=>",print_r($this->fields,1)).' -->';

		}

	}

	// this function submits the form to the database;
	// IF form 'pkey' value is set then UPDATE record
	// ELSE INSERT a new record
	function submitForm($echo=true){

		// return if there's no post
		if($this->_post === false){ return 0; }

		// return saved value if already submitted
		// this avoids double submit if called explicitly and auto submitted
		if($this->submitted){ return $this->submitted; }

		// apply to each node if a multiform root
		if( $this->_multi == 'root' ){

			// set false success flag to submit nodes w/ a dry run
			// this prevents partial node submission when validation fails on one or more
			$this->_multiSuccess = false;
			$result = $this->_applyMethod('submitForm');

			// upon full validation $result should be equal to the number of tables
			if( $result == count($this->tables) ){

				// set true success flag to really submit nodes
				$this->_multiSuccess = true;
				$this->_applyMethod('submitForm');
				$this->submitted = 1; 

				if( $echo || !$this->returnOutput ){
					echo $this->msg_insertSuccess;
				} else {
					return $this->msg_insertSuccess;
				}

				return 1;

			} else return -1;

		}

		// cycle through allowed fields and assign values
		// ex. $this->_allowedFrom['table__fieldName'] = 'field';
		if( isset($this->_allowedFrom) ){
			foreach($this->_allowedFrom as $fromField=>$field){

				// set the value if already in post
				if( isset($_POST[$fromField]) ){
					$this->_post[$field] = $_POST[$fromField];

				// otherwise test if fromField is a pkey from another table
				} else if( isset($this->_root) && strpos($fromField,'__') ){

					$fromField = explode('__',$fromField);
					if( array_key_exists($fromField[0], $this->_root->tables) ){

						$fromTable = $this->_root->tables[ $fromField[0] ];
						if( isset($fromTable->pkeyID) &&
							$fromTable->pkey == $fromField[1] ){
							$this->_post[$field] = $fromTable->pkeyID;
						}

					}

				}

			}
		}

		// don't run this block if an unvalidated multiform node
		if( $this->_multi != 'node' || $this->_root->_multiSuccess ){

			// cycle through form signature if set
			// if a "set" field is missing then assign empty value
			// and if any other field type is missing then assign NULL
			if( isset($this->_post['formitable_signature']) ){
				$this->_signature = split(',', $this->decrypt($this->_post['formitable_signature']));
				foreach($this->_signature as $key){
					if(!isset($this->_post[$key])){
						if( isset($this->fields[$key]) && $this->fields[$key]=='set' ){
							$this->_post[$key] = '';
						} else {
							$this->_post[$key] = NULL;
						}
					}
				}
			// signature should always accompany encryption
			} else if( isset($this->rc4key) ){
				print $this->msg_insertFail;
				return false;
			}

		}

		// submit via UPDATE
		if( isset($this->_post['pkey']) ){

			// don't run this block if an unvalidated multiform node
			if( $this->_multi != 'node' || !$this->_root->_multiSuccess ){

				// decrypt primary key if encrypted
				if( isset($this->rc4key) ){
					$this->_post['pkey'] = $this->decrypt($this->_post['pkey']);
					$this->_post['pkey'] = str_replace($this->rc4key,'',$this->_post['pkey']);
				}

				// set pkey for form output in case validation fails
				$this->pkeyID = $this->_post['pkey'];

				// assign empty values for checkboxes/multiselects if necessary
				if( isset($this->_post['formitable_setcheck']) ){
					foreach($this->_post['formitable_setcheck'] as $key){
						$key = $this->decrypt($key);
						if( !isset($this->_post[$key]) ){
							$this->_post[$key] = '';
						}
					}
				}

				// validate all fields and return -1 if validation failed
				if( $this->_checkValidation() == -1 ){
					$this->submitted = -1;
					return -1;
				}

				// if a multiform node then assume query is ok for now
				if( $this->_multi == 'node' ){
					return 1;
				}

			}

			// cycle through $this->_post variables to form query assignments
			foreach($this->_post as $key=>$value){

				// ignore skipped fields, formitable specific variables, and fields not in signature
				if( isset($this->skip[$key]) || strstr($key,'_verify') ||
					(isset($this->_signature) && !in_array($key, $this->_signature))
				){ continue; }

				// assign comma seperated value if checkbox or multiselect, otherwise normal assignment
				if(is_array($value)) @$fields .= ",`$key` = '".implode(",",$this->_post[$key])."'";
				else if(is_null($value)) @$fields .= ",`$key` = NULL";
				else @$fields .= ",`$key` = '".$this->_handle_magic_quotes($value,true)."'";

			}

			// remove first comma
			$fields = substr($fields,1);

			// build query
			$SQLquery = "UPDATE $this->table SET $fields WHERE `$this->pkey` = '".$this->_post['pkey']."'";

			// execute query and test errors
			$this->query($SQLquery);
			if( @mysql_error()=='' ){

				// set pkeyID for output if multiple page form
				if( @$this->_post['formitable_multipage'] == 'next' ){

					// decrypt primary key first if encrypted
					$this->pkeyID = decrypt($this->_post['pkey']);

				} else {
					
					// skip success message when first page or multiform node
					if( $this->_multi != 'node' && (!isset($this->_post['formitable_multipage']) || $this->_post['formitable_multipage'] != 'start') ){
						if($echo || !$this->returnOutput) echo $this->msg_updateSuccess;
						else return $this->msg_updateSuccess;
					}
					
				}
				
				$this->submitted = 1;
				return 1;

			}

		// submit via INSERT
		} else {

			// don't run this block if an unvalidated multiform node
			if( $this->_multi != 'node' || !$this->_root->_multiSuccess ){

				if($this->_checkValidation() == -1){ $this->submitted=-1; return -1; }

				// if a multiform node then assume query is ok and return
				if( $this->_multi == 'node' ){
					return 1;
				}

			}

			foreach($this->_post as $key=>$value){

				// skip if told to, or if verify field, or if not in signature
				if( isset($this->skip[$key]) || strstr($key,'_verify') ||
					(isset($this->_signature) && !in_array($key, $this->_signature)) ) continue;

				@$fields .= ",`".$key."`";

				if(is_array($value)) @$values .= ",'".implode(",",$value)."'";
				else if(is_null($value)) @$values .= ',NULL';
				else @$values .= ",'".$this->_handle_magic_quotes($value,true)."'";

			}

			// remove first comma
			$fields = substr($fields,1);
			$values = substr($values,1);

			// form and execute query, eventually echoing results
			$SQLquery = "INSERT INTO $this->table ($fields) VALUES ($values)";

			// execute query and test errors
			$this->query($SQLquery);
			if( @mysql_error()=='' ){

				// flag successful submit
				$this->submitted = 1;

				// get primary key
				// if( isset($this->_post['formitable_multipage']) && $this->_post['formitable_multipage'] == 'start' )
				$this->pkeyID = $this->getPrimaryKey();

				// skip success message when not last page or is a multiform node
				if( $this->_multi != 'node' && (!isset($this->_post['formitable_multipage']) || $this->_post['formitable_multipage']=='end') ){
					if($echo || !$this->returnOutput) echo $this->msg_insertSuccess;
					else return $this->msg_insertSuccess;
				}
				
				return 1;
			}

		}

		// the query failed if it made it this far
		$result = $this->msg_updateFail.($this->mysql_errors ? '<br/>'.$SQLquery.'<br/><strong>'.mysql_error().'</strong>' : '');
		if($echo){
			echo $result;
			return 0;
		} else {
			return $result;
		}
		
	}

	// this function returns the primary key value for the last inserted record
	// new in version 1.5
	function getPrimaryKey(){
		if( isset($this->pkeyID) ){ return $this->pkeyID; }
		if($this->submitted){
			// mysql_insert_id Retrieves the ID generated for an
			// AUTO_INCREMENT column by the previous INSERT query
			$lastID = @mysql_insert_id($this->conn);
			// next best thing if mysql_insert_id failed, not guaranteed to be accurate
			if(!$lastID && $this->pkey){
				$SQLquery = "SELECT `$this->pkey` FROM `$this->table` ORDER BY `$this->pkey` DESC LIMIT 1";
				$lastID = @mysql_result(@mysql_query($SQLquery,$this->conn),0);
			}
			return (int)$lastID;
		} else return false;
	}

	// this function will execute an arbitrary query on the current database
	// returning the raw resource or an array of results in the format specified by $fetchFlag
	// new in version 1.5
	function query($sql,$result_type=false){
		@mysql_select_db($this->DB,$this->conn);
		$result = @mysql_query($sql,$this->conn);
		if( $result && in_array($result_type,array(MYSQL_ASSOC,MYSQL_NUM,MYSQL_BOTH)) ){
			$rows = array();
			while( $row = @mysql_fetch_array($result,$result_type) ) {			    $rows[] = $row;			}
			$result = $rows;
		}
		return $result;
	}

	// this function will query the table for a record
	// with a primary key value of argument $id.
	// use the decrypt arg if the value came from an encrypted form
	function getRecord($id, $decryptId=false){

		// apply to each node if a multiform root
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('getRecord', array($id,$decryptId), $id);
		}

		// need to decrypt id if it came from the form
		if( isset($this->rc4key) && $decryptId ){
			$id = $this->decrypt($id);
			$id = str_replace($this->rc4key,'',$id);
		}

		// build and execute query
		$SQLquery = "SELECT * FROM $this->table WHERE $this->pkey = '$id'";
		@mysql_select_db($this->DB,$this->conn);
		$result = @mysql_query($SQLquery,$this->conn);

		// get fields if there was only one record
		if( @mysql_num_rows($result) == 1 ){
			$this->pkeyID = $id;
			$this->record = @mysql_fetch_assoc($result);
			return true;
		// otherwise consider it a faliure
		} else return false;

	}

	// this function retrieves records from another table to be used as values for input
	function normalizedField($fieldName, $tableName, $tableKey = 'ID', $tableValue = 'name', $orderBy = 'value ASC', $whereClause = '1'){
		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			$args = array($fieldName,$tableName,$tableKey,$tableValue,$orderBy,$whereClause);
			return $this->_applyMethod('normalizedField', $args, $fieldName);
		}
		// otherwise as normal
		$this->normalized[$fieldName]['tableName'] = $tableName;
		$this->normalized[$fieldName]['tableKey'] = $tableKey;
		$this->normalized[$fieldName]['tableValue'] = $tableValue;
		$this->normalized[$fieldName]['orderBy'] = $orderBy;
		$this->normalized[$fieldName]['whereClause'] = $whereClause;
	}

	// this function retrieves records from another table to be used as labels for enum/set fields
	// it is used to supply descriptions for smaller names -New in version .99-
	function getLabels($fieldName, $tableName, $tableKey = 'ID', $tableValue = 'name'){
		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			$args = array($fieldName,$tableName,$tableKey,$tableValue);
			return $this->_applyMethod('getLabels', $args, $fieldName);
		}
		// otherwise as normal
		$this->labelValues[$fieldName]['tableName'] = $tableName;
		$this->labelValues[$fieldName]['tableKey'] = $tableKey;
		$this->labelValues[$fieldName]['tableValue'] = $tableValue;
	}

	// this function forces a form field to an explicit input type regardless of size
	// args are field name and input type, input types are as follows:
	// for enum field - "select" or "radio"
	// for set field- "multiselect" or "checkbox"
	// for string or blob field - "text" or "textarea"
	// string can also be forced as "password" or "file"
	function forceType($fieldName,$inputType){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('forceType', array($fieldName,$inputType), $fieldName);
		}
		// otherwise as normal
		if($inputType == 'file'){
			$this->hasFiles = true;
			if( $this->_multi == 'node' ){ $this->_root->hasFiles = true; }
		}
		$this->forced[$fieldName] = $inputType;

	}

	function forceTypes($fieldNames,$inputTypes){

		if( count($fieldNames) != count($inputTypes) ) return false;

		for($i=0;$i<count($fieldNames);$i++)
			$this->forceType($fieldNames[$i],$inputTypes[$i]);

		return true;

	}

	// this function sets a default value for the field
	function setDefaultValue($fieldName, $fieldValue='', $overrideRetrieved=false) {
		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			$args = array($fieldName,$fieldValue,$overrideRetrieved);
			return $this->_applyMethod('setDefaultValue', $args, $fieldName);
		}
		// otherwise as normal
		$this->defaultValues[$fieldName]['value'] = $fieldValue;
		if($overrideRetrieved) $this->defaultValues[$fieldName]['override'] = true;
	}

	// this function forces a form field to be skipped on INSERT or UPDATE
	// arg is field name
	function skipField($fieldName){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('skipField', array($fieldName), $fieldName);
		}
		// otherwise as normal
		$this->skip[$fieldName] = true;

	}

	function skipFields($fieldNames){

		if( !is_array($fieldNames) ) return false;

		foreach( $fieldNames as $field ){
			$this->skipField($field);
		}

		return true;

	}

	// this function hides a field from HTML output
	// arg is field name, plural version below
	function hideField($fieldName){

		if( !is_string($fieldName) ){ return false; }
		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('hideField', array($fieldName), $fieldName);
		// otherwise as normal
		} else {
			$this->hidden[$fieldName] = 'hide';
		}
		return true;

	}

	function hideFields($fieldNames){

		if( !is_array($fieldNames) ){ return false; }
		foreach( $fieldNames as $field ){
			$this->hideField($field);
		}
		return true;

	}

	// this function sets a field's label text
	// args are field name and label text, plural version below
	function labelField($fieldName,$fieldLabel){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('labelField', array($fieldName,$fieldLabel), $fieldName);
		// otherwise as normal
		} else {
			$this->labels[$fieldName] = $fieldLabel;
		}

	}

	function labelFields($fieldNames,$fieldLabels){

		if( count($fieldNames) != count($fieldLabels) ){ return false; }
		for($i=0; $i<count($fieldNames); $i++){
			$this->labelField( $fieldNames[$i], $fieldLabels[$i] );
		}
		return true;

	}

	// this function sets the HTML immediately
	// following label tags, arg is HTML code
	function setLabelBreak($HTML){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('setLabelBreak', array($HTML) );
		}
		// otherwise as normal
		$this->labelBreak = $HTML;

	}

	// this function sets the HTML immediately
	// following each field type, arg is HTML code
	function setFieldBreak($HTML){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('setFieldBreak', array($HTML) );
		}
		// otherwise as normal
		$this->fieldBreak = $HTML;

	}

	// this function sets the HTML immediately
	// following each radio option, arg is HTML code
	function setOptionBreak($HTML){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('setOptionBreak', array($HTML) );
		}
		// otherwise as normal
		$this->optionBreak = $HTML;

	}

	// this function allows you set all breaks including label, field and option
	// it's useful for changing 2 or 3 breaks at the same time, option break is optional
	function setBreaks($label, $field, $option=NULL){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('setBreaks', array($label,$field,$option) );
		}
		// otherwise as normal
		$this->labelBreak = $label;
		$this->fieldBreak = $field;
		if(!is_null($option)) $this->optionBreak = $option;

	}

	// this function sets the name of the table's primary key,
	// its necessary to retrieve/update a record or for multiPage functionality
	function setPrimaryKey($pkey_name){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('setPrimaryKey', array($pkey_name) );
		}
		// otherwise as normal
		$this->pkey = $pkey_name;

	}

	// this function sets the enableFieldSets option, value is either true or false
	function toggleFieldSets($toggle){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('toggleFieldSets', array($toggle) );
		}
		$this->enableFieldSets=$toggle;

	}

	// this function checks existing records for field value, arg is field name
	function uniqueField($fieldName,$msg='Already taken.'){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('uniqueField', array($fieldName,$msg), $fieldName );
		}
		$this->unique[$fieldName]['msg'] = $msg;

	}

	// this function registers a new validation type
	// args are method name, regular expression, and optional error text
	function registerValidation($methodName, $regex, $errText = 'Invalid input.'){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('registerValidation', array($methodName, $regex, $errText) );
		}
		$this->_validationMethod[$methodName]['regex'] = $regex;
		$this->_validationMethod[$methodName]['err'] = $errText;

	}

	// this function sets a field's validation type
	// args are field name, method name, and optional custom error text
	function validateField($fieldName, $methodName, $errText=NULL){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('validateField', array($fieldName, $methodName, $errText), $fieldName );
		}
		$this->validate[$fieldName]['method'] = $methodName;
		if(!is_null($errText)) $this->validate[$fieldName]['err'] = $errText;

	}

	// this function opens the form tag and submits the form if pkey is set
	function openForm($attr='', $autoSubmit=true){

		// not callable by a multiform node
		if( $this->_multi == 'node' ) return false;

		// assign all error messages from nodes to root
		if( $this->_multi == 'root' ){
			foreach($this->tables as $form){
				if( isset($form->errMsg) ){
					foreach($form->errMsg as $key=>$value){
						$this->errMsg[$key] = $value;
					}
				}
			}
		}

		if( $this->returnOutput ){ ob_start(); }

		if( isset($_POST['submit']) && $autoSubmit ){
			// submit form and store results
			$this->submitted = $this->submitForm();
		}

		// output error text box if validation failed
		if( $this->submitted == -1 && ($this->feedback=='box' || $this->feedback=='both') ){
			echo $this->err_box_pre;
			foreach($this->errMsg as $key=>$value){
				if( isset($this->labels[$key]) ) $label = $this->labels[$key];
				else $label = ucwords( str_replace('_', ' ', $key) );
				echo '<span class="errBoxName">'.$label.':</span> '.$value.'<br/>';
			}
			echo $this->err_box_post;
		}

		// don't open form if single page form or last page of multiple and no errors
		if( $this->submitted != 1 || (isset($_POST['formitable_multipage']) && $_POST['formitable_multipage']!='end') ){

			// output opening form tag, checking for overridden name and action
			echo '<form method="POST"'.
				( ( stripos($attr,'name=') === false ) ? ' name="'.$this->formName.'"' : '' ).
				( ( stripos($attr,'action=') === false ) ? ' action="'.($action ? $action : $_SERVER['PHP_SELF']).'"' : '' ).
				($this->hasFiles?' enctype="multipart/form-data"':'').
				($attr!=''?' '.$attr:'').">\n";

			// output hidden MAX_FILE_SIZE field if files are present.
			// to set the upload size smaller than the value in php.ini
			// create an .htaccess file with the following directive
			// php_value upload_max_filesize 1M
			// http:// us3.php.net/manual/en/ini.core.php#ini.upload-max-filesize
			if($this->hasFiles){
				$maxBytes = trim(ini_get('upload_max_filesize'));
				$lastChar = strtolower($maxBytes[strlen($maxBytes)-1]);
				if($lastChar=="k"){ $maxBytes=$maxBytes*1024; }
				else if($lastChar=="m"){ $maxBytes=$maxBytes*1024*1024; }
				echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxBytes.'"/>'."\n";
			}

		}

		if( $this->returnOutput ){
			$html_block = ob_get_contents();
			ob_end_clean();
			return $html_block;
		}

	}

	// this function closes the form tag & prints a hidden field 'pkey' if a record has been set either manually or through multiPage
	function closeForm($submitValue='Submit',$attr='',$resetValue='Reset Form',$printReset=true,$closeForm=true){

		if( $this->returnOutput ){ ob_start(); }

		// don't output signature or primary key if a multitable root
		if($this->_multi != 'root'){

			$fieldTable = $this->_multi ? $this->table.'__' : '';

			// output hidden pkey field for update opertaions
			if( isset($this->pkeyID) ){
				if( isset($this->rc4key) ){
					$pkeyVal = $this->encrypt( $this->rc4key.$this->pkeyID );
				} else $pkeyVal = $this->pkeyID;
				echo '<input type="hidden" name="'.$fieldTable.'pkey" value="'.$pkeyVal.'"/>'."\n";
			}
			
			// output hidden signature field for security check
			if( isset($this->rc4key) && isset($this->signature) ){
				$sigVal = $this->encrypt( implode(",",$this->signature) );
				echo '<input type="hidden" name="'.$fieldTable.'formitable_signature" value="'.$sigVal.'"/>'."\n";
			}

		// but still apply to nodes if a multiform root
		} else {

			if( $this->_multi == 'root' ){
				$this->_applyMethod('closeForm', array() );
			}

		}

		// don't output buttons or close form when a multitable node
		if($this->_multi != 'node'){

			if( isset($this->multiPageSubmitValue) ){
				$submitValue = $this->multiPageSubmitValue;
			}
			echo '<div class="button">'.($printReset?'<input type="reset" value="'.$resetValue.'" class="reset"/>':'');
			if(strstr($submitValue,"image:")){
				echo '<input type="hidden" name="submit"/><input type="image" src="'.
					str_replace('image:','',$submitValue).'"'.($attr && stristr($attr,'class=')?'':' class="img"').($attr!=""?" ".$attr:"")."/>";
			} else {
				echo '<input type="submit" name="submit" value="'.$submitValue.'"'.
				($attr && stristr($attr,'class=')?'':' class="submit"').($attr?' '.$attr:'').'/>';
			}
			echo '</div>'.($closeForm?'</form>':'')."\n";

		}

		if( $this->returnOutput ){
			$html_block = ob_get_contents();
			ob_end_clean();
			return $html_block;
		}

	}

	// This function outputs a single field called by name. It searches the fields resource using mysql_field_name
	// until it finds the field provided in the argument, it then calls _outputField($n) where $n is the record offset
	function printField($fieldName,$attr='',$verify=false){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('printField', array($fieldName,$attr,$verify), $fieldName);
		}
		
		// otherwise as normal
		for ($n=0; $n < $this->columns; $n++){

			if( $fieldName == $this->fieldNames[$n] ){

				if( $this->returnOutput ){ ob_start(); }

				$this->_outputField($n, $attr, $verify);

				if( $this->returnOutput ){
					$html_block = ob_get_contents();
					ob_end_clean();
					return $html_block;
				}

				return 1;
			}

		} return 0;

	}

	// this sets a key string for rc4 encryption of pkey
	function setEncryptionKey($key){

		// not callable by a multiform node
		if( $this->_multi == 'node' && !isset($this->_root->_encFlag) ) return false;

		if($key){

			$this->rc4key = $key;
			$this->rc4 = new rc4crypt();

			// set flag and apply to nodes if a multiform root 
			if( $this->_multi == 'root' ){
				$this->_encFlag = true;
				$this->_applyMethod('setEncryptionKey', array($key));
				unset($this->_encFlag);
			}
			return true;

		} else return false;

	}

	// this function outputs a hidden field that enables a multi page form, takes argument $step
	// $step should be "start" for first page, "end" for last page and "next" for intermediate pages
	function multiPage($step,$buttonValue='Continue'){

		// not callable by a multiform node
		if( $this->_multi == 'node' ) return false;

		if( $this->returnOutput ){ ob_start(); }

		if($step == 'start' || $step == 'next' || $step == 'end')
			echo '<input type="hidden" name="formitable_multipage" value="'.$step.'"/>';
		if($step == 'end' && $buttonValue == 'Continue') $this->multiPageSubmitValue = 'Finish';
		else $this->multiPageSubmitValue = $buttonValue;

		if( $this->returnOutput ){
			$html_block = ob_get_contents();
			ob_end_clean();
			return $html_block;
		}

	}

	// This function returns a field's default mysql value,
	// or false when no default is available.
	// new in version 1.5
	function getFieldDefault($fieldName)
	{
		// only apply to a single node if a multiform root 
		if( $this->_multi == 'root' ){
			return strpos($fieldName,'.') ? $this->_applyMethod('getFieldDefault', array($fieldName), $fieldName) : false;
		}
		
		return isset($this->fields[$fieldName]['default']) ? $this->fields[$fieldName]['default'] : false;
	}

	// This function returns a single field value. It is useful to test a field value without printing it
	// this is equivilent to accessing a field like so: $FormitableObj->record["fieldName"] but with some error checking
	function getFieldValue($fieldName){

		// only apply to a single node if a multiform root 
		if( $this->_multi == 'root' ){
			if( strpos($fieldName,'.') ){
				return $this->_applyMethod('getFieldValue', array($fieldName), $fieldName );
			} else return false;
		}
		
		if( isset($this->record[$fieldName]) ) return $this->record[$fieldName];
		else if( isset($this->_post[$fieldName]) ) return $this->_post[$fieldName];
		else return false;

	}

	// This function returns a single field label. It is useful to get a field label without printing it
	// this is equivilent to accessing a field like so: $FormitableObj->labels["fieldName"] but with some error checking
	function getFieldLabel($fieldName){

		// only apply to a single node if a multiform root 
		if( $this->_multi == 'root' ){
			if( strpos($fieldName,'.') ){
				return $this->_applyMethod('getFieldLabel', array($fieldName), $fieldName );
			} else return false;
		}
		
		if( isset($this->labels[$fieldName]) ) return $this->labels[$fieldName];
		else return ucwords( str_replace("_", " ", $fieldName) );

	}

	// This function enables the submission of an arbitrary field when encryption is enabled
	// and the field was not output in the form (therefore not included in the form signature)
	function allowField($fieldName,$from=null){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			$field = explode('.',$fieldName);
			if( count($field) == 2 && array_key_exists($field[0],$this->tables) )
				return $this->_applyMethod('allowField', array($field[1],$from), $fieldName );
		} else {
			if( $fieldName ) $this->signature[] = $fieldName;
			if( $from ) $this->_allowedFrom[ str_replace('.','__',$from) ] = $fieldName;
		}

	}

	// this function outputs the entire form, one field at a time
	function printForm($openArgs=array(),$closeArgs=array()){

		if( $this->returnOutput ){

			ob_start();
			$this->returnOutput = false;
			$this->printForm($openArgs,$closeArgs);
			$this->returnOutput = true;
			$html_block = ob_get_contents();
			ob_end_clean();
			return $html_block;

		} else {

			// don't open if this is a multiform node
			if( $this->_multi != 'node' ){ echo call_user_func_array( array(&$this, 'openForm'), $openArgs ); }
			// if this is a multiform root cycle through nodes
			if( $this->_multi == 'root' ){
				foreach( $this->tables as $form ){
					for($n=0; $n < $form->columns; $n++) $form->_outputField($n);
				}
			// otherwise output as normal
			} else if( $this->submitted != 1 ){
				for($n=0; $n < $this->columns; $n++) $this->_outputField($n);
			}
			// close form
			if( $this->submitted != 1 ){
				echo call_user_func_array( array(&$this, 'closeForm'), $closeArgs );
			}

		}

	}

	// this function outputs the entire form, one field at a time
	function printFromTemplate($tpl){

		// not callable by a multiform node
		if( $this->_multi == 'node' ) return false;

		if( is_file($tpl) ){
			if( !is_readable($tpl) ){ return false; }
			$tpl = file_get_contents($tpl);
			if($tpl === false){ return false; }			
		}

		// store return flag to restore later
		$tmpReturn = $this->returnOutput;
		$this->returnOutput = true;

		$pattern = '/\\'.$this->tplStart.'([^\\'.$this->tplEnd.']+)\\'.$this->tplEnd.'/U';
		preg_match_all($pattern, $tpl, $fields);

		foreach($fields[1] as $field){
			if( strstr($field, '_verify') ){ continue; }
			$replace = $this->tplStart.$field.$this->tplEnd;
			if( strstr($field, ':') ) {
				$pieces = explode(':',$field);
				$ct = count($pieces);
				// replace with a php variable, up to a 2 dimension array
				if($pieces[0]=='php'){
					global $$pieces[1];
					if($ct==2){
						$tpl = str_replace($replace, (isset(${$pieces[1]}) ? ${$pieces[1]}:''), $tpl);
					} else if($ct==3){
						$tpl = str_replace($replace, (isset(${$pieces[1]}[$pieces[2]]) ? ${$pieces[1]}[$pieces[2]]:''), $tpl);
					} else if($ct==4){
						$tpl = str_replace($replace, (isset(${$pieces[1]}[$pieces[2]][$pieces[3]]) ? ${$pieces[1]}[$pieces[2]][$pieces[3]]:''), $tpl);
					}
				} else if($pieces[0]=='setBreak'){
					if($ct==3){
						switch( strtolower($pieces[1]) ){
							case 'label':
							$tpl = str_replace($replace,$this->setLabelBreak($pieces[2]), $tpl);
							break;
							case 'field':
							$tpl = str_replace($replace,$this->setFieldBreak($pieces[2]), $tpl);
							break;
							case 'option':
							$tpl = str_replace($replace,$this->setOptionBreak($pieces[2]), $tpl);
							break;
						}
					}
				}
			} else if( $field == 'open_form' || strstr($field,'open_form,') ) {
				$pieces = explode(',',$field);
				$tpl = str_replace($replace,
					$this->openForm(
						(isset($pieces[1])?$pieces[1]:''),	 // $attr
						(isset($pieces[2])?$pieces[2]:true), // $autosubmit
						(isset($pieces[3])?$pieces[3]:'')	 // $action
					), $tpl);
			} else if( $field=='close_form' || strstr($field,'close_form,') ) {
				$pieces = explode(',',$field);
				$tpl = str_replace($replace,
					$this->closeForm(
						(isset($pieces[1])?$pieces[1]:'Submit'),	 // $submitValue
						(isset($pieces[2])?$pieces[2]:''),			 // $attr
						(isset($pieces[3])?$pieces[3]:'Reset Form'), // $resetValue
						(isset($pieces[4])?$pieces[4]:true), 		 // $printReset
						(isset($pieces[5])?$pieces[5]:true) 		 // $closeForm
					), $tpl);
			} else {
			
				$verifyReplace = $this->tplStart.$field.'_verify'.$this->tplEnd;
				
				// get attribute arg if avail
				if( strpos($field, ',') !== false ){
					list($field, $fieldAttr) = explode(',',$field,2);
				} else { $fieldAttr = null; }
				
				// get table and field if a multiform
				if( $this->_multi == 'root' && strpos($field, '.') !== false ){
					list($table, $field) = explode('.',$field,2);
					if( array_key_exists($table,$this->tables) ){
						$formitable = &$this->tables[$table];
					}
				}
				
				// get the correct object if its not a multiform
				if(!isset($formitable)){ $formitable = &$this; }
				
				// store node output flag if a root
				if( $this->_multi == 'root' ){
					$tmpMultiReturn = $formitable->returnOutput;
					$formitable->returnOutput = true;
				}
				
				// replace the matched string w/ output of printField or empty string
				$tpl = str_replace($replace,
					( in_array($field,$formitable->fieldNames) ? $formitable->printField($field, $fieldAttr) : '' ), $tpl);
				
				// replace verify string if available
				if( in_array( (isset($table)?$table.'.':'').$field.'_verify', $fields ) ){
					$tpl = str_replace($verifyReplace, $formitable->printField($field, $fieldAttr, true), $tpl);
				}
				
				// restore node output flag if a root
				if( $this->_multi == 'root' ){
					$formitable->returnOutput = $tmpMultiReturn;
				}
				
				unset($fieldAttr);
			}
		} // end foreach

		$this->returnOutput = $tmpReturn;
		if($tmpReturn){
			return $tpl;
		} else {
			print $tpl;
			return true;
		}

	}

	function setTemplateDelimiters($start, $end){

		// not callable by a multiform node
		if( $this->_multi == 'node' ) return false;

		if( (string)$start=='' || (string)$end=='' ){ return false; }
		$this->tplStart = $start;
		$this->tplEnd = $end;

	}

	// this function sets the error feedback method
	function setFeedback($mode){

		// not callable by a multiform node
		if( $this->_multi == 'node' ) return false;

		if( @in_array($mode, array('line','box','both')) ){
			$this->feedback = $mode;
			return true;
		} else return false;

	}

	// this function sets a callback function
	function registerCallback($fieldName, $funcName, $mode = 'post', $args = ''){

		// apply to nodes if a multiform root 
		if( $this->_multi == 'root' ){
			return $this->_applyMethod('registerCallback', array($fieldName,$funcName,$mode,$args) );
		}
		
		if( @in_array(strtolower($mode), array('post','retrieve','both')) && is_callable($funcName) ){

			$this->callback[$fieldName]['args'] = $args;

			if($mode == 'both'){
				$this->callback[$fieldName]['post'] = $this->callback[$fieldName]['retrieve'] = $funcName;
			} else {
				$this->callback[$fieldName][$mode] = $funcName;
			}

			return true;

		} else return false;

	}
	
	function getProcessedData()
	{
		return array_diff_key($this->_post, $this->skip);
	}

	// this function encrypts a value using the RC4 cipher
	function encrypt($value){
		
		if($this->rc4key){
			$value = $this->rc4->_encrypt (
				$this->rc4key, $value
			);
		}
		
		return $value;
		
	}

	// this function decrypts a value using the RC4 cipher
	function decrypt($value){
		
		if($this->rc4key){
			$value = $this->rc4->_decrypt (
				$this->rc4key,
				$this->_handle_magic_quotes($value)
			);
		}
		
		return $value;
		
	}

	/*** BEGIN PRIVATE METHODS ***/
	
	function _mysql_enum_values($fieldName){
		
		// let's only query the database once
		static $fields = null;
		if(is_null($fields)){
			
			// make sure something is assigned regardless of the results
			$fields = array();
			$rows = $this->query('DESCRIBE '.$this->table, MYSQL_ASSOC);
			foreach($rows as $row){
				$pieces = explode('(',$row['Type']);
				if( count($pieces) > 1 && ($pieces[0] == 'enum' || $pieces[0] == 'set') ){
					// trim parens and separate values, 1 == "'" and -2 == "')"
					$options = split("','",substr($pieces[1],1,-2));
					$fields[$row['Field']] = $options;
				}
			}
			
		}
		
		if($fieldName)
			return isset( $fields[$fieldName] ) ? $fields[$fieldName] : false;
		else
			return $fields;
		
	}

	// this function will process the $_POST vars for a multiform node
	// or just assign $_POST for a normal table
	function _processPost(){
		if( empty($_POST) ) return false;
		if( $this->_multi == 'node' ){
			$post = array();
			foreach($_POST as $fieldName=>$fieldValue){
				// split at __, if count eq 2 continue
				$field = explode('__',$fieldName);
				if( count($field) == 2 ){
					// shuffle post values if tables match
					if( $field[0] == $this->table ){
						$post[$field[1]] = $fieldValue;
					}
				}
			}
			return $post;
		} else return $_POST;
	}

	// retrieve normalized data from another field
	function _getFieldData($fieldName){

		$SQLquery = 'SELECT `'
					.$this->normalized[$fieldName]['tableKey'].'` AS pkey'.
					', '
					.$this->normalized[$fieldName]['tableValue'].' AS value '.
					'FROM `'
					.$this->normalized[$fieldName]['tableName'].'` '.
					'WHERE '
					.$this->normalized[$fieldName]['whereClause'].' '.
					'ORDER BY '
					.$this->normalized[$fieldName]['orderBy'];

		$retrievedData = @mysql_query($SQLquery,$this->conn);
		if(@mysql_error()!=''){
			echo 'ERROR: Unable to retrieve normalized data from `'.$this->normalized[$fieldName]['tableName'].'`'.( $this->mysql_errors ? '<br/>'.mysql_error() : '' );
			return false;
		}

		$numPairs = @mysql_num_rows($retrievedData);
		$this->normalized[$fieldName]['pairs'] = $numPairs;

		for($i=0; $i<$numPairs; $i++){

			$set = @mysql_fetch_assoc($retrievedData);
			$this->normalized[$fieldName]['keys'][$i] = $set['pkey'];
			$this->normalized[$fieldName]['values'][$i] = $set['value'];

		}

	}

	// retrieve field labels from another field
	function _getFieldLabels($fieldName,$fieldOptions){

		$fieldOptions= "'".implode("','",$fieldOptions)."'";
		$SQLquery = "SELECT `"
					.$this->labelValues[$fieldName]['tableKey']."` AS pkey".
					", `"
					.$this->labelValues[$fieldName]['tableValue']."` AS value ".
					"FROM `"
					.$this->labelValues[$fieldName]['tableName']."` ".
					"WHERE `".$this->labelValues[$fieldName]['tableKey']."` IN(".$fieldOptions.")";

		$retrievedData = @mysql_query($SQLquery,$this->conn);
		if(@mysql_error()!=""){
			echo "ERROR: Unable to retrieve field labels from '".$this->labelValues[$fieldName]['tableName']."'.".($this->mysql_errors?"<br/>".mysql_error():"");
			return false;
		}

		$numPairs = @mysql_num_rows($retrievedData);

		for($i=0; $i<$numPairs; $i++){

			$set = @mysql_fetch_assoc($retrievedData);
			$this->labelValues[$fieldName][$set['pkey']] = $set['value'];

		}

	}

	// outputs a hidden field that gets checked on submit to
	// prevent empty set/enum fields from being overlooked when empty (i.e. no fields checked)
	function _putSetCheckField($name){
		if(!isset($this->pkeyID) || isset($this->rc4key)) return;
		$fieldTable = $this->_multi ? $this->table.'__' : '';
		echo '<input type="hidden" name="'.$fieldTable.'formitable_setcheck[]" value="'.$name.'"/>'."\n\n";
	}

	// prevent empty set/enum fields from being overlooked when empty (i.e. no fields checked)
	// cycle through formitable_setcheck POST variable to assign empty values if necessary
	function _setCheck(){
		if( isset($this->_post['formitable_setcheck']) )
		foreach($this->_post['formitable_setcheck'] as $key){
			$key = $this->decrypt($key);
			if(!isset($this->_post[$key])) $this->_post[$key]='';
		}
	}

	// checks magic quotes and returns value accordingly
	function _handle_magic_quotes($value,$add=false){
		if($add)
			return $this->_magic_quotes ? $value : addslashes($value);
		else
			return $this->_magic_quotes ? stripslashes($value) : $value;
	}

	// validate field
	function _validateField($fieldName,$fieldValue,$methodName){

		// special case for verify fields
		if($methodName == "_verify"){

			if( $this->_post[$fieldName] == $this->_post[str_replace("_verify","",$fieldName)] ) return true;
			else{ $this->errMsg[$fieldName] = "Values do not match"; return false; }

		} else if( @ereg($this->_validationMethod[$methodName]['regex'],$fieldValue) ){
			return true;
		} else {
			// test if custom error is set
			if( isset($this->validate[$fieldName]['err']) )
				$this->errMsg[$fieldName] = $this->validate[$fieldName]['err'];
			else // otherwise use default error
				$this->errMsg[$fieldName] = $this->_validationMethod[$methodName]['err'];
			return false;
		}

	}

	// check validation
	function _checkValidation(){

			// cycle through $this->_post variables to test for validation
			foreach($this->_post as $key=>$value){

				if( isset($this->skip[$key]) ) continue;

				// decrypt hidden values if encrypted
				if( isset($this->forced[$key]) && $this->forced[$key]=="hidden"){
					$this->_post[$key] = $value = $this->decrypt($value);
				}

				$validated = true;
				if( isset($this->validate[$key]) )
					$validated = $this->_validateField($key,$value,$this->validate[$key]['method']);

				// run callback if set and is callable
				if( isset($this->callback[$key]["post"]) && $validated ){

					$tmpValue = $this->callback[$key]["post"]($key,$value,$this->callback[$key]["args"]);
					if( isset($tmpValue["status"]) && $tmpValue["status"] == "failed"){
						$this->errMsg[$key] = $tmpValue["errMsg"];
						$validated = false;
					}
					else $this->_post[$key] = $tmpValue;

				}

				// special cases for unique and verify fields
				if( isset($this->unique[$key]) && $validated ) $this->_queryUnique($key);
				if( strstr($key,"_verify") && $validated ) $this->_validateField($key,$value,"_verify");

			}

			// test if there are errors from validation
			if( isset($this->errMsg) ) return -1;

	}

	// this function checks if a field value is unique (not already stored in a record)
	function _queryUnique($fieldName){

		$SQLquery = "SELECT `".$fieldName."` FROM ".$this->table." WHERE `".$fieldName."` ='".$this->_post[$fieldName]."'";
		// if updating make sure it doesn't select self
		if( isset($this->_post['pkey']) ) $SQLquery .= " AND ".$this->pkey." != '".$this->_post['pkey']."'";
		if( @mysql_num_rows(@mysql_query($SQLquery)) ) $this->errMsg[$fieldName] = $this->unique[$fieldName]['msg'];

	}

	// this function is used to apply a method to multiform nodes
	// $fieldName should be provided for methods that use dot syntax (table.field)
	function _applyMethod($method, $args=array(), $fieldName=false){

		// apply to a single node
		if( $fieldName && strpos($fieldName,'.') ){

			// parse $fieldName, expecting table name at $field[0] and field name at $field[1]
			$field = explode('.',$fieldName);
			// test if there's exactly 2 results and if the table is legit
			if( count($field)!=2 || !isset($this->tables[ $field[0] ]) ){ return false; }
			// replace the first arg if its the fieldname
			if( $args[0] == $fieldName ){ $args[0] = $field[1]; }

			return call_user_func_array( array(&$this->tables[ $field[0] ], $method), $args );

		// apply to each node
		} else {

			// apply the method to each node, updating the return value each time
			foreach( $this->tables as $table=>$form ){
				$result = call_user_func_array( array(&$form, $method), $args );
				switch( gettype($result) ){
					default:
					case 'boolean': $return = $result && (isset($return) ? $return : true); break;
					case 'integer': $return = $result + (isset($return) ? $return : 0); break;
					case 'array': // fallthrough
					case 'resource': $return[$table] = $result; break;
					case 'NULL': $return = null; break;
				} 
			}
			return $return;

		}

	}

	// this function is used by printForm to write the HTML for all label tags
	// args are field name and label text with optional css class, focus value and fieldset
	function _putLabel($fieldName, $fieldLabel, $css='text', $focus=true, $fieldSet=false){

		$inputName = $this->_multi ? $this->table.'__'.$fieldName : $fieldName;
		echo '<label class="'.$css.'label" for="'.$inputName.'">'.$fieldLabel.'</label>';
		if(!$fieldSet) echo $this->labelBreak; else echo $this->optionBreak;

	}

	// this function is called by _outputField. it returns the correct field value by
	// testing if a record has been retrieved using getRecord(), or the form was posted,
	// or a default value has been set.
	function _putValue($fieldName,$fieldType='text',$fieldValue=NULL){

		$retrieved = isset($this->record);
		if($retrieved){
			$recordValue = isset($this->defaultValues[$fieldName]['override']) ?
				$this->defaultValues[$fieldName]['value'] : $this->record[$fieldName];
		}

		$posted = isset($this->_post[$fieldName]);
		if($posted) $postValue = $this->_post[$fieldName];

		$default = isset($this->defaultValues[$fieldName]);
		if($default) $defaultValue = $this->defaultValues[$fieldName]['value'];

		switch($fieldType){

			case 'textarea':
				if( $posted && isset($postValue) )
					return $postValue;
				else if( $retrieved )
					return isset($this->callback[$fieldName]['retrieve']) ?
						$this->callback[$fieldName]['retrieve']($fieldName,$recordValue,$this->callback[$fieldName]['args'])
							: htmlspecialchars($recordValue);
				else if( isset($defaultValue) )
					return htmlspecialchars($defaultValue);
			break;

			case 'hidden':
			case 'text':
				if( isset($postValue) ){
					if( $fieldType=='hidden' && isset($this->rc4key) ){
						$postValue = $this->encrypt($postValue);
					} else {
						$postValue = htmlspecialchars($postValue);
					}
					return ' value="'.$postValue.'"';
				}
				else if( isset($recordValue) ){
					$value = isset($this->callback[$fieldName]['retrieve']) ?
						$this->callback[$fieldName]['retrieve']($fieldName,$recordValue,$this->callback[$fieldName]['args'])
							: $recordValue;
					if( $fieldType=='hidden' && isset($this->rc4key) ){
						$value = $this->encrypt($value);
					} else {
						$value = htmlspecialchars($value);
					}
					return ' value="'.$value.'"';
				}
				else if( isset($defaultValue) ){
					if( $fieldType=='hidden' && isset($this->rc4key) )
						$defaultValue = $this->encrypt($defaultValue);
					return ' value="'.$defaultValue.'"';
				}
				// accounts for default date & time formats
				else if( !is_null($fieldValue) )
					return ' value="'.$fieldValue.'"';
			break;

			case 'radio':
				$selectedText = ' checked';
			case 'select':
				if(!isset($selectedText)) $selectedText = ' selected';
				if( ($posted && $postValue == $fieldValue) ||
					(!$posted && $retrieved && $recordValue == $fieldValue) ||
					(!$posted && !$retrieved && $default && $defaultValue == $fieldValue)
				) return $selectedText;
			break;

			case 'checkbox':
				$selectedText = ' checked';
			case 'multi':
				if(!isset($selectedText)) $selectedText = ' selected';
				if(
					($posted && $postValue && preg_match( '/\b'.$fieldValue.'\b/', implode(",",$postValue) )) ||
					(!$posted && $retrieved && preg_match('/\b'.$fieldValue.'\b/', $recordValue)) ||
					(!$posted && !$retrieved && $default && preg_match('/\b'.$fieldValue.'\b/', $defaultValue))
				){ return $selectedText; }
			break;

		}

		return '';

	}

	// this function forms the core of the class;
	// it is called by public function printField and outputs a single field using a record offset
	function _outputField($n,$attr='',$verify=false){

		$name    = $this->fieldNames[$n];
		$flags   = $this->fields[$name];
		$type    = $flags['type'];
		$len     = $flags['length'];
		$subtype = @$flags['subtype'];
		$byForce = false;

		// check if multiple tables are enabled
		if($this->_multi){
			$inputName = $this->table.'__'.$name;
		} else { $inputName = $name; }

		// check if type is forced, set var accordingly
		if( isset($this->forced[$name]) ) $byForce = $this->forced[$name];

		// if hidden, set type to skip
		if( isset($this->hidden[$name]) ) $type = 'skip';
		else $this->signature[] = $name;

		// handle hidden type
		if( $byForce == 'hidden' ){
			echo '<input type="hidden" name="'.$inputName.'"'.$this->_putValue($name,'hidden').($attr!=''?' '.$attr:'')."/>\n";
			return;
		}

		// set custom label or uppercased-spaced field name
		if($verify) $verified='_verify'; else $verified='';
		if( isset($this->labels[$name.$verified]) ) $label = $this->labels[$name.$verified];
			else $label = ucwords( str_replace('_', ' ', $name.$verified) );

		// add error text to label if validation failed
		if( $this->feedback=='line' || $this->feedback=='both' ){

			// test if verify field and validation failed
			if( $verify && isset($this->errMsg[$name.'_verify']) ) $label .= $this->err_pre.$this->errMsg[$name.'_verify'].$this->err_post;
			// else test if regular field validation failed
			else if( isset($this->errMsg[$name]) && $byForce != 'button' ) $label .= $this->err_pre.$this->errMsg[$name].$this->err_post;

		}

		// set vars if normalized data was retrieved
		if( isset($this->normalized[$name]) ) $valuePairs = true;	else $valuePairs = false;

		// set vars if enum labels were retrieved
		if( isset($this->labelValues[$name]) ) $labelPairs = true;	else $labelPairs = false;

		switch($type){

			case 'real':
			case 'int':
				if($valuePairs){
					$this->_putLabel($name,$label,'select',false);
					$this->_getFieldData($name);
					echo '<select name="'.$inputName.'" id="'.$inputName.'" size="1"'.
						($attr && stristr($attr,'class=')?'':' class="select"').
						($attr!=''?' '.$attr:'').">\n";
					for($i=0;$i<$this->normalized[$name]['pairs'];$i++){
						echo '  <option value="'.$this->normalized[$name]['keys'][$i].'"'.
						$this->_putValue($name,'select',$this->normalized[$name]['keys'][$i]).'>'.
						$this->normalized[$name]['values'][$i]."</option>\n";
					}
					echo '</select>'.$this->fieldBreak;
				}
				else {
					$this->_putLabel($name,$label);
					if($len<$this->textInputLength) $length = $len; else $length=$this->textInputLength;
					echo '<input type="text" name="'.$inputName.'" id="'.$inputName.'" size="'.$length.'"'.
						($attr && stristr($attr,'maxlength=')?'': ' maxlength="'.$len.'"').
						($attr && stristr($attr,'class=')?'':' class="text"').
						$this->_putValue($name).($attr!=""?" ".$attr:"").'>'.$this->fieldBreak;
				}
			break;

			case 'blob':
				$this->_putLabel($name,$label);
				if( $byForce == 'file' ){
					echo '<input type="file" name="'.$inputName.'" id="'.$inputName.'" size="'.$this->fileInputLength.'"'.
						($attr && stristr($attr,'class=')?'':' class="file"').($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
				} else if( ($len>$this->strField_toggle || $byForce == 'textarea') && $byForce!='text' ){
					echo '<textarea name="'.$inputName.'" id="'.$inputName.'" rows="'.$this->textareaRows.'" cols="'.$this->textareaCols.'"'.
						($attr && stristr($attr,'class=')?'':' class="textarea"').($attr!=''?' '.$attr:'').
						'>'.$this->_putValue($name,'textarea').'</textarea>'.$this->fieldBreak;
				} else {
					echo '<input type="text" name="'.$inputName.'" id="'.$inputName.'" size="'.$this->textInputLength.'"'.
						($attr && stristr($attr,'maxlength=')?'': ' maxlength="'.$len.'"').
						($attr && stristr($attr,'class=')?'':' class="text"').
						$this->_putValue($name).($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
				}
			break;

			case 'string':

				if($subtype == 'enum'){

					if($valuePairs){
						$this->_getFieldData($name);
						$len=sizeof($this->normalized[$name]);
					} else {
						$options = $flags['options'];
						if($labelPairs) $this->_getFieldLabels($name,$options);
						$len=sizeof($options);
					}

					if( ($len > $this->enumField_toggle || $byForce == 'select') && $byForce != 'radio'){
						$this->_putLabel($name,$label,'',false);
						echo '<select name="'.$inputName.'" id="'.$inputName.'" size="1"'.
							($attr && stristr($attr,'class=')?'':' class="select"').($attr!=''?' '.$attr:'').">\n";

						if( $valuePairs ){
							for($i=0;$i<$this->normalized[$name]['pairs'];$i++)
								echo '	<option value="'.$this->normalized[$name]['keys'][$i].'"'.
									$this->_putValue($name,'select',$this->normalized[$name]['keys'][$i]).'>'.
									$this->normalized[$name]['values'][$i].'</option>'."\n";
						} else {
							foreach($options as $opt){
								if( isset($this->labelValues[$name][$opt]) ) $optionLabel=$this->labelValues[$name][$opt]; else $optionLabel=$opt;
								echo '	<option value="'.$opt.'"'.$this->_putValue($name,'select',$opt).'>'.$optionLabel."</option>\n";
							}
						}

						echo '</select>'.$this->fieldBreak;
					} else {
						if($this->enableFieldSets){
							echo '<fieldset class="fieldset">'."\n";
							echo '<legend class="legend">'.$label.'</legend>'."\n";
						} else $this->_putLabel($name,$label,"",false);
						if( $valuePairs )
							for($i=0;$i<$this->normalized[$name]['pairs'];$i++){
								echo '	<input type="radio" name="'.$inputName.'"'.
									' id="'.$inputName.'_'.$this->normalized[$name]['keys'][$i].'"'.
									' value="'.$this->normalized[$name]['keys'][$i].'"'.
									($attr && stristr($attr,'class=')?'':' class="radio"').
									$this->_putValue($name,'radio',$this->normalized[$name]['keys'][$i]).
									($attr!=''?' '.$attr:'').'/>';
								$this->_putLabel($name."_".$this->normalized[$name]['keys'][$i],$this->normalized[$name]['values'][$i],'radio',true,true);
							}
						else
						foreach($options as $opt){
							if( isset($this->labelValues[$name][$opt]) ) $optionLabel=$this->labelValues[$name][$opt]; else $optionLabel=$opt;
							echo '	<input type="radio" name="'.$inputName.'" id="'.$inputName.'_'.$opt.'"'.
								' value="'.$opt.'"'.($attr && stristr($attr,'class=')?'':' class="radio"').
								 $this->_putValue($name,'radio',$opt).($attr!=''?' '.$attr:'').'/>';
							$this->_putLabel($name."_".$opt,$optionLabel,'radio',true,true);
						}
						if($this->enableFieldSets) echo '</fieldset><br/>'."\n\n";
					}

				} else if($subtype == 'set') {

					if( $valuePairs ){
						$this->_getFieldData($name);
						$len=sizeof($this->normalized[$name]);
					}
					else {
						$options = $flags['options'];
						if($labelPairs) $this->_getFieldLabels($name,$options);
						$len=sizeof($options);
					}
					if( ($len > $this->enumField_toggle || $byForce == 'multiselect') && $byForce != 'checkbox' ){
						$this->_putLabel($name,$label,"",false);
						echo '<select name="'.$inputName.'[]" id="'.$inputName.'"'.
							($attr && stristr($attr,'size=')?'':' size="'.$this->multiSelectSize.'"').
							' multiple="multiple"'.($attr && stristr($attr,'class=')?'':' class="multiselect"').($attr!=''?' '.$attr:'').">\n";
						if( $valuePairs ){
							for($i=0;$i<$this->normalized[$name]['pairs'];$i++){
								echo '	<option value="'.$this->normalized[$name]['keys'][$i].'"'.
									$this->_putValue($name,"multi",$this->normalized[$name]['keys'][$i]).'>'.
									$this->normalized[$name]['values'][$i].'</option>'."\n";
							}
						} else {
							foreach($options as $opt){
								if( isset($this->labelValues[$name][$opt]) ) $optionLabel=$this->labelValues[$name][$opt]; else $optionLabel=$opt;
								echo '	<option value="'.$opt.'"'.$this->_putValue($name,"multi",$opt).'>'.$optionLabel.'</option>'."\n";
							}
						}
						echo '</select>'.$this->fieldBreak;
					} else {
						if($this->enableFieldSets){
							echo '<fieldset class="fieldset">'."\n";
							echo '<legend class="legend">'.$label.'</legend>'."\n";
						} else $this->_putLabel($name,$label,"",false);
						$cb=0;
						if( $valuePairs )
							for($i=0;$i<$this->normalized[$name]['pairs'];$i++){
								echo '	<input type="checkbox" name="'.$inputName.'[]" id="'.$inputName.'_'.$cb.'"'.
									' value="'.$this->normalized[$name]['keys'][$i].'"'.
									$this->_putValue($name,'checkbox',$this->normalized[$name]['keys'][$i]).
									($attr!=''?' '.$attr:'').'/>';
								$this->_putLabel($name.'_'.$cb,$this->normalized[$name]['values'][$i],'checkbox',true,true);
								$cb++;
							}
						else
							foreach($options as $opt){
								if( isset($this->labelValues[$name][$opt]) ) $optionLabel=$this->labelValues[$name][$opt]; else $optionLabel=$opt;
								echo '	<input type="checkbox" name="'.$inputName.'[]" id="'.$inputName.'_'.$cb.'"'.
									' value="'.$opt.'"'.$this->_putValue($name,'checkbox',$opt).($attr!=''?' '.$attr:'').'/>';
								$this->_putLabel($name.'_'.$cb,$optionLabel,'checkbox',true,true);
								$cb++;
							}
						if($this->enableFieldSets) echo '</fieldset><br/>'."\n\n";
					}
					$this->_putSetCheckField($name);

				} else { // plain text field

					if($verify) $inputName = $inputName.'_verify';
					if( $byForce != 'button' ){ $this->_putLabel($name,$label); }
					if($len < $this->textInputLength) $length = $len; else $length=$this->textInputLength;

					if( ($len>$this->strField_toggle || $byForce == 'textarea') &&
						$byForce != 'text' && $byForce != 'file' ){
						echo '<textarea name="'.$inputName.'" id="'.$inputName.'" rows="'.$this->textareaRows.'"'.
							' cols="'.$this->textareaCols.'"'.($attr && stristr($attr,'class=')?'':' class="textarea"').
							($attr!=''?' '.$attr:'').'>'.$this->_putValue( (isset($this->_post[$name])?$name:str_replace('_verify','',$name)),'textarea' ).'</textarea>'.$this->fieldBreak;
					} else {
						if( $byForce == 'file' ){
							echo '<input type="file" name="'.$inputName.'" id="'.$inputName.'" size="'.$this->fileInputLength.'"'.
							($attr && stristr($attr,'class=')?'':' class="file"').($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
						} else if( $byForce == 'button' ){
							echo '<input type="button" name="'.$inputName.'" id="'.$inputName.'" value="'.$label.'"'.
							($attr && stristr($attr,'class=')?'':' class="button"').($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
						} else {
							$fieldType = ($byForce=='password' ? 'password' : 'text');
							echo '<input type="'.$fieldType.'" name="'.$inputName.'" id="'.$inputName.'"'.
								' size="'.$length.'" maxlength="'.$len.'"'.
								($attr && stristr($attr,'class=')?'':' class="text"').
								$this->_putValue( (isset($this->_post[$name])?$name:str_replace('_verify','',$name)) ).
								($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
						}
					}

				}
			break;

			case 'date':
				$fieldVals['date']		= array('size'=>'10',	'default'=>date('Y-m-d'));

			case 'datetime':
				$fieldVals['datetime']	= array('size'=>'19',	'default'=>date('Y-m-d H:i:s'));

			case 'timestamp':
				$fieldVals['timestamp']	= array('size'=>$len,	'default'=>time());

			case 'time':
				$fieldVals['time']		= array('size'=>'8',	'default'=>date('H:i:s'));

			case 'year':
				$fieldVals['year']		= array('size'=>'4',	'default'=>date('Y'));

				$this->_putLabel($name,$label);
				echo '<input type="text" name="'.$inputName.'" id="'.$inputName.'" size="'.$fieldVals[$type]['size'].'"'.
					' maxlength="'.$fieldVals[$type]['size'].'"'.$this->_putValue($name,"text",$fieldVals[$type]['default']).
					($attr && stristr($attr,'class=')?'':' class="text"').($attr!=''?' '.$attr:'').'/>'.$this->fieldBreak;
			break;

			case 'skip':
			break;

		} // end switch

	} // end _outputField

} // end Formitable class


// RC4Crypt 3.2 (C) Copyright 2006 Mukul Sabharwal [http://mjsabby.com] All Rights Reserved
class rc4crypt {
	function _crypt ($pwd, $data, $ispwdHex = 0){
		if ($ispwdHex)
			$pwd = @pack('H*', $pwd); // valid input, please!

		$key[] = '';
		$box[] = '';
		$cipher = '';

		$pwd_length = strlen($pwd);
		$data_length = strlen($data);

		for ($i = 0; $i < 256; $i++){
			$key[$i] = ord($pwd[$i % $pwd_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++){
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $data_length; $i++){
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipher .= chr(ord($data[$i]) ^ $k);
		}
		return $cipher;
	}
	function _encrypt ($pwd, $data, $ispwdHex = 0){
		return rawurlencode($this->_crypt($pwd, $data, $ispwdHex));
	}
	function _decrypt ($pwd, $data, $ispwdHex = 0){
		//return $this->_crypt($pwd, urldecode(get_magic_quotes_gpc()?stripslashes($data):$data), $ispwdHex);
		return $this->_crypt($pwd, urldecode($data), $ispwdHex);
	}
}

?>
