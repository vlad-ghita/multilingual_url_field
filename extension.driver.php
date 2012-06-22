<?php

	if( !defined('__IN_SYMPHONY__') ) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');



	require_once(EXTENSIONS.'/url_field/extension.driver.php');



	class Extension_Multilingual_URL_Field extends Extension_URL_Field
	{
		public function __construct(){
			parent::__construct();

			$this->field_table = 'tbl_fields_multilingual_url';
		}
	}
