<?php
  /**
   * Message Board Script
   *     
   * index.php
   *     
   * @copyright Oleg Karp, 2010
   */        

  //we are implementing only the contact page
  if(empty($_GET) || is_null($_GET["page"]) || $_GET["page"] != "contact") {
    header("Location: /contact");
    exit;
  }
  
  //load the MessageBoard Class
  require_once("messageboard.class.php");
  
  $msg_board = new MessageBoard();
  
  //check the action
  if(is_null($_GET["action"]) || $_GET["action"] != "submit") 
    //action is not submit
    $msg_board->view();
  else
    //action is submit
    call_user_func_array(array($msg_board, "submit"), !empty($_POST) ? $_POST : array());
?>
