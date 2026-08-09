<?php

/**
 * Test bootstrap.
 *
 * This package is developed against the SynergyCP framework, which is not a
 * composer dependency. The framework classes and helpers the package touches
 * are stubbed here so the units under test can run standalone. The App\Shell
 * stubs double as recording fakes for asserting on generated shell commands.
 */

namespace App\Shell {
  class Process {
  }

  class ShellCommand {
    public string $executed = '';
    public ?string $outputFile = null;
    public int $exitCode = 0;
    public string $errors = '';

    public function setOutputFile(string $file): static {
      $this->outputFile = $file;

      return $this;
    }

    public function exec(string $cmd): static {
      $this->executed = $cmd;

      return $this;
    }

    public function getErrors(): string {
      return $this->errors;
    }

    public function getExitCode(): int {
      return $this->exitCode;
    }
  }

  class Shell {
    public int $nextExitCode = 0;
    public string $nextErrors = '';

    /** @var ShellCommand[] */
    public array $commands = [];

    public function cmd(): ShellCommand {
      $cmd = new ShellCommand();
      $cmd->exitCode = $this->nextExitCode;
      $cmd->errors = $this->nextErrors;
      $this->commands[] = $cmd;

      return $cmd;
    }

    /** @return string[] every command that was executed, in order */
    public function executed(): array {
      return array_map(
        static fn (ShellCommand $cmd): string => $cmd->executed,
        $this->commands
      );
    }
  }

  /**
   * A ShellCommand that actually runs its command through the local shell,
   * mirroring the framework's proc_open() semantics. Used by round-trip
   * integration tests.
   */
  class ExecutingShellCommand extends ShellCommand {
    public function exec(string $cmd): static {
      $this->executed = $cmd;

      $errFile = tempnam(sys_get_temp_dir(), 'scp-test-err-');
      $suffix = ($this->outputFile !== null ? ' 1>' . $this->outputFile : '')
        . ' 2>' . escapeshellarg($errFile);

      exec($cmd . $suffix, $ignored, $code);

      $this->exitCode = $code;
      $this->errors = trim((string) file_get_contents($errFile));
      unlink($errFile);

      return $this;
    }
  }

  class ExecutingShell extends Shell {
    public function cmd(): ShellCommand {
      $cmd = new ExecutingShellCommand();
      $this->commands[] = $cmd;

      return $cmd;
    }
  }
}

namespace App\System\SSH\Key {
  class GlobalSSHKey {
    public string $privateKeyFile = '/scp/data/ssh key/id_rsa';

    public function getPrivateKeyFile(): string {
      return $this->privateKeyFile;
    }
  }
}

namespace App\Database\Models {
  class Model {
    public $attributes = [];

    public function __get($key) {
      return $this->getAttributeValue($key);
    }

    public function __set($key, $value) {
      $this->setAttribute($key, $value);
    }

    public function getAttributeValue($key) {
      return $this->attributes[$key] ?? null;
    }

    public function setAttribute($key, $value) {
      $this->attributes[$key] = $value;

      return $this;
    }

    public function getKey() {
      return $this->attributes['id'] ?? null;
    }
  }
}

namespace App\Auth\Permission {
  interface ICanHavePermissions {
  }

  trait THasPermissionChecks {
  }
}

namespace App\Auth\Permission\Rule {
  class AllowIfUserHasPermissions {
    public static function create(array $permissions): static {
      return new static();
    }
  }
}

namespace Illuminate\Support {
  class Collection {
    public array $items;

    public function __construct(array $items = []) {
      $this->items = $items;
    }

    public function push($item): static {
      $this->items[] = $item;

      return $this;
    }

    public function each(callable $callback): static {
      foreach ($this->items as $item) {
        $callback($item);
      }

      return $this;
    }

    public function count(): int {
      return count($this->items);
    }

    public function all(): array {
      return $this->items;
    }
  }
}

namespace Illuminate\Database\Eloquent {
  class Collection extends \Illuminate\Support\Collection {
  }

  class Builder {
  }
}

namespace Illuminate\Support\Facades {
  /**
   * Recording fake for Laravel's HTTP client. Queue responses with
   * Http::queue(); every request made through the fluent chain is recorded
   * in Http::$requests.
   */
  class Http {
    /** @var FakeHttpResponse[] */
    public static array $responses = [];

    /** @var array[] */
    public static array $requests = [];

    public static function reset(): void {
      self::$responses = [];
      self::$requests = [];
    }

    public static function queue(int $status, $data): void {
      self::$responses[] = new FakeHttpResponse($status, $data);
    }

    public static function __callStatic($method, $args) {
      $pending = new FakePendingRequest();

      return $pending->$method(...$args);
    }
  }

  class FakePendingRequest {
    public array $options = [];

    public function connectTimeout($seconds): static {
      $this->options['connectTimeout'] = $seconds;

      return $this;
    }

    public function timeout($seconds): static {
      $this->options['timeout'] = $seconds;

      return $this;
    }

    public function withBasicAuth($username, $password): static {
      $this->options['basicAuth'] = [$username, $password];

      return $this;
    }

    public function withHeaders(array $headers): static {
      $this->options['headers'] = array_merge(
        $this->options['headers'] ?? [],
        $headers
      );

      return $this;
    }

    public function get(string $url) {
      return $this->record('GET', $url, null);
    }

    public function post(string $url, array $data = []) {
      return $this->record('POST', $url, $data);
    }

    public function send(string $method, string $url, array $options = []) {
      return $this->record($method, $url, $options);
    }

    protected function record(string $method, string $url, $payload) {
      Http::$requests[] = [
        'method' => $method,
        'url' => $url,
        'payload' => $payload,
        'options' => $this->options,
      ];

      if (!Http::$responses) {
        throw new \RuntimeException("No fake HTTP response queued for $url");
      }

      return array_shift(Http::$responses);
    }
  }

  class FakeHttpResponse {
    public function __construct(private int $statusCode, private $data) {
    }

    public function failed(): bool {
      return $this->statusCode >= 400;
    }

    public function status(): int {
      return $this->statusCode;
    }

    public function body(): string {
      return is_string($this->data) ? $this->data : (string) json_encode($this->data);
    }

    public function json($key = null, $default = null) {
      $data = is_array($this->data) ? $this->data : [];

      if ($key === null) {
        return $data;
      }

      foreach (explode('.', (string) $key) as $segment) {
        if (!is_array($data) || !array_key_exists($segment, $data)) {
          return $default;
        }
        $data = $data[$segment];
      }

      return $data;
    }
  }
}

namespace {
  function config(string $key, $default = null) {
    return \Packages\Backup\Tests\Support\TestConfig::get($key, $default);
  }

  function collection(array $items = []): \Illuminate\Support\Collection {
    return new \Illuminate\Support\Collection($items);
  }

  function encrypt($value): string {
    return 'test-encrypted:' . base64_encode(serialize($value));
  }

  function decrypt(string $value) {
    if (strpos($value, 'test-encrypted:') !== 0) {
      throw new \RuntimeException('The payload is invalid.');
    }

    return unserialize(base64_decode(substr($value, strlen('test-encrypted:'))));
  }

  require __DIR__ . '/../vendor/autoload.php';
}
