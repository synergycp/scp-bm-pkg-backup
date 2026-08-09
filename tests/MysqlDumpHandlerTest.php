<?php

namespace Packages\Backup\Tests;

use App\Shell\Shell;
use Packages\Backup\App\Archive\Archive;
use Packages\Backup\App\Sources\Mysql\MysqlDumpHandler;
use Packages\Backup\Tests\Support\FakeSource;
use Packages\Backup\Tests\Support\FakeValueService;
use Packages\Backup\Tests\Support\TestConfig;
use PHPUnit\Framework\TestCase;

final class MysqlDumpHandlerTest extends TestCase {
  private const HOSTILE_DATABASE = 'scp; rm -rf /';
  private const PASSWORD = 'p\'as$w"o`rd';

  private Shell $shell;
  private MysqlDumpHandler $handler;
  private Archive $backup;
  private string $tempDir;
  private string $tempFile;

  protected function setUp(): void {
    TestConfig::$values = [
      'database.connections.mysql.username' => 'scp_user',
      'database.connections.mysql.password' => self::PASSWORD,
      'database.connections.mysql.host' => 'db-host',
    ];

    $this->shell = new Shell();
    $this->handler = new MysqlDumpHandler(
      $this->shell,
      new FakeValueService(['database' => self::HOSTILE_DATABASE])
    );

    $this->backup = new Archive();
    $this->backup->attributes['id'] = 42;
    $this->backup->attributes['source'] = new FakeSource();

    $this->tempDir = sys_get_temp_dir() . '/pkg-backup-test-' . getmypid();
    if (!is_dir($this->tempDir)) {
      mkdir($this->tempDir, 0700, true);
    }
    $this->tempFile = $this->tempDir . '/42';
  }

  protected function tearDown(): void {
    foreach (glob($this->tempDir . '/*') ?: [] as $file) {
      unlink($file);
    }
    if (is_dir($this->tempDir)) {
      rmdir($this->tempDir);
    }
  }

  public function testRunsMkdirDumpAndVerifyInOrder(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    [$mkdir, $dump, $verify] = $this->shell->executed();

    $this->assertSame(
      sprintf("mkdir -p '%s'", $this->tempDir),
      $mkdir,
      'output dir is created quoted, from the parent directory'
    );
    $this->assertStringStartsWith('bash -o pipefail -c ', $dump);
    $this->assertSame(
      sprintf("test -s '%s' && gzip -t < '%s'", $this->tempFile, $this->tempFile),
      $verify,
      'dump is verified to be a non-empty valid gzip file'
    );
  }

  public function testDumpFailsWhenAnyPipelineCommandFails(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    // pipefail is what surfaces a mysqldump failure that gzip would
    // otherwise mask with its own successful exit code.
    $this->assertStringContainsString(
      'bash -o pipefail -c ',
      $this->dumpCommand()
    );
    $this->assertStringContainsString('| gzip -f -6', $this->dumpCommand());
  }

  public function testDumpOutputIsRedirectedToTheTempFile(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    $this->assertSame($this->tempFile, $this->shell->commands[1]->outputFile);
  }

  public function testDatabaseNameIsShellEscaped(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    // Inside the escapeshellarg()d bash -c payload, the database's own
    // escapeshellarg() quotes appear as '\'' sequences.
    $this->assertStringContainsString(
      "'\\''" . self::HOSTILE_DATABASE . "'\\''",
      $this->dumpCommand()
    );
  }

  public function testCredentialsAreNotOnTheCommandLine(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    $this->assertStringNotContainsString(self::PASSWORD, $this->dumpCommand());
    $this->assertStringContainsString(
      '--defaults-extra-file=',
      $this->dumpCommand()
    );
  }

  public function testDumpIncludesRoutinesEventsAndTriggers(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    $this->assertStringContainsString(
      '--routines --events --triggers',
      $this->dumpCommand()
    );
    $this->assertStringContainsString(
      '--single-transaction --quick',
      $this->dumpCommand()
    );
  }

  public function testCredentialsFileIsRemovedAfterTheDump(): void {
    $this->handler->handle($this->backup, $this->tempFile);

    $this->assertFileDoesNotExist($this->tempFile . '.cnf');
  }

  public function testCredentialsFileIsRemovedWhenTheDumpFails(): void {
    $this->shell->nextExitCode = 2;

    try {
      $this->handler->handle($this->backup, $this->tempFile);
      $this->fail('expected the dump to throw');
    } catch (\Exception $exc) {
      // expected
    }

    $this->assertFileDoesNotExist($this->tempFile . '.cnf');
  }

  public function testCredentialsFileIsPrivateAndEscaped(): void {
    $cnf = $this->tempDir . '/creds.cnf';

    $write = new \ReflectionMethod(MysqlDumpHandler::class, 'writeCredentialsFile');
    $write->invoke($this->handler, $cnf);

    $this->assertSame(
      '0600',
      substr(sprintf('%o', fileperms($cnf)), -4),
      'credentials file is only readable by its owner'
    );

    $contents = file_get_contents($cnf);
    $this->assertStringStartsWith("[client]\n", $contents);
    $this->assertStringContainsString('user="scp_user"', $contents);
    $this->assertStringContainsString('password="p\'as$w\\"o`rd"', $contents);
    $this->assertStringContainsString('host="db-host"', $contents);
  }

  public function testNonZeroExitCodeThrowsWithStderrInMessage(): void {
    $this->shell->nextExitCode = 2;
    $this->shell->nextErrors = 'mysqldump: Got error: 1045';

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/^Error 2 with .*1045/s');

    $this->handler->handle($this->backup, $this->tempFile);
  }

  public function testStderrAloneDoesNotFailTheBackup(): void {
    // mysqldump writes non-fatal warnings to stderr; only the exit code
    // decides success.
    $this->shell->nextErrors = 'Warning: something harmless';

    $this->handler->handle($this->backup, $this->tempFile);

    $this->assertCount(3, $this->shell->executed());
  }

  private function dumpCommand(): string {
    return $this->shell->executed()[1];
  }
}
