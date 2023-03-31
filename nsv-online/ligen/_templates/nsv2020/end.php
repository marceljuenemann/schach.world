</div> <!-- card body -->
</div> <!-- card -->
</div> <!-- column -->
<div class="col-12 col-lg-3" id="nsv-sidebar">
<?php

// AUFSTELLUNGEN
echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
echo "<h5 class='card-title'>Aufstellungen</h5>";
?>
  <form class="form" method="get">
    <select name="mannschaft" onchange="this.form.submit()" class="form-control form-control-sm">
      <option value="" selected="selected">--- Mannschaft ---</option>
      <?
        foreach ( $globals ['teams'] as $id=>$name )
          echo "<option value='$id'>$name</option>";
      ?>
    </select>
  </form>
<?
echo "</div></div>";

  
// NAVIGATION
echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
echo "<h5 class='card-title'>Staffeln</h5>";
$style = ( isset ( $globals ['mod'] ) && $globals ['mod'] == "startseite_html" ) ? "font-weight: bold;" : "";
echo "<a href='?' style='$style'>&Uuml;bersicht</a><br>";
foreach ( $globals ['staffeln'] as $id=>$name ) {
  $style = ( isset ( $_GET ['staffel'] ) && $_GET ['staffel'] == $id ) ? "font-weight: bold;" : "";
  echo "<a href='?staffel=$id&r=' style='$style'>$name</a><br>";
}
echo "</div></div>";

  
// LINKS
if ( $menu = SED_GetMenue () ) {
  echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
  echo "<h5 class='card-title'>Links</h5>";
  while ( $entry = mysql_fetch_array ( $menu, MYSQL_ASSOC ) ) {
    echo "<a href='$entry[url]' ". ( "$entry[neuesfenster]" ? "target='_blank'" : "" ) . ">$entry[titel]</a><br>";
  }
  echo "</div></div>";
}
  
  
// SPIELTAG-AUSWAHL
if ( count ( $globals ['staffeln'] ) )
{
  echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
  echo "<h5 class='card-title'>Spieltag-Auswahl</h5>";
  echo "<form class='form' method='get'><div>";

  // Staffelauswahl
  if ( count ( $globals ['staffeln'] ) == 1 )
  {
    // Nur eine Staffel
    foreach ( $globals ['staffeln'] as $id=>$name )
    echo "<input type='hidden' name='staffel' value='$id' />";
  }
  else
  {
    // Staffeln sammeln
    $options = "";
    foreach ( $globals ['staffeln'] as $id=>$name )
    $options .= "<option value='$id'>$name</option>";

    // Falls eine Staffel ausgewählt ist
    if ( isset ( $_GET ['staffel'] ) )
    $options = SED_SelectOption ( $options, $_GET ['staffel'] );

    // Liste ausgeben
    echo "<div class='form-group'><select name='staffel' onchange='this.form.submit()' class='form-control form-control-sm'>";
    echo "$options</select></div>";
  }

  // Spieltag-Auswahl
  ?>
    <div class="form-group">
      <select name="r" onchange="this.form.submit()" class='form-control form-control-sm'>
      <option value="statistik">Statistik</option>
      <option value="spielplan">Spielplan</option>
      <option value="" selected="selected">Aktueller Spieltag</option>
      <?
        for ( $i = 1; $i <= $prefs ['runden']; ++$i ){
            $selected = (isset ( $_GET['r'] )&&$_GET['r']==$i) ? "selected='selected'" : "";
            echo "<option value='$i' $selected>$i. Spieltag</option>";
        }
      ?>
      </select>
    </div>

    <input type="submit" value="Anzeigen" class="btn btn-sm btn-primary">
    <input type="submit" name="ausgabe" value="PDF" class="btn btn-sm btn-primary">
  <?php
  echo '</div></form></div></div>';
}

  
// SAISON-AUSWAHL
$links = SED_GetSaisonLinks();
if (count($links)) {
  echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
  echo "<h5 class='card-title'>Saisonauswahl</h5>";
  ?>
    <form class='form'><div>
        <select name="saison" onchange="window.location.href = '../' + this.value" class="form-control form-control-sm">
        <?
          foreach ( $links as $value => $label ) {
            $selected = $value == $prefs['directory'] ? 'selected="selected"' : '';
            echo "<option value='$value' $selected>$label</option>";
          }
        ?>
        </select>
    </div></form>
  <?
  echo "</div></div>";
}


// NEWSLETTER
if ( !$prefs ['sysKeinNewsletter'] ) {
  echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
  echo "<h5 class='card-title'>Newsletter</h5>";
  ?>
    <form action="?m=newsletter" method="post"><div>
      <div class="form-group"><select class="form-control form-control-sm" name="staffel">
        <?
          echo "<option value='1'>--- Staffel ---</option>";
          foreach ( $globals ['staffeln'] as $id=>$name )
            echo "<option value='$id'>$name</option>";
        ?>
      </select></div>
      <div class="form-group">
        <input type="text" name="email" placeholder="Email" onfocus="this.value = '';" class="form-control form-control-sm" />
      </div>
      <input type="submit" name="newsletter_register" value="Registrieren" class="btn btn-sm btn-primary" />
    </div></form>
  <?php
  echo "</div></div>";
}
  

  
// TURNIERLEITER
if (isset($prefs['organisation']) && $prefs['organisation'] == "frl"): ?>
  <div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>
    <h5 class='card-title'>Turnierleitung</h5>
    <b>Hannelore Neumeyer</b><br />
    Lerchengrund 9<br />
    04158 Leipzig<br /> 
    <?= SED_Email("Neumeyer-Leipzig@gmx.de") ?><br />
    Tel.: 0341 / 33209902<br />
    <br />Diese Seite wird im Auftrag der Frauenkommission des Deutschen Schachbundes gef&uuml;hrt.
  </div></div>
<?php
endif;

    
  
// LOGIN
echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
echo "<h5 class='card-title'>Staffelleiter</h5>";
?>
  <form action="?admin=login" method="post"><div>
    <div class="form-group"><select class="form-control form-control-sm" name="benutzer">
      <?
        $benutzer = isset ( $_GET ['staffel'] ) ? $_GET ['staffel'] : ""; // nur über tinyurl möglich
        echo "<option value='t-$globals[tid]'>--- Benutzer ---</option>";
        foreach ( $globals ['staffeln'] as $id=>$name ){
            $selected = $benutzer == $id ? "selected='selected'" : "";
            echo "<option value='s-$id' $selected>$name</option>";
        }
        echo "<option value='t-$globals[tid]'>Turnierleiter</option>";
      ?>
    </select></div>
    <div class="form-group">
      <input type="password" name="passwort" placeholder="Passwort" class="form-control form-control-sm">
    </div>
    <input type="submit" value="Einloggen" class="btn btn-sm btn-primary" />
  </div></form>
<?php  
echo "</div></div>";

  
echo '</div>';
include ("$globals[basedir]/../core/nsv2020/footer.php");
