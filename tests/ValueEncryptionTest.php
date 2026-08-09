<?php

namespace Packages\Backup\Tests;

use Packages\Backup\App\Archive\Field\Field;
use Packages\Backup\App\Archive\Field\Value;
use PHPUnit\Framework\TestCase;

final class ValueEncryptionTest extends TestCase {
  private function value(bool $secret): Value {
    $field = new Field();
    $field->attributes['secret'] = $secret;

    $value = new Value();
    $value->attributes['field'] = $field;

    return $value;
  }

  public function testSecretFieldValuesAreEncryptedAtRest(): void {
    $value = $this->value(true);
    $value->value = 'K000secretsecret';

    $this->assertStringStartsWith(
      'test-encrypted:',
      $value->attributes['value'],
      'the stored (database) value is ciphertext'
    );
    $this->assertSame('K000secretsecret', $value->value);
    $this->assertSame('K000secretsecret', $value->value());
  }

  public function testNonSecretFieldValuesAreStoredInPlainText(): void {
    $value = $this->value(false);
    $value->value = 'backups.example.org';

    $this->assertSame('backups.example.org', $value->attributes['value']);
    $this->assertSame('backups.example.org', $value->value);
    $this->assertSame('backups.example.org', $value->value());
  }

  public function testEmptySecretValuesPassThrough(): void {
    $value = $this->value(true);
    $value->attributes['value'] = '';

    $this->assertSame('', $value->value());
  }
}
