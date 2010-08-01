<?php
  /**
   * MessageBoard Class
   * 
   * messageboard.class.php
   * 
   * @copyright Oleg Karp, 2010            
   */
  
  //load encryptor/decryptor 
  require_once("utils.php");     
   
  //message board api server
  define("MSG_BOARD_API_SERVER", "teleweb-adm.vkarp.com");
  //message board user/pass pair
  define("MSG_BOARD_API_AUTH_USER", "oleg");
  define("MSG_BOARD_API_AUTH_PASS", "password");
  //message board api server key
  define("MSG_BOARD_API_SERVER_KEY", "sEa45fsKf~dg8HfgN7!MShf0hGh(4m3kkj");
   
  class MessageBoard {
    //outputs the 'send a message' form    
    public function view() {
      require_once("index.tpl");
    }
    
    //submits a new message to the API
    public function submit($from = "", $to = "", $msg = "") {
      //response array
      $resp = array();
      
      if(empty($from))
        //'from' is empty
        $resp = array(
          "error" => "You didn't fill the 'From' field!",
          "error_object" => "from"
        );  
      elseif(empty($to))
        //'to' is empty
        $resp = array(
           "error" => "You didn't select a department!",
           "error_object" => "to"
        );
      elseif(empty($msg))
        //'message' is empty
        $resp = array(
          "error" => "Your message is empty!",
          "error_object" => "msg"
        );
      else
        //call a message board API
        $resp = $this->callMessageBoardAPI(array(
          "action" => "submit",
          "user" => MSG_BOARD_API_AUTH_USER,
          "pass" => MSG_BOARD_API_AUTH_PASS,
          "from" => $from,
          "to" => $to,
          "msg" => $msg,
          "ip" => $_SERVER['REMOTE_ADDR']
        ));
      
      //output the result
      $this->response($resp);
    }
    
    //send a response to the client using JSON encode
    protected function response($resp) {
      echo json_encode($resp);
    }
    
    //encrypts data to be sent to API
    private function encrypt($data) {
      $enc = encrypt($data, MSG_BOARD_API_SERVER_KEY);

      return array("data" => base64_encode($enc[0]), "iv" => base64_encode($enc[1]));
    }
    
    //decrypts data received from API
    private function decrypt($data) {
      if(empty($data))
        throw new Exception("An error occured: no data provided!");
    
      list($result, $iv) = array_values($data);
      
      if(!isset($result) || !isset($iv) || empty($result) || empty($iv))
        throw new Exception("An error occured: data format is wrong!");
             
      //get the IV and 
      $iv = base64_decode($iv);
      $result = base64_decode($result);
      
      return decrypt($result, MSG_BOARD_API_SERVER_KEY, $iv);
    }
    
    protected function callMessageBoardAPI($params) {
      //submit a message via message board API
      $curl = curl_init();

      //set URL and other appropriate options
      curl_setopt($curl, CURLOPT_URL, "http://".MSG_BOARD_API_SERVER."/api");
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $this->encrypt(serialize($params)));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

      //run request
      $result = unserialize(trim(curl_exec($curl)));

      //close cURL resource, and free up system resources
      curl_close($curl);
      
      return unserialize($this->decrypt($result));
    }
  }
?>
