<?php

namespace Packages\Backup\App\Configuration;

use App\Http\Requests\RestRequest;

class ConfigurationFormRequest extends RestRequest {
  public function boot() {
    $this->rules = [];
  }
}
