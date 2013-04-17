// JavaScript Document

var current_row = -1;
var current_column = -1;
var current_object = null;
$(document).ready(onDocumentLoaded);
var index_to_string_index = new Array("", "Station", "", "", "Artikel", "", "", "", "Quelle", "Erbauer", "", "");
var timeout_refference = null;
var tmp_data = null;

function onDocumentLoaded()
{
  registerHandler();
  
  meldungsBox();
  
  $.ajaxSetup(
  {
    type: "POST",
    dataType: "text"
  });
  
  $(document).ajaxSuccess(ajaxSuccess);
  $(document).ajaxError(ajaxError);
}

function registerHandler()
{
  if(rawdata.length > 0)
  {
    startSessionTimeOut();
  }
  
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
    case 4:
    case 8:
    case 9:
      editFieldBase();
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

function startSessionTimeOut()
{
  timeout_refference = setTimeout('window.location.href=window.location.href', sessiontimeout);
}

function stopSessionTimeOut()
{
  clearTimeout(timeout_refference);
  timeout_refference = null;
}

function meldungsBox()
{
  $("div#meldung").fadeOut(2000, function()
  {
    $("div#meldung").text("");
  });
}

function editFieldBase()
{
  current_object.innerHTML = "<form class=\"edit\"><span><input class=\"edit\" value=\"" + rawdata[current_row][index_to_string_index[current_column]] + "\"></span></form>";
  $("input.edit").focus();
  $("input.edit").setCursorPosition(rawdata[current_row][index_to_string_index[current_column]].length);
}

function finishEdit()
{
  if(current_object != null)
  {
    tmp_data = new cloneObject(rawdata[current_row]);
    
    switch(current_column)
    {
    case 1:
    case 4:
    case 8:
    case 9:
      rawdata[current_row][index_to_string_index[current_column]] = $("input.edit").val();
      break;
    }
    
    updateRow(current_row);
    
    var tmpData = new cloneObject(rawdata[current_row]);
    tmpData["ajax"] = "true";
    tmpData["row"] = current_row;
    tmpData["action"] = "updateDB";
    
    $.ajax({
      data: tmpData
    });
    
    current_row = -1;
    current_column = -1;
    current_object = null;
  }
}

function updateRow(row)
{
  var data = rawdata[row];
  var bstr = "td#\\#" + row + "\\#";
  
  $(bstr + "0").text(data["Station-ID"]);
  $(bstr + "1").text(data["Station"]);
  $(bstr + "2").text(data["Kategorie"] + " (" + data["Unterkategorie"] + ")");
  $(bstr + "3").text(data["Position-Welt"] + ": " + data["Position-X"] + ", " + data["Position-Y"] + ", " + data["Position-Z"]);
  $(bstr + "4").text(data["Artikel"]);
  $(bstr + "5").text(data["Stations-Status"]);
  $(bstr + "6").text(data["Info-Status"]);
  $(bstr + "7").text(data["Position-Welt"] + ": " + data["Warp-X"] + ", " + data["Warp-Y"] + ", " + data["Warp-Z"]);
  $(bstr + "8").text(data["Quelle"]);
  $(bstr + "9").text(data["Erbauer"]);
  $(bstr + "10").text(shortString(data["Info"], 50));
  $(bstr + "11").text(shortString(data["Team-Info"], 50));
}

function ajaxSuccess(event, request, settings)
{
  var tmp_array = unserialize(settings.data);
  
  if(request.responseText == "true")
  {
    tmp_data = null;
    
    delete tmp_array["ajax"];
    var tmp_row = tmp_array["row"];
    delete tmp_array["row"];
    
    rawdata[tmp_row] = new cloneObject(tmp_array);
  }
  else
  {
    console.log(request.responseText);
    
    databaseError(tmp_array["row"]);
  }
}

function ajaxError(event, request, settings)
{
  databaseError(unserialize(settings.data)["row"]);
}

function databaseError(row)
{
  console.log(tmp_data);
  
  rawdata[row] = new cloneObject(tmp_data);
  tmp_data = null;
  
  if(!$("div#meldung").length)
  {
    $(document.body).append($("<div id=\"meldung\">"));
  }
  
  $("div#meldung").append("<p>Datenbankfehler!</p>")
  $("div#meldung").fadeIn(0);
  meldungsBox();
  
  updateRow(row);
}

// Utility

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

function unserialize(string)
{
  var tmp_array = string.split("&"), tmp_array2, length = tmp_array.length;
  
  var return_array = new Array();
  
  for(var i = 0; i < length; i++)
  {
    tmp_array2 = tmp_array[i].split("=");
    return_array[tmp_array2[0]] = urldecode(tmp_array2[1]);
  }
  
  return return_array;
}

function urldecode(str)
{
  return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

function shortString(string, length)
{
  if(string == null)
  {
    return "This string was null!";
  }
  
  if(string.length <= length)
  {
    return string;
  }
  
  return wordwrap(string, length - 3, "\r\n", true).split("\r\n")[0] + "...";
}

function wordwrap(str, width, brk, cut)
{
  brk = brk || '\n';
  width = width || 75;
  cut = cut || false;
  
  if(!str)
  {
    return str;
  }
  
  var regex = '.{1,' + width + '}(\\s|$)' + (cut ? '|.{' + width + '}|.+$' : '|\\S+?(\\s|$)');
  
  return str.match(RegExp(regex, 'g')).join(brk);
}

function cloneObject(source)
{
  for(i in source)
  {
    if(typeof source[i] == 'source')
    {
      this[i] = new cloneObject(source[i]);
    }
    else
    {
      this[i] = source[i];
    }
  }
}