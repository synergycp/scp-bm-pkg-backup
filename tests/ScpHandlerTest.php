<?php

namespace Packages\Backup\Tests;

use App\Shell\Shell;
use App\System\SSH\Key\GlobalSSHKey;
use Packages\Backup\App\Archive\Dest\Dest;
use Packages\Backup\App\Destinations\Scp\ScpHandler;
use Packages\Backup\Tests\Support\FakeValueService;
use PHPUnit\Framework\TestCase;

final class ScpHandlerTest extends TestCase {
  private Shell $shell;

  protected function setUp(): void {
    $this->shell = new Shell();
  }

  private function handler(array $values): ScpHandler {
    return new ScpHandler(
      $this->shell,
      new FakeValueService($values),
      new GlobalSSHKey()
    );
  }

  private function copy(array $values, string $destFile = 'source-name.42.sql.gz'): array {
    $this->handler($values)->copy(new Dest(), '/scp/data/tmp/backup/42', $destFile);

    return $this->shell->executed();
  }

  public function testCopyCreatesTheRemoteFolderThenCopies(): void {
    $commands = $this->copy([
      'host' => 'backups.example.com:2222',
      'user' => 'scp',
      'folder' => 'my backups/$(reboot)',
    ]);

    $this->assertCount(2, $commands);

    [$mkdir, $scp] = $commands;

    $this->assertStringStartsWith('ssh ', $mkdir);
    $this->assertStringContainsString('-p 2222', $mkdir);
    $this->assertStringContainsString(
      // Escaped once for the local shell and once for the remote shell, so
      // the hostile folder name cannot execute anything on either side.
      "'mkdir -p '\\''my backups/\$(reboot)'\\'''",
      $mkdir
    );
    $this->assertStringContainsString("'scp@backups.example.com'", $mkdir);

    $this->assertStringStartsWith('scp ', $scp);
    $this->assertStringContainsString('-P 2222', $scp);
    $this->assertStringContainsString("'/scp/data/tmp/backup/42'", $scp);
    $this->assertStringContainsString(
      "'scp@backups.example.com:'\\''my backups/\$(reboot)/source-name.42.sql.gz'\\'''",
      $scp
    );
  }

  public function testSshAndScpAreHardenedAgainstHangsAndMitm(): void {
    $commands = $this->copy([
      'host' => 'backups.example.com',
      'user' => 'scp',
      'folder' => 'backups',
    ]);

    foreach ($commands as $command) {
      $this->assertStringContainsString('-o BatchMode=yes', $command);
      $this->assertStringContainsString('-o ConnectTimeout=30', $command);
      $this->assertStringContainsString(
        '-o StrictHostKeyChecking=accept-new',
        $command
      );
      $this->assertStringContainsString(
        "-i '/scp/data/ssh key/id_rsa'",
        $command,
        'key file path is escaped'
      );
    }
  }

  public function testNoFolderSkipsTheMkdirAndUsesDefaultPort(): void {
    $commands = $this->copy([
      'host' => 'backups.example.com',
      'user' => 'scp',
      'folder' => '',
    ]);

    $this->assertCount(1, $commands, 'no mkdir -p "" command is run');
    $this->assertStringStartsWith('scp ', $commands[0]);
    $this->assertStringContainsString('-P 22', $commands[0]);
    $this->assertStringContainsString(
      "'scp@backups.example.com:'\\''source-name.42.sql.gz'\\'''",
      $commands[0]
    );
  }

  public function testBracketedIpv6HostWithPort(): void {
    $commands = $this->copy([
      'host' => '[2001:db8::1]:2200',
      'user' => 'scp',
      'folder' => 'backups',
    ]);

    $this->assertStringContainsString('-p 2200', $commands[0]);
    $this->assertStringContainsString("'scp@2001:db8::1'", $commands[0]);
  }

  public function testBareIpv6HostKeepsTheDefaultPort(): void {
    $commands = $this->copy([
      'host' => '2001:db8::1',
      'user' => 'scp',
      'folder' => 'backups',
    ]);

    $this->assertStringContainsString('-p 22 ', $commands[0]);
    $this->assertStringContainsString("'scp@2001:db8::1'", $commands[0]);
  }

  public function testHostPortSplitStillWorks(): void {
    $commands = $this->copy([
      'host' => '192.0.2.10:2022',
      'user' => 'scp',
      'folder' => 'backups',
    ]);

    $this->assertStringContainsString('-p 2022', $commands[0]);
    $this->assertStringContainsString("'scp@192.0.2.10'", $commands[0]);
  }

  public function testDeleteRemovesTheRemoteFile(): void {
    $this->handler([
      'host' => 'h.example.com',
      'user' => 'scp',
      'folder' => 'backups',
    ])->delete(new Dest(), 'a.42.sql.gz');

    $command = $this->shell->executed()[0];

    $this->assertStringStartsWith('ssh ', $command);
    $this->assertStringContainsString(
      "'rm -f '\\''backups/a.42.sql.gz'\\'''",
      $command
    );
  }

  public function testNonZeroExitCodeThrowsWithStderrInMessage(): void {
    $this->shell->nextExitCode = 255;
    $this->shell->nextErrors = 'Permission denied (publickey).';

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/^Error 255 with: .*publickey/s');

    $this->handler([
      'host' => 'h.example.com',
      'user' => 'scp',
      'folder' => '',
    ])->copy(new Dest(), '/tmp/f', 'a.42.sql.gz');
  }
}
