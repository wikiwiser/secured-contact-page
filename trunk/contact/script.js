/**
 * MessageBoard Main Javascript File
 *
 * Copyright Oleg Karp, 2010
 */

//process error messages
function processError(error, error_object) {
  //show error message
  $("#msg_error").append(error);
  $("#msg_error").fadeIn();
  //make more space in the container
  $(".container").height(330 + $("#msg_error").height());
  
  //select the appropriate error_object
  if(error_object)
    switch(error_object) {
      //ie7 compatibility
      case "to": {
        //change the border to red
        $(".select_container").css("border-color", "red");
        $("#" + error_object).css("border-color", "red");
        $("#" + error_object).change(function() {
          $(".select_container").css("border-color", "#595633");
          $(this).css("border-color", "#595633");
          $(this).change(function() { void(0); });
        });
        break;
      }
      default: {
        //change the border to red
        $("#" + error_object).css("border-color", "red");
        $("#" + error_object).change(function() {
          $(this).css("border-color", "#595633");
          $(this).change(function() { void(0); });
        });
      }
    }
}

//process success message
function processSuccess(data) {
  if(data.success && data.success == "1") {
    //show success message
    $("#msg_error").append("Thank you for the message!");
    $("#msg_error").fadeIn();
    //make more space in the container
    $(".container").height(330 + $("#msg_error").height());
  }
  else {
    processError("There's an error occured!");
  }
}

$(document).ready(function() {
  //focus on the from field
  $("#from").focus();
  
  //clear button
  $("#reset").click(function() {
    $(':input','#msg_form').not(':button, :submit, :reset').val('');
  });

  //attach the submit event to the form
  $("#msg_form").submit(function() {
    //remove error message (if any)
    $("#msg_error").hide();
    $("#msg_error").empty();
    $(".container").height(330);
  
    //submit via ajax request
    $.ajax({
      type: 'POST',
      url: "/contact/submit",
      data: $("#msg_form").serialize(),
      success: function(data) {
        if(!data)
          //probably no internet connection
          return alert("There's an error occured!\n\nPlease check your Internet connection.");
          
        if(data.error)
          processError(data.error, data.error_object ? data.error_object : "");
        else
          processSuccess(data);
      },
      error: function() {
        //general error occured (probably data object is wrong)
        alert("There's an error occured!");
      },
      dataType: "json",
      cache: false
    });
  });
});
