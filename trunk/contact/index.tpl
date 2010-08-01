<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Submit Messages Script by Oleg Karp</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" media="all" type="text/css" href="/style.css" />
	<script type="text/javascript" src="/jquery.min.js"></script>
	<script type="text/javascript" src="/script.js"></script>
</head>

<body>
  <div>
    <div class="floater"></div>
    <div class="container">
      <div class="header">
        Message Form
      </div>
      <hr />
      <div class="msg">
        <div class="error" id="msg_error"></div>
        <form method="post" action="javascript:void(0);" id="msg_form">
          <div>
            <div class="msg_from"><label for="from">From:</label></div>
            <div><input type="text" name="from" value="" tabindex="1" id="from" /></div>
          </div>
          <div>
            <div class="msg_to"><label for="to">To:</label></div>
            <div>
              <div class="select_container">
              <select name="to" tabindex="2" id="to">
                <option value="" selected="selected">-- Select a department --</option>
                <option value="sales">Sales</option>
                <option value="support">Support</option>
              </select>
              </div>
            </div>
          </div>
          <div>
            <div class="msg_msg"><label for="msg">Message:</label></div>
            <div><textarea name="msg" tabindex="3" id="msg" rows="5" cols="10"></textarea></div>
          </div>
          <div>
            <div class="msg_submit">
              <hr />
              <input type="button" name="reset" value="Clear" tabindex="5" id="reset" />&nbsp;&nbsp;
              <input type="submit" name="submit" value="Send" tabindex="4" id="submit" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

