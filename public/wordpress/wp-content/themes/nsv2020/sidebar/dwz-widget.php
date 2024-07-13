<div class="nsv-widget">

  <!-- Spielersuche -->
  <form action="https://www.schachbund.de/spieler.html" target="_blank" method="get" class="mb-3">
    <div class="input-group">
      <input class="form-control form-control-sm" type="text" name="search" placeholder="Spieler suchen..." onkeyup="DwzSpielerAutocomplete(this.value);">
      <div class="input-group-append">
        <button class="btn btn-sm btn-primary" type="submit"><span class="dashicons dashicons-search"></span></button>
      </div>
    </div>
    <ul id="dwz-autocomplete-spieler" style="display: none; padding-left: 15px; margin: 0;"><li></li></ul>
  </form>

  <!-- Vereinssuche -->
  <form action="https://www.schachbund.de/verein.html" target="_blank" method="get" class="mb-3">
    <div class="input-group">
      <input class="form-control form-control-sm" type="text" name="search" placeholder="Verein suchen..." onkeyup="DwzVereinAutocomplete(this.value);">
      <div class="input-group-append">
        <button class="btn btn-sm btn-primary" type="submit"><span class="dashicons dashicons-search"></span></button>
      </div>
    </div>
    <ul id="dwz-autocomplete-vereine" style="display: none; padding-left: 15px; margin: 0;"><li></li></ul>
  </form>

  <!-- Links -->
  <a href="http://www.schachbund.de/verband/70000.html?toplist=100&sex=&age_from=0&age_to=140">Top 100</a> |
  <a href="http://www.schachbund.de/verband/70000.html?toplist=100&sex=f&age_from=0&age_to=140">Top Frauen</a><br />
  <a href="http://www.schachbund.de/turnier.html?search=1&keyword=&zps=700&last_months=6">Neue Auswertungen</a><br />
  <a href="http://isewase.de">DWZ Berechnung</a>

  <script type="text/javascript">
    function DwzSpielerAutocomplete(name) {
      if (name.length < 3) return;
      jQuery.ajax({
        url: "/api/dwz/spieler/",
        data: { name: name }, 
        dataType: 'json',
        success: function(result) {
          html = result.map(player => "<li><a href='" + player.link + "'>" + player.Spielername.replace(",", ", ") + "</a></li>").join('');
          jQuery('#dwz-autocomplete-spieler').css('display', 'block').html(html);
        }
      });
    }

    function DwzVereinAutocomplete(name) {
      if (name.length < 3) return;
      jQuery.ajax({
        url: "/dwz/api/clubs/",
        data: { name: name, zps: '7' },
        dataType: 'json',
        success: function(result) {
          result = result.slice(0, 6);
          html = result.map(club => "<li><a href='https://www.schachbund.de/verein.html?zps=" + club.zps + "'>" + club.name + "</a></li>").join('');
          jQuery('#dwz-autocomplete-vereine').css('display', 'block').html(html);
        }
      });
    }
  </script>
</div>