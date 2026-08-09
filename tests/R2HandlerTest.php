<?php

namespace Packages\Backup\Tests;

use Illuminate\Support\Facades\Http;
use Packages\Backup\App\Archive\Dest\Dest;
use Packages\Backup\App\Destinations\R2\R2Handler;
use Packages\Backup\Tests\Support\FakeValueService;
use PHPUnit\Framework\TestCase;

/**
 * R2Handler with a fixed clock so signatures are deterministic.
 */
final class FixedClockR2Handler extends R2Handler {
  const AMZ_DATE = '20260809T120000Z';

  protected function timestamp() {
    return self::AMZ_DATE;
  }
}

final class R2HandlerTest extends TestCase {
  private const VALUES = [
    'account_id' => 'acct123',
    'access_key_id' => 'R2AKID',
    'secret_access_key' => 'R2SECRET',
    'bucket' => 'scp-backups',
    'folder' => 'panel',
  ];

  private string $tempFile;

  protected function setUp(): void {
    Http::reset();

    $this->tempFile = tempnam(sys_get_temp_dir(), 'r2-test-');
    file_put_contents($this->tempFile, 'dump-contents');
  }

  protected function tearDown(): void {
    if (is_file($this->tempFile)) {
      unlink($this->tempFile);
    }
  }

  private function handler(array $values = self::VALUES): R2Handler {
    return new FixedClockR2Handler(new FakeValueService($values));
  }

  /**
   * An independent SigV4 reference computation to check the handler against.
   */
  private function expectedSignature(
    string $method,
    string $host,
    string $path,
    string $payloadHash
  ): string {
    $amzDate = FixedClockR2Handler::AMZ_DATE;
    $date = substr($amzDate, 0, 8);

    $canonicalRequest = $method . "\n"
      . $path . "\n"
      . "\n"
      . "host:$host\n"
      . "x-amz-content-sha256:$payloadHash\n"
      . "x-amz-date:$amzDate\n"
      . "\n"
      . "host;x-amz-content-sha256;x-amz-date\n"
      . $payloadHash;

    $stringToSign = "AWS4-HMAC-SHA256\n"
      . "$amzDate\n"
      . "$date/auto/s3/aws4_request\n"
      . hash('sha256', $canonicalRequest);

    $kDate = hash_hmac('sha256', $date, 'AWS4R2SECRET', true);
    $kRegion = hash_hmac('sha256', 'auto', $kDate, true);
    $kService = hash_hmac('sha256', 's3', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

    return hash_hmac('sha256', $stringToSign, $kSigning);
  }

  public function testCopyPutsTheObjectWithASignedRequest(): void {
    Http::queue(200, []);

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertCount(1, Http::$requests);
    $request = Http::$requests[0];

    $this->assertSame('PUT', $request['method']);
    $this->assertSame(
      'https://acct123.r2.cloudflarestorage.com/scp-backups/panel/main-database.42.gz',
      $request['url']
    );

    $headers = $request['options']['headers'];
    $payloadHash = hash('sha256', 'dump-contents');
    $this->assertSame($payloadHash, $headers['x-amz-content-sha256']);
    $this->assertSame(FixedClockR2Handler::AMZ_DATE, $headers['x-amz-date']);

    $expected = sprintf(
      'AWS4-HMAC-SHA256 Credential=R2AKID/20260809/auto/s3/aws4_request, SignedHeaders=host;x-amz-content-sha256;x-amz-date, Signature=%s',
      $this->expectedSignature(
        'PUT',
        'acct123.r2.cloudflarestorage.com',
        '/scp-backups/panel/main-database.42.gz',
        $payloadHash
      )
    );
    $this->assertSame($expected, $headers['Authorization']);

    $this->assertIsResource($request['payload']['body']);
  }

  public function testDeleteSendsASignedDelete(): void {
    Http::queue(204, []);

    $this->handler()->delete(new Dest(), 'main-database.42.gz');

    $request = Http::$requests[0];

    $this->assertSame('DELETE', $request['method']);
    $this->assertSame(
      'https://acct123.r2.cloudflarestorage.com/scp-backups/panel/main-database.42.gz',
      $request['url']
    );

    $headers = $request['options']['headers'];
    $this->assertSame(
      R2Handler::EMPTY_PAYLOAD_SHA256,
      $headers['x-amz-content-sha256']
    );
    $this->assertStringContainsString(
      'Signature=' . $this->expectedSignature(
        'DELETE',
        'acct123.r2.cloudflarestorage.com',
        '/scp-backups/panel/main-database.42.gz',
        R2Handler::EMPTY_PAYLOAD_SHA256
      ),
      $headers['Authorization']
    );
  }

  public function testRootFolderAndSpecialCharactersAreEncoded(): void {
    Http::queue(200, []);

    $values = array_merge(self::VALUES, ['folder' => 'my panel']);
    $this->handler($values)->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertSame(
      'https://acct123.r2.cloudflarestorage.com/scp-backups/my%20panel/main-database.42.gz',
      Http::$requests[0]['url']
    );

    Http::reset();
    Http::queue(200, []);

    $values = array_merge(self::VALUES, ['folder' => '/']);
    $this->handler($values)->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertSame(
      'https://acct123.r2.cloudflarestorage.com/scp-backups/main-database.42.gz',
      Http::$requests[0]['url']
    );
  }

  public function testFailedUploadThrowsWithTheS3ErrorMessage(): void {
    Http::queue(
      403,
      '<?xml version="1.0"?><Error><Code>SignatureDoesNotMatch</Code><Message>The request signature we calculated does not match</Message></Error>'
    );

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches(
      '/upload failed.*HTTP 403.*signature we calculated/s'
    );

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');
  }

  public function testMissingConfigurationFailsClearly(): void {
    $values = array_merge(self::VALUES, ['account_id' => '']);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/missing its "account_id" value/');

    $this->handler($values)->copy(new Dest(), $this->tempFile, 'main-database.42.gz');
  }
}
