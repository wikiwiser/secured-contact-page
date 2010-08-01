<?php
  /**
   * Message Board Admin Class
   *     
   * messageboardadmin.class.php
   *     
   * @copyright Oleg Karp, 2010
   */
   
  require_once("messageboard.class.php");

  class MessageBoardAdmin extends MessageBoard {
    /**
     * Constructor
     */
    public function __construct($params) {
      $request_params = array(
        "action" => "view",
        "user" => MSG_BOARD_API_AUTH_USER,
        "pass" => MSG_BOARD_API_AUTH_PASS
      );
    
      //check the params
      if(empty($params) || !is_array($params)) {  
        //show the admin page
        $data = $this->callMessageBoardAPI($request_params);
        
        $this->view($this->messagesToHTML($data));
      }
      else {
        //should be ajax request
        //start from timestamp
        if(isset($params["starttime"]) && is_numeric($params["starttime"]))
          $request_params["starttime"] = $params["starttime"];
          
        //limit
        if(isset($params["limit"]) && is_numeric($params["limit"]))
          $request_params["limit"] = $params["limit"];
        
        //send the required data
        $data = $this->callMessageBoardAPI($request_params);
        
        $this->response($this->messagesToHTML($data));
      }      
    }  
    
    public function messagesToHTML($data = array()) {
      $html = array();
      
      if(!empty($data) && is_array($data))
        foreach($data as $record) {
          //some sanitization
          $record["from"] = htmlentities($record["from"], ENT_COMPAT, "UTF-8");
          $record["to"] = htmlentities($record["to"], ENT_COMPAT, "UTF-8");
          $record["message"] = htmlentities($record["message"], ENT_COMPAT, "UTF-8");
          $record["time"] = date("l jS \of F Y h:i:s A", $record["timestamp"]);
          
          //create html for the record
          $html[] = <<<EOF
<div class="record">
  <div class="timestamp">
  <b>Sent:</b> {$record["time"]}
  <div id="starttime">{$record["timestamp"]}</div>
  </div>
  <div class="ip">
  <b>IP:</b> {$record["ip"]}
  </div>
  <div class="from">
  <div>From:</div><div>{$record["from"]}</div>
  </div>
  <div class="to">
  <div>To:</div><div>{$record["to"]}</div>
  </div>
  <div class="message">
  <div>Message:</div><div>{$record["message"]}</div>
  </div>
</div>          
EOF;
        }
        
      return $html;
    } 
    
    //override view() function
    public function view($data = array()) {
      $html = "";
      
      //join all the messages
      if(!empty($data) && is_array($data))
        foreach($data as $record) {  
          $html .= $record;
        }
        
      require_once("index.tpl");
    }
  }
?>
