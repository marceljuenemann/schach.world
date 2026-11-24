<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

resetTestDatabase();

/**
 * Rebuilds the test database from dev/dbinit/ so every run starts from the same
 * data, locally and in CI. Set SKIP_DB_RESET=1 to reuse the current state.
 */
function resetTestDatabase(): void
{
    if (getenv('SKIP_DB_RESET') || ($_SERVER['SKIP_DB_RESET'] ?? false)) {
        return;
    }

    $url = parse_url($_SERVER['MAIN_DATABASE_URL']);
    $database = ltrim($url['path'] ?? '', '/');
    $command = implode(' ', array_map('escapeshellarg', [
        dirname(__DIR__).'/dev/scripts/reset-test-db.sh',
        $url['host'],
        (string) ($url['port'] ?? 3306),
        urldecode($url['user'] ?? ''),
        urldecode($url['pass'] ?? ''),
        $database,
    ]));

    $start = microtime(true);
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "Resetting the test database '$database' failed (exit code $exitCode).\n");
        exit(1);
    }
    fprintf(STDERR, "Reset test database '%s' in %.1fs.\n", $database, microtime(true) - $start);

    // Tests with @runInSeparateProcess run this bootstrap again in a child
    // process. PHPUnit builds its environment from either the process
    // environment or $_SERVER, so set both to skip the reset there.
    putenv('SKIP_DB_RESET=1');
    $_SERVER['SKIP_DB_RESET'] = '1';
}
