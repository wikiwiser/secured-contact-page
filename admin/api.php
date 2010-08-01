<?php
  /**
   * Message Board API Script
   *     
   * api.php
   *     
   * @copyright Oleg Karp, 2010
   */
   
  //load the MessageBoardAPI Class
  require_once("messageboardapi.class.php");
  
  $api = new MessageBoardAPI($_POST);
?>
