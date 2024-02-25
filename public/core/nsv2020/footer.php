      </div> <!-- row -->
    </div> <!-- #nsv-main -->

    <footer id="nsv-footer" class="d-print-none">
      <div class="container">
        <?php if (nsv2020_theme() == 'nsv'): ?>
          <div class="row">
            <ul class="col-12 col-sm-6">
              <li><a href="/kontakt/">Kontakt</a></li>
              <li><a href="/impressum">Impressum</a></li>
              <li><a href="/impressum">Datenschutz</a></li>
              <li><a href="http://nds-schachsenioren.de">Nieders&auml;chsische Schachsenioren</a></li>
              <li><a href="https://nsj-online.de">Nieders&auml;chsische Schachjugend (NSJ)</a></li>
              <li><a href="https://deutsche-schachjugend.de/">Deutsche Schachjugend (DSJ)</a></li>
              <li><a href="https://schachbund.de">Deutscher Schachbund (DSB)</a></li>
            </ul>
            <ul class="col-12 col-sm-6">
              <?php
                $bezirke = require(dirname(__FILE__) . '/bezirke.php');
                foreach ($bezirke as $bezirk) {
                  echo "<li><a href='$bezirk[website]'>".htmlentities($bezirk['name'], 0, 'UTF-8')."</a><li>";
                }
              ?>
              <li><a href="/bezirke/Websites.php">Vereine</a></li>
            </ul>
          </div>
        <?php elseif (nsv2020_theme() == 'bezirk1'): ?>
          <div class="row">
            <div class="col-12" style="text-align: center">
              Schachbezirk Hannover e.V.
              | <a href="https://schachbezirk-hannover.de/impressum/">Impressum</a>
              | <a href="https://schachbezirk-hannover.de/impressum/#datenschutz">Datenschutz</a>
            </div>
          </div>
        <?php elseif (nsv2020_theme() == 'ergebnisdienst'): ?>
          <div class="row">
            <div class="col-12" style="text-align: center">
              Schach-Ergebnisdienst des <a href='http://nsv-online.de'>Nieders&auml;chsischen Schachverbandes</a>
              | <a href="http://nsv-online.de/goto/Impressum">Impressum</a>
              | <a href="http://nsv-online.de/core/apps/kontakt/">Kontakt</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </footer>
  </body>
</html>
