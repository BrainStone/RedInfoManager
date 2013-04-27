// JavaScript Document

var current_row = -1;
var current_column = -1;
var current_object = null;
var index_to_string_index = new Array("", "Station", "", "", "Artikel", "", "", "", "Quelle", "Erbauer", "Datei");
var timeout_refference = null;
var tmp_data = null;

$(document).ready(onDocumentLoaded);

function onDocumentLoaded()
{
  if(!$("div#meldung").length)
  {
    $(document.body).append($("<div id=\"meldung\">"));
    $("div#meldung").fadeOut(0);
  }
  
  registerHandler();
  
  meldungsBox();
}

function registerHandler()
{
  if(rawdata.length > 0)
  {
    startSessionTimeOut();
  }
  
  $(document).on("click", "td", function()
  {
    if(tmp_data != null)
    {
      $("div#meldung").append("<p>Einen Moment bitte!</p>");
      $("div#meldung").fadeIn(0);
      meldungsBox();
      
      return;
    }
    
    if((current_row) != -1 || (current_column != -1) || (current_object != null))
      return;
    
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
    case 10:
      editFieldBase();
      break;
    case 2:
      editCategories();
      break;
    case 3:
    case 7:
      editCoordinates();
      break;
    case 5:
      editStationStatus();
      break;
    case 6:
      editInfoStatus();
      break;
    default:
      current_row = -1;
      current_column = -1;
      current_object = null;
      break;
    }
  });
   
  $(document).on("blur", "input.edit, select.edit", function()
  {
    current_row_old = current_row;
    current_column_old = current_column;
    current_object_old = current_object;
    
    $(document).one("click", finishEditBlur);
  });
  
  $(document).on("change", "select.edit#\\#1", selectChange);
    
  $(document).on("submit", "form.edit", function(event)
  {
    event.preventDefault();
    finishEdit();
  });
  
  $(document).on("submit", "form.search", function(event)
  {
    event.preventDefault();
    search();
  });
  $(document).on("keyup", "form.search", function()
  {
    search();
  });
      
  $.ajaxSetup(
  {
    type: "POST",
    dataType: "text"
  });
  
  $(document).ajaxSuccess(ajaxSuccess);
  $(document).ajaxError(ajaxError);
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

function restartSessionTimeOut()
{
  stopSessionTimeOut();
  startSessionTimeOut();
}

function meldungsBox()
{
  if(document.hasFocus())
  {
    $("div#meldung").fadeOut(2000, function()
    {
      $("div#meldung").text("");
    });
  }
  else
  {
    $(document).one("focus", meldungsBox);
  }
}

function editFieldBase()
{
  current_object.innerHTML = "<form class=\"edit\"><input class=\"edit\" value=\"" + rawdata[current_row][index_to_string_index[current_column]] + "\"></form>";
  $("input.edit").focus();
  $("input.edit").setCursorPosition(rawdata[current_row][index_to_string_index[current_column]].length);
}

function editCategories()
{
  var options = "";
  var kategorie = rawdata[current_row]["Kategorie"];
  
  for(var i in categories)
  {
    options += "<option" + ((i == kategorie) ? " selected" : "") + ">" + i + "</option>";
  }
  
  current_object.innerHTML = "<form class=\"edit\"><select class=\"edit\" id =\"#1\">" + options + "</select><select class=\"edit\" id =\"#2\"></select></form>";
  $("select.edit#\\#1").focus();
  
  selectChange();
}

function editCoordinates()
{
  var options = "";
  
  if(current_column == 3)
  {
    options = "<select class=\"edit\" id =\"#0\">";
    var world = rawdata[current_row]["Position-Welt"];
    
    for(var i in worlds)
    {
      options += "<option" + ((worlds[i] == world) ? " selected" : "") + ">" + worlds[i] + "</option>";
    }
    
    options += "</select>";
  }
  
  var prefix = (current_column == 3) ? "Position-" : "Warp-";
  
  current_object.innerHTML = "<form class=\"edit\">" + options + "<input class=\"edit\" id=\"#1\" value=\"" + rawdata[current_row][prefix + "X"] + "\"><input class=\"edit\" id=\"#2\" value=\"" + rawdata[current_row][prefix + "Y"] + "\"><input class=\"edit\" id=\"#3\" value=\"" + rawdata[current_row][prefix + "Z"] + "\"></form>";
  if(current_column == 3)
    $("select.edit#\\#0").focus();
  else
    $("input.edit#\\#1").focus();
}

