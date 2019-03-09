<?php

// Starchat Class v0.9.0

// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

namespace Starchat;

class Starchat {
	private $mysql;

	protected $username;
	private $password;
	private $token;

	// Used for formatting error messages and kills the script
	private function starchat_error($message, $type = "starchat_error") {
		$json_message = json_encode(array($type=>$message));
		die($json_message);
		$mysql->close();
	}

	// Example params: "SELECT * FROM messages", true, "s", variable, "i", variable
	// Usage: (query, return_results, type, data)
	// Starchats custom clean wrapper function to prepare, bind, and execute a query
	public function starchat_sql() {
		$arg_array = func_get_args();
		$arg_count = func_num_args();

		$sql_query = $arg_array[0];
		$param_stack = array();
		$param_types = "";

		// Start at 2 to ignore query and return_results
		for($i = 2; $i < $arg_count-1; $i += 2) {
			array_push($param_stack, $arg_array[$i+1]);
			$param_types .= $arg_array[$i];
		}

		array_unshift($param_stack, $param_types);

		// Make param_stack use references
		$param_reference = array();
		foreach ($param_stack as $ind => $value) {
			$param_reference[$ind] = &$param_stack[$ind];
		}

		$result = $this->mysql->prepare($sql_query);
		call_user_func_array(array($result, 'bind_param'), $param_reference);
		$result->execute();

		// Return result so you can use the data
		if ($arg_array[1] === true) {
			return $result->get_result();
		}else{
			return $result;
		}
	}

	// Generate secure strings on multiple versions
	private function generate_secure_string($length = 32) {
		if (function_exists("random_bytes")) {
			return bin2hex(random_bytes($length));
		} elseif (function_exists("openssl_random_pseudo_bytes")) {
			return bin2hex(openssl_random_pseudo_bytes($length));
		} else {
			$this->starchat_error("Secure random number not generated, your PHP version is most likely out of date");
		}
	}

	private function check_login($username, $password) {
		$login_results = $this->starchat_sql("SELECT * FROM accounts WHERE username = ?", true, "s", $username);
		$login_rows = $login_results->num_rows;

		if ($login_rows === 0) {
			return false;
		}

		while($row = $login_results->fetch_assoc()) {
			if (password_verify($password, $row['password'])) {
				return true;
			}else{
				// Password did not match
				return false;
			}
		}
		return false;
	}

	// Returns boolean if token is found and assigns username
	private function check_token($token) {
		$token_check = $this->starchat_sql("SELECT * FROM tokens WHERE token = ?", true, "s", $token);
		$token_rows = $token_check->num_rows;

		if ($token_rows !== 1) {
			return false;
		}

		while($row = $token_check->fetch_assoc()) {
			if ($token === $row["token"]) {
				// Since token was found, set username
				$this->username = $row["username"];
				return true;
			}
		}
		return false;
	}

	// Next 2 functions simply set variables
	// Refer to check_token and check_login for actually verifying them

	// Needed to generate token
	public function set_login($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	public function set_token($token) {
		$this->token = $token;
	}

	public function generate_token() {
		// In order to generate a token, we need to login first
		if ($this->check_login($this->username, $this->password) === false) {
			$this->starchat_error("Login failed, please verify the credentials");
		}
		$is_token_created_loop = 1;
		// Incase of token collision, regenerate token if collision detected
		while ($is_token_created_loop !== 0) {
			$token = $this->generate_secure_string();
			$token_date = date("Y-m-d H:i:s");

			$check_token_result = $this->starchat_sql("SELECT token FROM tokens WHERE token=?", true, "s", $token);
			$check_token_rows = $check_token_result->num_rows;

			if ($check_token_rows > 0) {
				if ($is_token_created_loop > 15) {
					$this->starchat_error("Token collision occured too many times. Possible server error");
					return false;
				}else{
					$is_token_created_loop++;
				}
			}else{
				// Token created successfully, lets break the loop
				$is_token_created_loop = 0;
			}
		}

		$this->starchat_sql("INSERT INTO tokens (token, username, created) VALUES (?, ?, ?)", false,
			"s", $token,
			"s", $username,
			"s", $token_date);

		// Return the token in JSON
		$json_token = json_encode(array("token"=>$token));
		return $json_token;
	}

	function __construct($conn) {
		$this->mysql = $conn;
	}
}

?>