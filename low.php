<?php
$html .= "<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>";

if( isset( $_GET[ 'Login' ] ) ) {
  $user = mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  stripslashes($_GET[ 'username' ]));

  $pass = $_GET[ 'password' ];
  $pass = md5( $pass );

  $query = $db->prepare( 'SELECT * FROM users WHERE user = (:user) LIMIT 1;' );
  $query->bindParam( ':user', $user, PDO::PARAM_STR);
  $query->execute();
  if ($query->rowCount() == 1) {
    $row = $query->fetch();
    $account_locked = False;
    $captcha_required = False;
    $last_failed = strtotime( $row["last_failed"] );
	$failed_recently = time() < $last_failed + (5 * 60);

    if ($row["failed_login"] >= 10) {
      if ($failed_recently)
        $account_locked = True;
    }
    if ($row["failed_login"] >= 2){
      if ($failed_recently){
        $html .= "<script>document.getElementById(\"captcha\").style.display = \"block\";</script>";
        $captcha_required = true;
      }
    }

    $captcha_failed = false;

    if ($captcha_required)
    {
      $response = $_GET["g-recaptcha-response"];
      $url = 'https://www.google.com/recaptcha/api/siteverify';
      $data = [
        'secret' => '6LeRSgYjAAAAAInmUFMNWoVLMPcBZzDfbgGESi3l',
        'response' => $response
      ];
      $options = [
        'http' => [
          'method' => 'POST',
          'content' => http_build_query($data)
        ]
      ];
      $context  = stream_context_create($options);
      $verify = file_get_contents($url, false, $context);
      $captcha_success=json_decode($verify);

      if ($captcha_success->success==false)
        $captcha_failed = true;
    }

    if ($row["password"] == $pass && !$account_locked && !$captcha_failed){
		$avatar = $row["avatar"];
  
		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";
  
		$query = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
		$query->bindParam( ':user', $user, PDO::PARAM_STR );
		$query->execute();
	  }
	  else{
		$min_sleep = 0;
		if ($row["failed_login"] >= 3 && time() < $last_failed + (5 * 60))
		  $min_sleep = ($row["failed_login"] - 2) * 10;
		sleep( rand( $min_sleep + 0, $min_sleep + 3 ) );
		// Login failed
		if ($captcha_failed)
		  $html .= "<pre><br />Please enter captcha.</pre>";
		else if (!$account_locked)
		  $html .= "<pre><br />Username and/or password incorrect.</pre>";
		else
		  $html .= "<pre><br />Account has been temporary locked. Please try again later.</pre>";
  
		$query = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1), last_failed = now() WHERE user = (:user) LIMIT 1;' );
		$query->bindParam( ':user', $user, PDO::PARAM_STR );
		$query->execute();
	  }
	}
	else {
	  sleep( rand( 10, 60 ) );
	  $html .= "<pre><br />Username and/or password incorrect.</pre>";
	}
  }
  
  ?>