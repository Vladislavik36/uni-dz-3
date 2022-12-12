<?php
// CWE-352: Cross-Site Request Forgery
// CWE-307: Improper Restriction of Excessive Authentication Attempts
if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ]; // CWE-20: Improper Input Validation
	// Get password
	$pass = $_GET[ 'password' ]; // CWE-20: Improper Input Validation
	$pass = md5( $pass ); // CWE-327: Use of a Broken or Risky Cryptographic Algorithm
	// Check the database
	$query  = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';"; // CWE-943: Improper Neutralization of Special Elements in Data Query Logic; LIMIT 1 ????

	// CWE-526: Exposure of Sensitive Information Through Environmental Variables
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];
		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>"; // CWE-79: Failure to Preserve Web Page Structure ('Cross-site Scripting')
		$html .= "<img src=\"{$avatar}\" />"; // CWE-79: Failure to Preserve Web Page Structure ('Cross-site Scripting')
	}
	else {
		// CWE-208: Observable Timing Discrepancy
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}
	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res); // CWE-526: Exposure of Sensitive Information Through Environmental Variables
}
?>