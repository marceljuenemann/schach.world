
function SED_SendEmail ( mail ) {
  window.location = "mailto:" + mail.replace ( /-_-_bei_-_-/, "@" );
}

function dummy () { }

function SED_CookieSetzen ( Bezeichner, Wert, Dauer ) {  
  jetzt=new Date();
  Auszeit=new Date(jetzt.getTime()+Dauer*86400000);
  document.cookie=Bezeichner+'='+Wert+';expires='+Auszeit.toGMTString()+';';
}		

function SED_CookieLesen () {
  var Wert = '';
  if (document.cookie) {
    var Wertstart = document.cookie.indexOf('=') + 1;
    var Wertende = document.cookie.indexOf(';');
    if (Wertende == -1)
      Wertende = document.cookie.length;
    Wert = document.cookie.substring(Wertstart, Wertende);
  }
  return Wert;
}
