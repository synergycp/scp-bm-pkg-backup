<?php

namespace Packages\Backup\Tests;

use App\Shell\ExecutingShell;
use Packages\Backup\App\Archive\Archive;
use Packages\Backup\App\Sources\Mysql\MysqlDumpHandler;
use Packages\Backup\Tests\Support\FakeSource;
use Packages\Backup\Tests\Support\FakeValueService;
use Packages\Backup\Tests\Support\TestConfig;
use PHPUnit\Framework\TestCase;

/**
 * Runs the handler's real shell pipeline (with a stubbed mysqldump binary)
 * and decrypts the result with the exact parameters the restore script
 * uses, proving producer and consumer stay compatible.
 */
final class MysqlDumpRoundTripTest extends TestCase {
  private const APP_KEY = 'base64:round-trip-key';
  private const SQL = 'CREATE TABLE roundtrip (id INT);';

  private string $dir;
  private string $originalPath;

  protected function setUp(): void {
    TestConfig::$values = [
      'database.connections.mysql.username' => 'scp_user',
      'database.connections.mysql.password' => 'secret',
      'database.connections.mysql.host' => 'db-host',
      'app.key' => self::APP_KEY,
    ];

    $this->dir = sys_get_temp_dir() . '/pkg-backup-roundtrip-' . getmypid();
    mkdir($this->dir . '/bin', 0700, true);

    // Stub mysqldump that ignores its arguments and emits known SQL.
    file_put_contents(
      $this->dir . '/bin/mysqldump',
      "#!/bin/sh\necho '" . self::SQL . "'\n"
    );
    chmod($this->dir . '/bin/mysqldump', 0755);

    $this->originalPath = (string) getenv('PATH');
    putenv('PATH=' . $this->dir . '/bin:' . $this->originalPath);
  }

  protected function tearDown(): void {
    putenv('PATH=' . $this->originalPath);
    foreach (glob($this->dir . '/{bin/,}*', GLOB_BRACE) ?: [] as $file) {
      is_file($file) && unlink($file);
    }
    @rmdir($this->dir . '/bin');
    @rmdir($this->dir);
  }

  public function testBackupDecryptsWithTheRestoreScriptParameters(): void {
    $backup = new Archive();
    $backup->attributes['id'] = 42;
    $backup->attributes['source'] = new FakeSource();

    $tempFile = $this->dir . '/42';

    $handler = new MysqlDumpHandler(
      new ExecutingShell(),
      new FakeValueService(['database' => 'testdb'])
    );
    // Runs mkdir, the real dump pipeline, and the decrypt-verify step.
    $handler->handle($backup, $tempFile);

    $this->assertFileExists($tempFile);
    $this->assertSame(
      'Salted__',
      (string) file_get_contents($tempFile, false, null, 0, 8),
      'the backup on disk is an openssl-encrypted stream'
    );

    // Decrypt exactly the way dev/restore.sh does.
    $decrypted = shell_exec(sprintf(
      'openssl enc -d -aes-256-cbc -pbkdf2 -iter 100000 -md sha256 -pass pass:%s < %s | gunzip',
      escapeshellarg(self::APP_KEY),
      escapeshellarg($tempFile)
    ));

    $this->assertStringContainsString(self::SQL, (string) $decrypted);
  }

  public function testWrongKeyCannotDecryptTheBackup(): void {
    $backup = new Archive();
    $backup->attributes['id'] = 43;
    $backup->attributes['source'] = new FakeSource();

    $tempFile = $this->dir . '/43';

    $handler = new MysqlDumpHandler(
      new ExecutingShell(),
      new FakeValueService(['database' => 'testdb'])
    );
    $handler->handle($backup, $tempFile);

    $decrypted = shell_exec(sprintf(
      'openssl enc -d -aes-256-cbc -pbkdf2 -iter 100000 -md sha256 -pass pass:wrong-key < %s 2>/dev/null | gunzip 2>/dev/null',
      escapeshellarg($tempFile)
    ));

    $this->assertStringNotContainsString(self::SQL, (string) $decrypted);
  }
}
