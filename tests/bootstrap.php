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
      return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value) {
      $this->attributes[$key] = $value;
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

namespace {
  function config(string $key, $default = null) {
    return \Packages\Backup\Tests\Support\TestConfig::get($key, $default);
  }

  function collection(array $items = []): \Illuminate\Support\Collection {
    return new \Illuminate\Support\Collection($items);
  }

  require __DIR__ . '/../vendor/autoload.php';
}
