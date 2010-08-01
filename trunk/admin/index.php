<?php
 /**
   * Message Board Admin Script
   *     
   * index.php
   *     
   * @copyright Oleg Karp, 2010
   */
   
  //allowed users
  $auth = array("oleg" => "password");

  //we are implementing only the admin page
  if(empty($_GET) || is_null($_GET["page"]) || $_GET["page"] != "admin") {
    header("Location: /admin");
    exit;
  }

  //authentication
  //we need the following trick, because PHP runs on this server as CGI
  $userpass = explode(":", base64_decode(substr($_SERVER["REDIRECT_HTTP_AUTH"],6)));

  if(count($userpass) == 2)
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = $userpass;

  //authentication using HTTP Auth Basic
  if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
     !isset($auth[$_SERVER['PHP_AUTH_USER']]) || $auth[$_SERVER['PHP_AUTH_USER']] != $_SERVER['PHP_AUTH_PW']) {
    header('WWW-Authenticate: Basic realm="Secure Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Access denied!";
    exit;
  }
  
  //load the MessageBoardAdmin Class
  require_once("messageboardadmin.class.php");
  
  $msg_board = new MessageBoardAdmin($_POST);
?>
