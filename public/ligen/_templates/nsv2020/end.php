</div> <!-- card body -->
</div> <!-- card -->
</div> <!-- column -->
<div class="col-12 col-lg-3" id="nsv-sidebar">

  <!-- New navigation card -->
  <div class='card shadow nsv-card nsv-sidebar-card'>
    <div class='card-body overflow-visible'>

      <!-- Headline with season selection -->
      <h5 class='card-title' style="cursor: pointer">
        <span class="dropdown">
          <span class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            Saison <?= $prefs['startjahr'].'/'.substr($prefs['startjahr']+1, 2)?>
          </span>
          <ul class="dropdown-menu">
            <?php foreach (SED_GetSaisonLinks() as $path => $name): ?>
              <li><a class="dropdown-item" href="/ligen/<?=$path?>/"><?=$name?></a></li>
            <?php endforeach; ?>
          </ul>
        </span>
      </h5>

      <!-- Divisions -->
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link <?=isset($globals['isHomescreen']) && $globals['isHomescreen'] ? 'active' : ''?>" href="<?=$globals['basepath']?>">&Uuml;bersicht</a>
        </li>
        <?php foreach ($globals['staffeln'] as $id => $name): ?>
          <li class="nav-item">
            <a class="nav-link <?=isset($_GET['staffel']) && $id == $_GET['staffel'] ? 'active' : ''?>"
              href="<?=$globals['basepath']?>/?staffel=<?=$id?>&r="><?=$name?></a>
          </li>
        <?php endforeach; ?>
      </ul>

    </div>
  </div>



<?php

// AUFSTELLUNGEN
echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
echo "<h5 class='card-title'>Aufstellungen</h5>";
?>
  <form method="get" action="<?= $globals['basepath'] ?>/">
    <select name="mannschaft" onchange="this.form.submit()" class="form-select">
      <option value="" selected="selected">--- Mannschaft ---</option>
      <?php
        foreach ( $globals ['teams'] as $id=>$name )
          echo "<option value='$id'>" . SED_escape($name) . "</option>";
      ?>
    </select>
  </form>
<?php
echo "</div></div>";

// LINKS
if ( $menu = SED_GetMenue () ) {
  echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
  echo "<h5 class='card-title'>Links</h5>";
  echo '<ul class="nav flex-column">';
  foreach ( $menu as $entry ) {
    echo "<li class='nav-item'><a class='nav-link' href='$entry[url]' ". ( "$entry[neuesfenster]" ? "target='_blank'" : "" ) . ">$entry[titel]</a></li>";
  }
  echo "</ul></div></div>";
}
  
  
// LOGIN
echo "<div class='card shadow nsv-card nsv-sidebar-card'><div class='card-body'>";
echo "<h5 class='card-title'>Turnierleitung</h5>";

global $admin, $globals;
if (isset($admin)):
  echo "<p>Angemeldet als ";
  if ($admin['usertype'] == 't') {
    echo "Turnierleiter:in";
  } else {
    echo "Staffelleiter:in ";
    echo $globals['staffeln'][$admin['staffel']];
  }
  echo ".</p>";
  ?>
    <div>
      <a href="<?=$globals['basepath']?>/?admin=desktop--">
        <button type="button" class="btn btn-success btn-sm">Zum Desktop</button>
      </a>
      <a href="<?=$globals['basepath']?>/?admin=logout--">
        <button type="button" class="btn btn-success btn-sm">Abmelden</button>
      </a>
    </div> 
 <?php
else:
  ?>
  <form action="<?=$globals['basepath']?>/?admin=login" method="post"><div>
    <div class="form-group"><select class="form-select" name="benutzer">
      <?php
        $benutzer = isset ( $_GET ['staffel'] ) ? $_GET ['staffel'] : ""; // nur über tinyurl möglich
        echo "<option value='t-$globals[tid]'>Turnierleiter:in</option>";
        foreach ( $globals ['staffeln'] as $id=>$name ){
            $selected = $benutzer == $id ? "selected='selected'" : "";
            echo "<option value='s-$id' $selected>$name</option>";
        }
      ?>
    </select></div>
    <div class="form-group">
      <input type="password" name="passwort" placeholder="Passwort" class="form-control">
    </div>
    <input type="submit" value="Einloggen" class="btn btn-sm btn-primary" />
  </div></form>
  <?php  
endif;
echo "</div></div>";   // END OF LOGIN

  
echo '</div>';
include ("$globals[basedir]/../core/nsv2020/footer.php");
