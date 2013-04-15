// JavaScript Document

$(document).ready(onDocumentLoaded);

function onDocumentLoaded()
{
  $("div#meldung").fadeOut(2000, function()
  {
    $("div#meldung").remove();
  });
  
  $("td").click(function()
  {
    alert("lol");
  });
}