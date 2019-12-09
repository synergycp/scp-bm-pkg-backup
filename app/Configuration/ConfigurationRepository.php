<?php

namespace Packages\Backup\App\Configuration;

use App\Database\ModelRepository;

class ConfigurationRepository extends ModelRepository {
  protected $model = Configuration::class;
}
