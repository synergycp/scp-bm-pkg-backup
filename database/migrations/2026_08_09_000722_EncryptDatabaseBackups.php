<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Packages\Backup\App\Archive\Archive;
use Packages\Backup\App\Archive\Handler\Handler;
use Packages\Backup\App\Archive\Source\Source;
use Packages\Backup\App\Sources\Mysql\MysqlDumpHandler;

class EncryptDatabaseBackups extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    // Backfill the remote file name of Archives created before it was
    // recorded at copy time, while the Source extension still matches the
    // files on the destinations. Without this, changing the extension below
    // would break remote deletion of those old Archives.
    Archive::query()
      ->whereNull('dest_file')
      ->with('source')
      ->chunkById(500, function ($archives) {
        foreach ($archives as $archive) {
          if (!$archive->source) {
            continue;
          }

          $archive->dest_file = sprintf(
            '%s.%d.%s',
            Str::slug($archive->source->name),
            $archive->getKey(),
            $archive->source->ext
          );
          $archive->save();
        }
      });

    // New backups are encrypted gzip streams: give them an extension that
    // says so.
    $this->mysqlSources()->update(['ext' => 'gz.enc']);
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    $this->mysqlSources()->update(['ext' => 'gz']);
  }

  /**
   * @return \Illuminate\Database\Eloquent\Builder
   */
  protected function mysqlSources() {
    $handlerIds = Handler::query()
      ->where('class', MysqlDumpHandler::class)
      ->pluck('id');

    return Source::query()->whereIn('handler_id', $handlerIds);
  }
}
