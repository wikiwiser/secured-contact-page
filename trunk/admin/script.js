/*
 * jquery-periodic
 */
jQuery.periodic = function (callback, options) {
  callback = callback || (function() { return false; });

  options = jQuery.extend({ },
                {frequency : 10, allowParallelExecution : false}, options);

  var currentlyExecuting = false;
  var timer;

  var controller = {
    stop : function () {
      if(timer) {
        window.clearInterval(timer);
        timer = null;
      }
    },
    currentlyExecuting : false,
    currentlyExecutingAsync : false
  };

  timer = window.setInterval(function() {
    if(options.allowParallelExecution || !
        (controller.currentlyExecuting || controller.currentlyExecutingAsync)) {
      try {
        controller.currentlyExecuting = true;
        if(!(callback(controller))) {
          controller.stop();
        }
      }
      finally {
        controller.currentlyExecuting = false;
      }
    }
  }, options.frequency * 1000);
};

//add messages to the page
function addMessages(msgs) {
  if(msgs && msgs.length > 0) {
    for(i=msgs.length-1; i>=0; i--) {
      $(".container").prepend(msgs[i].replace(/^(<div)/i, "<div style='display:none'"));
      $($(".container .record")[0]).fadeIn();
    }
  }
}

//bring the new messages from the server
function getMessages(control) {
  if(control) {
    //stop periodic execution
    control.currentlyExecutingAsync = true;
    control.currentlyExecuting = true;
  }

  $.ajax({
    type: 'POST',
    url: "/admin",
    data: {
      starttime: $($(".container .record #starttime")[0]).html(),
      limit: 0
    },
    success: function(data) {
      if(!data)
        //probably no internet connection
        return alert("There's an error occured!\n\nPlease check your Internet connection.");
      
      //add messages to the page if any  
      if(data.length > 0)
        addMessages(data);

      //continue periodic execution
      control.currentlyExecutingAsync = false;
      control.currentlyExecuting = false;   
    },
    error: function() {
      //general error occured (probably data object is wrong)
      alert("There's an error occured!");
      
      //stop periodic execution
      control.stop();
    },
    dataType: "json",
    cache: false
  });
  
  return true;
}
  
$(document).ready(function() {
  //run periodic execution of a function
  $.periodic(getMessages, {frequency: 10});
});

