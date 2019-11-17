<?php

namespace Packages\Backup\App\Configuration;

use App\Api;

/**
 * Routing for Configuration Archives API Requests.
 */
class ConfigurationController extends Api\Controller {
  use Api\Traits\CreateResource {
    store as _createResourceStore;
  }
  use Api\Traits\ShowResource;
  use Api\Traits\ListResource;

  /**
   * @var ConfigurationRepository
   */
  protected $items;
  /**
   * @var ConfigurationFilterService
   */
  protected $filter;
  /**
   * @var ConfigurationUpdateService
   */
  protected $update;
  /**
   * @var ConfigurationTransformer
   */
  protected $transform;

  /**
   * @var ConfigurationDownloader
   */
  protected $downloader;

  /**
   * ConfigurationController constructor.
   *
   * @param ConfigurationRepository $items
   * @param ConfigurationFilterService $filter
   * @param ConfigurationUpdateService $update
   * @param ConfigurationTransformer $transform
   * @param ConfigurationDownloader $downloader
   */
  public function boot(
    ConfigurationRepository $items,
    ConfigurationFilterService $filter,
    ConfigurationUpdateService $update,
    ConfigurationTransformer $transform,
    ConfigurationDownloader $downloader
  ) {
    $this->items = $items;
    $this->filter = $filter;
    $this->update = $update;
    $this->transform = $transform;
    $this->downloader = $downloader;
  }

  /**
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function filter() {
    return $this->filter->viewable($this->items->query());
  }

  /**
   * TODO: this whole setup is super janky. Create should generate some single-use token that lets you download the
   * configuration via GET request.
   *
   * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
   * @throws \Exception
   */
  public function store() {
    $response = $this->_createResourceStore();
    if (!obj_get($response->getData(), 'data.id')) {
      return $response; // error state
    }

    return $this->downloader->download();
  }
}
