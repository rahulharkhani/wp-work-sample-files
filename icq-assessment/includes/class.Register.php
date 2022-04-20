<?php

class IcqRegister{
	
	public function __construct(){
		add_filter('rewrite_rules_array', array($this, 'signup_rewrite_rules'));
		add_filter('query_vars', array($this, 'register_signup_query_var'));
		add_filter('template_include', array($this, 'register_template'), 99);
	}

	public function signup_rewrite_rules($rules){
		$new_rule = array('register/?$' => 'index.php?signup=independence', 'licensee-register/?$' => 'index.php?signup=licensee', 'trainer-register/?$' => 'index.php?signup=trainer','mt-register/?$' => 'index.php?signup=mastertrainer');	
		$rules = $new_rule + $rules;
		
		return $rules;
	}

	public function register_signup_query_var($vars){
		$vars[] = 'signup';
		$vars[] = 'signup_user_type';	
		return $vars;
	}
	
	public function register_template($template){
		$signup = get_query_var('signup');
		
		switch ($signup) {
			case 'independence' :
				return get_template_directory() . '/independence-register.php';
				
			case 'licensee' :
				return get_template_directory() . '/licensee-register.php';
				
			case 'trainer' :
				return get_template_directory() . '/trainer-register.php';
				
			case 'mastertrainer' :
				return get_template_directory() . '/mt-register.php';	
		}
		
		return $template;
	}

	public function get_trainer_added_by_id_for_register($trainer_added_id) {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM wp_trainer_added_users WHERE id='" . $trainer_added_id . "'");
		
		return $result;
	}
	
	
	public function get_trainer_added_by_ref_code($code, $ref) {
		global $wpdb;
		$trainer_added_id = $this->secret_key_decode($code);
		$result = $wpdb->get_row("SELECT * FROM wp_trainer_added_users WHERE id = '" . $trainer_added_id . "' AND reference_code = '" . $ref . "'");
		
		return $result;
	}
	
	public function get_mt_trainer_added_by_ref_code($code, $ref) {
		global $wpdb;
		$trainer_added_id = $this->secret_key_decode($code);
		$result = $wpdb->get_row("SELECT * FROM wp_mt_trainer_added_users WHERE id = '" . $trainer_added_id . "' AND reference_code = '" . $ref . "'");
		return $result;
	}
	
	public function secret_key_encode($plaintext) {
		$password = 'icq.assessment';
		$method = 'aes-256-cbc';

		$password = substr(hash('sha256', $password, true), 0, 16);
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

		if (is_numeric($plaintext)) {
			$EncodeValue = $plaintext + 427456253;
			$encrypted = base64_encode($EncodeValue);
		}else{
			$encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
		}
		return $encrypted;
	}

	public function secret_key_decode($key) {
		$password = 'icq.assessment';
		$method = 'aes-256-cbc';
		$password = substr(hash('sha256', $password, true), 0, 16);
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

		$DecodeValue = base64_decode($key);
		if (is_numeric($DecodeValue)) {
			$decrypted = $DecodeValue - 427456253;
		}else{
			$decrypted = openssl_decrypt(base64_decode($key), $method, $password, OPENSSL_RAW_DATA, $iv);
		}
		return $decrypted;
	}
	
}

$icq_register = new IcqRegister();