function editStationStatus()
{
  var options = "";
  var status = rawdata[current_row]["Stations-Status"];
  
  for(var i in stationstatus)
  {
    options += "<option" + ((stationstatus[i] == status) ? " selected" : "") + ">" + stationstatus[i] + "</option>";
  }
  
  current_object.innerHTML = "<form class=\"edit\"><select class=\"edit\">" + options + "</select></form>";
  $("select.edit").focus();
}

function editInfoStatus()
{
  var options = "";
  var status = rawdata[current_row]["Info-Status"];
  
  for(var i in infostatus)
  {
    options += "<option" + ((infostatus[i] == status) ? " selected" : "") + ">" + infostatus[i] + "</option>";
  }
  
  current_object.innerHTML = "<form class=\"edit\"><select class=\"edit\">" + options + "</select></form>";
  $("select.edit").focus();
}

function finishEditBlur()
{
  var td = "td#\\#" + current_row_old + "\\#" + current_column_old + " ";
  
  if(!($(td + "form.edit select.edit:focus").length || $(td + "form.edit input.edit:focus").length))
  {
    finishEdit();
  }
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
    case 10:
      rawdata[current_row][index_to_string_index[current_column]] = $("input.edit").val();
      break;
    case 2:
      rawdata[current_row]["Kategorie"] = $("select.edit#\\#1").val();
      rawdata[current_row]["Unterkategorie"] = $("select.edit#\\#2").val();
      break;
    case 3:
      rawdata[current_row]["Position-Welt"] = $("select.edit#\\#0").val();
    case 7:
      var prefix = (current_column == 3) ? "Position-" : "Warp-";
      
      rawdata[current_row][prefix + "X"] = $("input.edit#\\#1").val();
      rawdata[current_row][prefix + "Y"] = $("input.edit#\\#2").val();
      rawdata[current_row][prefix + "Z"] = $("input.edit#\\#3").val();
      break;
    case 5:
      rawdata[current_row]["Stations-Status"] = $("select.edit").val();
      break;
    case 6:
      rawdata[current_row]["Info-Status"] = $("select.edit").val();
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
    
    restartSessionTimeOut();
    
    current_row = -1;
    current_column = -1;
    current_object = null;
  }
}

function selectChange()
{
  var options = "";
  var length = categories.length;
  var kategorie = $("select.edit#\\#1").val();
  var unterkategorie = rawdata[current_row]["Unterkategorie"];
  var value;
  
  for(var i in categories[kategorie])
  {
    value = categories[kategorie][i];
    
    options += "<option" + ((value == unterkategorie) ? " selected" : "") + ">" + value + "</option>";
  }
  
  $("select.edit#\\#2").html(options);
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
  $(bstr + "10").text(data["Datei"]);
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
  rawdata[row] = new cloneObject(tmp_data);
  tmp_data = null;
  
  $("div#meldung").append("<p>Datenbankfehler!</p>")
  $("div#meldung").fadeIn(0);
  meldungsBox();
  
  updateRow(row);
}

function search()
{
  var rowcount = rawdata.length;
  var ID = $("input.search#search1").val();
  var Station = $("input.search#search2").val().toLowerCase();
  var Kategorie = $("input.search#search3").val().toLowerCase();
  var Unterkategorie = $("input.search#search4").val().toLowerCase();
  var Button = $("input.search#search5").val().toLowerCase();
  var obj_str;
  
  for(var i = 0; i < rowcount; i++)
  {
    obj_str = "tr#\\#" + i;
    
    if((rawdata[i]["Station-ID"].indexOf(ID) != -1) &&
       (rawdata[i]["Station"].toLowerCase().indexOf(Station) != -1) &&
       (rawdata[i]["Kategorie"].toLowerCase().indexOf(Kategorie) != -1) &&
       (rawdata[i]["Unterkategorie"].toLowerCase().indexOf(Unterkategorie) != -1) &&
       ($("td#\\#" + i + "\\#7").text().toLowerCase().indexOf(Button) != -1))
    {
      $(obj_str + ":hidden").show(300);
    }
    else
    {
      $(obj_str + ":visible").hide(300);
    }
    
  }
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