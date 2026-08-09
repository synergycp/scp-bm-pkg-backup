<?php

namespace Packages\Backup\Tests;

use Illuminate\Support\Facades\Http;
use Packages\Backup\App\Archive\Dest\Dest;
use Packages\Backup\App\Destinations\B2\B2Handler;
use Packages\Backup\Tests\Support\FakeValueService;
use PHPUnit\Framework\TestCase;

final class B2HandlerTest extends TestCase {
  private const VALUES = [
    'key_id' => '000abc123',
    'application_key' => 'K000secretsecret',
    'bucket' => 'scp-backups',
    'folder' => 'panel',
  ];

  private string $tempFile;

  protected function setUp(): void {
    Http::reset();

    $this->tempFile = tempnam(sys_get_temp_dir(), 'b2-test-');
    file_put_contents($this->tempFile, 'dump-contents');
  }

  protected function tearDown(): void {
    if (is_file($this->tempFile)) {
      unlink($this->tempFile);
    }
  }

  private function handler(array $values = self::VALUES): B2Handler {
    return new B2Handler(new FakeValueService($values));
  }

  private function queueAuthorize(array $allowed = []): void {
    Http::queue(200, [
      'accountId' => 'acct1',
      'authorizationToken' => 'auth-token',
      'apiUrl' => 'https://api002.backblazeb2.com',
      'allowed' => $allowed,
    ]);
  }

  private function queueListBuckets(): void {
    Http::queue(200, [
      'buckets' => [
        ['bucketId' => 'bucket-id-1', 'bucketName' => 'scp-backups'],
      ],
    ]);
  }

  public function testCopyAuthorizesResolvesBucketAndUploads(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, [
      'uploadUrl' => 'https://pod.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token',
    ]);
    Http::queue(200, ['fileId' => 'f1']);

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    [$auth, $buckets, $uploadUrl, $upload] = Http::$requests;

    $this->assertStringContainsString('b2_authorize_account', $auth['url']);
    $this->assertSame(
      ['000abc123', 'K000secretsecret'],
      $auth['options']['basicAuth']
    );

    $this->assertSame(
      'https://api002.backblazeb2.com/b2api/v2/b2_list_buckets',
      $buckets['url']
    );
    $this->assertSame(
      ['accountId' => 'acct1', 'bucketName' => 'scp-backups'],
      $buckets['payload']
    );

    $this->assertSame(['bucketId' => 'bucket-id-1'], $uploadUrl['payload']);

    $this->assertSame('https://pod.backblazeb2.com/upload', $upload['url']);
    $headers = $upload['options']['headers'];
    $this->assertSame('upload-token', $headers['Authorization']);
    $this->assertSame('panel/main-database.42.gz', $headers['X-Bz-File-Name']);
    $this->assertSame('b2/x-auto', $headers['Content-Type']);
    $this->assertSame(sha1('dump-contents'), $headers['X-Bz-Content-Sha1']);
    $this->assertIsResource($upload['payload']['body']);
  }

  public function testCopyUsesTheBucketOfARestrictedKeyWithoutListing(): void {
    $this->queueAuthorize([
      'bucketId' => 'restricted-id',
      'bucketName' => 'scp-backups',
    ]);
    Http::queue(200, [
      'uploadUrl' => 'https://pod.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token',
    ]);
    Http::queue(200, ['fileId' => 'f1']);

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertCount(3, Http::$requests, 'b2_list_buckets is skipped');
    $this->assertSame(
      ['bucketId' => 'restricted-id'],
      Http::$requests[1]['payload']
    );
  }

  public function testCopyRejectsAKeyRestrictedToADifferentBucket(): void {
    $this->queueAuthorize([
      'bucketId' => 'other-id',
      'bucketName' => 'someone-elses-bucket',
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/restricted to bucket/');

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');
  }

  public function testCopyFailsClearlyWhenAuthorizationFails(): void {
    Http::queue(401, ['code' => 'unauthorized', 'message' => 'bad key']);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches(
      '/authorization failed.*HTTP 401.*bad key/s'
    );

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');
  }

  public function testCopyRetriesTheUploadOnceWithAFreshUploadUrl(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, [
      'uploadUrl' => 'https://pod1.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token-1',
    ]);
    Http::queue(503, ['message' => 'try again']);
    Http::queue(200, [
      'uploadUrl' => 'https://pod2.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token-2',
    ]);
    Http::queue(200, ['fileId' => 'f1']);

    $this->handler()->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertCount(6, Http::$requests);
    $this->assertSame(
      'https://pod2.backblazeb2.com/upload',
      Http::$requests[5]['url']
    );
  }

  public function testCopyWithoutAFolderUploadsToTheBucketRoot(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, [
      'uploadUrl' => 'https://pod.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token',
    ]);
    Http::queue(200, ['fileId' => 'f1']);

    $values = array_merge(self::VALUES, ['folder' => '/']);
    $this->handler($values)->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertSame(
      'main-database.42.gz',
      Http::$requests[3]['options']['headers']['X-Bz-File-Name']
    );
  }

  public function testFileNamesArePercentEncodedForTheUploadHeader(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, [
      'uploadUrl' => 'https://pod.backblazeb2.com/upload',
      'authorizationToken' => 'upload-token',
    ]);
    Http::queue(200, ['fileId' => 'f1']);

    $values = array_merge(self::VALUES, ['folder' => 'my panel/backups']);
    $this->handler($values)->copy(new Dest(), $this->tempFile, 'main-database.42.gz');

    $this->assertSame(
      'my%20panel/backups/main-database.42.gz',
      Http::$requests[3]['options']['headers']['X-Bz-File-Name']
    );
  }

  public function testDeleteRemovesOnlyExactNameVersions(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, [
      'files' => [
        ['fileName' => 'panel/main-database.42.gz', 'fileId' => 'f-old'],
        ['fileName' => 'panel/main-database.421.gz', 'fileId' => 'f-other'],
      ],
    ]);
    Http::queue(200, []);

    $this->handler()->delete(new Dest(), 'main-database.42.gz');

    $this->assertCount(4, Http::$requests);

    $list = Http::$requests[2];
    $this->assertStringContainsString('b2_list_file_versions', $list['url']);
    $this->assertSame('panel/main-database.42.gz', $list['payload']['prefix']);

    $delete = Http::$requests[3];
    $this->assertStringContainsString('b2_delete_file_version', $delete['url']);
    $this->assertSame(
      ['fileName' => 'panel/main-database.42.gz', 'fileId' => 'f-old'],
      $delete['payload']
    );
  }

  public function testDeleteSucceedsWhenTheFileIsAlreadyGone(): void {
    $this->queueAuthorize();
    $this->queueListBuckets();
    Http::queue(200, ['files' => []]);

    $this->handler()->delete(new Dest(), 'main-database.42.gz');

    $this->assertCount(3, Http::$requests, 'nothing is deleted');
  }
}
