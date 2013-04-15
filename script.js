// JavaScript Document

var current_row = -1;
var current_column = -1;
var current_object = null;
$(document).ready(onDocumentLoaded);

function onDocumentLoaded()
{
  registerHandler();
  
  meldungsBox();
}

function registerHandler()
{
  $(document).on("click", "td", function()
  {
    var id = $(this)[0].getAttribute("id").substr(1).split("#");
    
    var tmp_row = parseInt(id[0]);
    var tmp_column = parseInt(id[1]);
    
    if((tmp_row == current_row) && (tmp_column == current_column))
      return; 
    
    current_row = tmp_row;
    current_column = tmp_column;
    current_object = $(this)[0];
    
    switch(current_column)
    {
    case 0:
      return;
    case 1:
      editField_1();
      break;
    }
  });
   
  $(document).on("blur", "input.edit", finishEdit);
    
  $(document).on("submit", "form.edit", function(event)
  {
    event.preventDefault();
    finishEdit();
  });
  
  $(document).on("submit", "form.search", function(event)
  {
    event.preventDefault();
  });
}

function meldungsBox()
{
  $("div#meldung").fadeOut(2000, function()
  {
    $("div#meldung").remove();
  });
}

function editField_1()
{
  current_object.innerHTML = "<form class=\"edit\"><span><input class=\"edit\" value=\"" + rawdata[current_row]["Station"] + "\"></span></form>";
  $("input.edit").focus();
  $("input.edit").setCursorPosition(rawdata[current_row]["Station"].length);
}

function finishEdit()
{
  if(current_object != null)
  {
    var value = $("input.edit").val();
    
    rawdata[current_row]["Station"] = value;
    current_object.innerHTML = value;
    
    current_row = -1;
    current_column = -1;
    current_object = null;
  }
}

new function($)
{
  $.fn.setCursorPosition = function(pos)
  {
    if ($(this).get(0).setSelectionRange)
    {
      $(this).get(0).setSelectionRange(pos, pos);
    }
    else if ($(this).get(0).createTextRange)
    {
      var range = $(this).get(0).createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  }
}(jQuery);