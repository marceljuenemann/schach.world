<?php
/**
 * Plugin Name:       schach.world
 * Plugin URI:        https://schach.world
 * Description:       Turnierverwaltung für Schachvereine: Anmeldung mit DWZ-Abgleich, Wartelisten und Exporte.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            Marcel Juenemann
 * License:           AGPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/agpl-3.0.html
 * Text Domain:       schach-world
 */

namespace SchachWorld;

defined('ABSPATH') || exit;

const VERSION = '0.1.0';
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/src/autoload.php';

Plugin::boot();
