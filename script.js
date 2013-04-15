// JavaScript Document

$(document).ready(onDocumentLoaded);

function onDocumentLoaded()
{
  registerHandler();
  
  meldungsBox();
}

function registerHandler()
{
  $("td").click(function()
  {
    alert($(this));
  });
}

function meldungsBox()
{
  $("div#meldung").fadeOut(2000, function()
  {
    $("div#meldung").remove();
  });
}