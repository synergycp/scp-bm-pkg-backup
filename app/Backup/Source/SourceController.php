<?php

namespace Packages\Backup\App\Backup\Source;

use App\Api;

/**
 * Routing for Abuse Report API Requests.
 */
class SourceController extends Api\Controller
{
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var SourceRepository
     */
    protected $items;
    /**
     * @var SourceFilterService
     */
    protected $filter;

    /**
     * @var SourceTransformer
     */
    protected $transform;

    /**
     * @param SourceRepository $items
     * @param SourceFilterService $filter
     * @param SourceTransformer $transform
     */
    public function boot(
        SourceRepository $items,
        SourceFilterService $filter,
        SourceTransformer $transform
    ) {
        $this->items     = $items;
        $this->transform = $transform;
        $this->filter    = $filter;
    }

    /**
     * Filter the repository.
     */
    public function filter()
    {
        $this->items->filter([$this->filter, 'viewable']);
    }
}