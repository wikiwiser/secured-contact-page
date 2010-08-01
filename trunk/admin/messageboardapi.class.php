<?php
  /**
   * Message Board API Class
   *     
   * messageboardapi.class.php
   *     
   * @copyright Oleg Karp, 2010
   */
  
  //load encryptor/decryptor 
  require_once("utils.php");
   
  //message board api server key
  define("MSG_BOARD_API_SERVER_KEY", "sEa45fsKf~dg8HfgN7!MShf0hGh(4m3kkj");
  //message board user/pass pair
  define("MSG_BOARD_API_AUTH_USER", "oleg");
  define("MSG_BOARD_API_AUTH_PASS", "password");
  
  //database params
  //use this syntax: <user>:<pass>@<host>//<database>
  define("MSG_BOARD_API_DSN", "teleweb_oleg:password@db.wikiwiser.com//teleweb_msg_board");
  //used to parse the dsn
  define("MSG_BOARD_API_DSN_PARSER", "/^(([^:]+):([^@]+)@)([^:\/]+)".
                                                       "(\/\/([^\/]+))*$/i");
										                                    
  //default number of messages to return
  define("MSG_BOARD_API_DEF_LIMIT", 10);

  class MessageBoardAPI {
    private $data;   //request data
    private $dblink; //db link
  
    /**
     * Constructor
     */         
    public function __construct($params) {
      //check the params
      if(empty($params) || !isset($params["data"]) || !isset($params["iv"]) ||
         empty($params["data"]) || empty($params["iv"]) || count($params) != 2)
        $this->error("Params are wrong!");

      //decrypt the request  
      $this->data = unserialize($this->decrypt($params));

      //check the auth data
      if(!isset($this->data["user"]) || $this->data["user"] != MSG_BOARD_API_AUTH_USER ||
         !isset($this->data["pass"]) || $this->data["pass"] != MSG_BOARD_API_AUTH_PASS)
         $this->error("Access denied!");
         
      //check the action
      if(is_null($this->data["action"]) || $this->data["action"] != "submit") 
        //action is not submit
        $this->view(); 
      else
        $this->submit();
    }
    
    //send an error message to the client
    private function error($msg, $object = "") {
      echo $this->encrypt(serialize(array(
        "error" => $msg,
        "error_object" => $object    
      )));
      exit;
    }
    
    //send a success message to the client
    private function success() {
      echo $this->encrypt(serialize(array(
        "success" => "1"    
      )));
      exit;
    }
    
    //encrypts data to be sent from API
    private function encrypt($result) {
      $enc = encrypt($result, MSG_BOARD_API_SERVER_KEY);
              
      return serialize(array("result" => base64_encode($enc[0]), "iv" => base64_encode($enc[1])));
    }
    
    //decrypts data received from client
    private function decrypt($request) {
      if(empty($request))
        throw new Exception("An error occured: no request provided!");
    
      list($data, $iv) = array_values($request);
      
      if(!isset($data) || !isset($iv) || empty($data) || empty($iv))
        throw new Exception("An error occured: request format is wrong!");
        
      //get the IV
      $iv = base64_decode($iv);
      $data = base64_decode($data);
      
      return decrypt($data, MSG_BOARD_API_SERVER_KEY, $iv);
    }
    
    //last message of the specified user to the given dept
    private function getLastMessageByUser($username, $dept) {
      return ($res = $this->query("SELECT `from`, `to`, `message`, `timestamp`, `ip` FROM `messages` WHERE `from`=\"{$this->escape($username)}\" AND `to`=\"{$this->escape($dept)}\" ORDER BY `timestamp` DESC LIMIT 1;")) ? $res->fetch_array(MYSQLI_ASSOC) : false;  
    }
    
    //last message of the specified ip to the given dept
    private function getLastMessageByIP($ip, $dept) {
      return ($res = $this->query("SELECT `from`, `to`, `message`, `timestamp`, `ip` FROM `messages` WHERE `ip`=\"{$this->escape($ip)}\" AND `to`=\"{$this->escape($dept)}\" ORDER BY `timestamp` DESC LIMIT 1;")) ? $res->fetch_array(MYSQLI_ASSOC) : false;  
    }
    
    //send the messages
    private function view() {
      //bring the messages from the database
      $result = $this->getMessages(
        (isset($this->data["limit"])) ? $this->data["limit"] : MSG_BOARD_API_DEF_LIMIT,
        (isset($this->data["starttime"])) ? $this->data["starttime"] : 0 
      );
            
      if(is_array($result))
        //send the resultset to the client
        echo $this->encrypt(serialize($result));
      else
        $this->error("There's an error occured while getting the messages from the database!");
    }
    
    //get the messages from the db
    private function getMessages($limit = 10, $starttime = 0) {
      $sql = "SELECT * FROM `messages`";
      
      //add starttime timestamp check
      if(!empty($starttime) && is_numeric($starttime))
        $sql .= " WHERE `timestamp`>{$starttime}";
      
      //newest first  
      $sql .= " ORDER BY `timestamp` DESC";
      
      //check the limit
      if(!empty($limit) && is_numeric($limit) && isDecimalNumber($limit))
        $sql .= " LIMIT ".$limit;

      //run the query
      $res = $this->query($sql);
      
      $return = array();
      if($res->num_rows > 0)
        while($record = $res->fetch_array(MYSQLI_ASSOC)) {
          $return[] = $record;  
        }
      
      return $return;
    }
    
    //run db query
    private function query($sql) {
      //check if connection already open
      if(!isset($this->dblink) || !$this->dblink instanceof mysqli)
        $this->connectDB();
		
      //run query	  
      $result = $this->dblink->query($sql);
			
      if($result === false)
        throw new Exception("The db query failed!");
			  
      return $result;
    }
    
    //connect to the db
    private function connectDB() {
      //check if the mysqli module is installed
      if(!@extension_loaded('mysqli') || !function_exists('mysqli_connect'))
        throw new Exception("MySQLi is not installed!");
    
      //check if connection already open
      if(isset($this->dblink) && $this->dblink instanceof mysqli &&
	 empty($this->dblink->connect_error) && $this->dblink->ping())
        return true;
      
      //use the given DSN  
      if(preg_match(MSG_BOARD_API_DSN_PARSER, MSG_BOARD_API_DSN, $dsn)) {
        //connect
        $connection = new mysqli(
                                 $dsn[4], //hostname
                                 $dsn[2], //username
                                 $dsn[3], //password
                                 $dsn[6]  //database
                                );

        //check there's no error		
        if(!$connection->connect_error)
          $this->dblink = $connection;
        else
          throw new Exception("Can't connect to the database server: ".$connection->connect_error);
	
        //change the charset to utf8 (if required)
        $this->dblink->set_charset('utf8');
        $this->query("SET character_set_results = 'utf8', ".
                     "character_set_client = 'utf8', ".
                     "character_set_connection = 'utf8', ".
                     "character_set_database = 'utf8', ".
                     "character_set_server = 'utf8'");
      }
      else
        throw new Exception("The given DSN is wrong!");
    }
      
    //escape string using real_escape_string
    private function escape($string) {
      //can't escape without connection
      if(!isset($this->dblink))
        $this->connectDB();
        
      return $this->dblink->real_escape_string($string);
    }
    
    //check the last message
    private function checkLastMessage($message_array) {
      if(is_array($message_array) && !empty($message_array)) {
        list($from, $to, $msg, $timestamp, $ip) = array_values($message_array);
        
        if($timestamp > time()-(60*60*24) && $to == $this->data['to'])
          return false;
      }
      
      return true;
    }
    
    //add a new message
    private function submit() {
      //get last message from this user
      $lastmsg = $this->getLastMessageByUser($this->data["from"], $this->data["to"]);
      if(!$this->checkLastMessage($lastmsg))
        $this->error("You can't submit more than one message per day to the same department!", "to");

      //get last message from this ip
      $lastmsg = $this->getLastMessageByIP($this->data["ip"], $this->data["to"]);
      if(!$this->checkLastMessage($lastmsg))
        $this->error("You can't submit more than one message per day to the same department!", "to");
    
      //submit the message
      $now = time();
      $this->query("INSERT INTO `messages` SET `from`=\"{$this->escape($this->data['from'])}\", `to`=\"{$this->escape($this->data['to'])}\", `message`=\"{$this->escape($this->data['msg'])}\", `timestamp`=\"{$now}\", `ip`=\"{$this->escape($this->data['ip'])}\";");
      
      $this->success();
    }

    //destructor
    public function __destruct() {
      //close mysql connection if any
      if(isset($this->dblink) && $this->dblink instanceof mysqli)
        @$this->dblink->close();
    }
  }
?>
