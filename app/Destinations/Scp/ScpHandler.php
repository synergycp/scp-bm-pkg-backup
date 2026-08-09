<?php

namespace Packages\Backup\App\Destinations\Scp;

use App\Shell;
use Packages\Backup\App\Archive;
use Illuminate\Support\Collection;
use App\System\SSH\Key\GlobalSSHKey;

/**
 * Secure Copy Handler.
 */
class ScpHandler implements Archive\Dest\Handler\Handler {
  /**
   * @var int
   */
  const DEFAULT_PORT = 22;

  /**
   * Seconds to wait for the SSH connection before giving up.
   *
   * @var int
   */
  const CONNECT_TIMEOUT = 30;

  /**
   * @var Shell\Shell
   */
  protected $shell;

  /**
   * @var Archive\Field\ValueService
   */
  protected $value;

  /**
   * @var GlobalSSHKey
   */
  protected $sshKey;

  /**
   * ScpHandler constructor.
   *
   * @param Shell\Shell                $shell
   * @param Archive\Field\ValueService $value
   * @param GlobalSSHKey               $sshKey
   */
  public function __construct(
    Shell\Shell $shell,
    Archive\Field\ValueService $value,
    GlobalSSHKey $sshKey
  ) {
    $this->shell = $shell;
    $this->value = $value;
    $this->sshKey = $sshKey;
  }

  /**
   * {@inheritdoc}
   */
  public function copy(Archive\Dest\Dest $dest, $tempFile, $destFile) {
    $runCommand = function ($command) {
      $this->run($command);
    };

    $this->commands($dest, $tempFile, $destFile)->each($runCommand);
  }

  /**
   * @inheritDoc
   */
  public function delete(Archive\Dest\Dest $dest, $destFile) {
    $this->run($this->deleteCommand($dest, $destFile));
  }

  /**
   * @param string $command
   *
   * @throws \Exception
   */
  protected function run($command) {
    $cmd = $this->shell->cmd()->exec($command);

    // Drain stderr before reading the exit code so a chatty command cannot
    // deadlock on a full pipe buffer.
    $errors = $cmd->getErrors();

    if ($status = $cmd->getExitCode()) {
      throw new \Exception(
        sprintf("Error %d with: %s: %s", $status, $command, $errors)
      );
    }
  }

  /**
   * @param Archive\Dest\Dest $dest
   * @param string           $tempFile
   * @param string           $destFile
   *
   * @return Collection
   */
  protected function commands(Archive\Dest\Dest $dest, $tempFile, $destFile) {
    $target = $this->target($dest, $destFile);
    $commands = collection([]);

    if ($target['folder'] !== '') {
      $commands->push(implode(' ', [
        'ssh',
        $this->sshOptions($target['port']),
        escapeshellarg($target['login']),
        '--',
        // Escaped twice: once for the local shell and once for the shell on
        // the remote end that ssh runs the command with.
        escapeshellarg(
          sprintf('mkdir -p %s', escapeshellarg($target['folder']))
        ),
      ]));
    }

    $commands->push(implode(' ', [
      'scp',
      $this->scpOptions($target['port']),
      escapeshellarg($tempFile),
      // The remote path is expanded by the remote shell, so it is escaped for
      // the remote side before the whole argument is escaped locally.
      escapeshellarg(
        sprintf('%s:%s', $target['login'], escapeshellarg($target['file']))
      ),
    ]));

    return $commands;
  }

  /**
   * @param Archive\Dest\Dest $dest
   * @param string           $destFile
   *
   * @return string
   */
  protected function deleteCommand(Archive\Dest\Dest $dest, $destFile) {
    $target = $this->target($dest, $destFile);

    return implode(' ', [
      'ssh',
      $this->sshOptions($target['port']),
      escapeshellarg($target['login']),
      '--',
      escapeshellarg(sprintf('rm -f %s', escapeshellarg($target['file']))),
    ]);
  }

  /**
   * Resolve the login, port and remote paths for a Destination.
   *
   * @param Archive\Dest\Dest $dest
   * @param string           $destFile
   *
   * @return array
   */
  protected function target(Archive\Dest\Dest $dest, $destFile) {
    $values = $this->value->all($dest);
    $folder = rtrim(trim((string) $values->value(ScpFields::FOLDER)), '/');

    $file = $destFile;
    // strrpos() returns false when the file has no folder component, which
    // (int) casts to 0, leaving an empty folder.
    $fileFolder = substr($destFile, 0, (int) strrpos($destFile, '/'));

    if ($folder !== '') {
      $file = $folder . '/' . $file;
      $fileFolder = $fileFolder === '' ? $folder : $folder . '/' . $fileFolder;
    }

    list($host, $port) = $this->parseHost($values->value(ScpFields::HOST));

    return [
      'login' => sprintf('%s@%s', $values->value(ScpFields::USER), $host),
      'port' => $port,
      'file' => $file,
      'folder' => $fileFolder,
    ];
  }

  /**
   * Parse "host", "host:port", "[ipv6]", "[ipv6]:port" and bare IPv6 hosts.
   *
   * @param string $host
   *
   * @return array tuple($host, $port)
   */
  protected function parseHost($host) {
    $host = trim((string) $host);
    $port = self::DEFAULT_PORT;

    if (preg_match('/^\[([^\]]+)\](?::(\d+))?$/', $host, $matches)) {
      return [
        $matches[1],
        empty($matches[2]) ? $port : (int) $matches[2],
      ];
    }

    // A single colon is a host:port separator. More than one colon means a
    // bare IPv6 address with no port.
    if (substr_count($host, ':') === 1) {
      list($host, $port) = explode(':', $host);
    }

    return [$host, (int) $port ?: self::DEFAULT_PORT];
  }

  /**
   * @param int $port
   *
   * @return string
   */
  protected function sshOptions($port) {
    return implode(' ', array_merge($this->commonOptions(), [
      sprintf('-p %d', $port),
    ]));
  }

  /**
   * @param int $port
   *
   * @return string
   */
  protected function scpOptions($port) {
    return implode(' ', array_merge($this->commonOptions(), [
      sprintf('-P %d', $port),
    ]));
  }

  /**
   * @return array
   */
  protected function commonOptions() {
    return [
      // Never fall back to an interactive password prompt: it would hang the
      // queue worker forever.
      '-o BatchMode=yes',
      sprintf('-o ConnectTimeout=%d', self::CONNECT_TIMEOUT),
      // Record the host key on first contact; refuse to connect if it changes
      // afterwards.
      '-o StrictHostKeyChecking=accept-new',
      sprintf('-i %s', escapeshellarg($this->sshKey->getPrivateKeyFile())),
    ];
  }
}
