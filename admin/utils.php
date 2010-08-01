<?php
  /**
   * MessageBoard Utilities Functions
   *
   * utils.php
   *
   * @copyright Oleg Karp, 2010
   */

  //encrypt a string
  function encrypt($string, $key = "") {
    //open the cipher
    $mod = mcrypt_module_open('rijndael-256', '', 'ofb', '');
    
    if(!$mod)
        throw new Exception("There was a problem initializing encryption module!");
    
    //create the IV and determine the keysize length
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($mod), MCRYPT_DEV_RANDOM);
    $ks = mcrypt_enc_get_key_size($mod);
    
    //create key
    $key = substr(md5($key), 0, $ks);
    
    //initialize encryption
    $enc = mcrypt_generic_init($mod, $key, $iv);
    
    if(($enc < 0) || ($enc === false))
      throw new Exception("There's an error occured during encryption!");
      
    //encrypt data
    $encrypted = mcrypt_generic($mod, $string);
    
    //terminate encryption handler and close module
    mcrypt_generic_deinit($mod);
    mcrypt_module_close($mod);
    
    return array($encrypted, $iv);
  }
  
  //decrypt a string 
  function decrypt($string, $key = "", $iv = "") {
    //open the cipher
    $mod = mcrypt_module_open('rijndael-256', '', 'ofb', '');
      
    if(!$mod)
      throw new Exception("There was a problem initializing decryption module!"); 
    //determine the keysize length
    $ks = mcrypt_enc_get_key_size($mod);
    
    //create key
    $key = substr(md5($key), 0, $ks);
    
    //initialize encryption module for decryption
    $enc = mcrypt_generic_init($mod, $key, $iv);
      
    if(($enc < 0) || ($enc === false))
      throw new Exception("There's an error occured during decryption!");
      
    //decrypt encrypted string
    $decrypted = mdecrypt_generic($mod, $string);
    
    //terminate decryption handle and close module
    mcrypt_generic_deinit($mod);
    mcrypt_module_close($mod);

    return trim($decrypted);  
  }

  // Returns true if the given number is decimal
  function isDecimalNumber( $n ) {
    return (string)(float)$n === (string)$n;
  }
?>
