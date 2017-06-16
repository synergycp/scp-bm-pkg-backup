<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api;

/**
 * Routing for Archive Source API Requests.
 */
class SourceController extends Api\Controller
{
    use Api\Traits\CreateResource;
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
     * @var SourceUpdateService
     */
    protected $update;
    /**
     * @var SourceDeleteService
     */
    protected $delete;

    /**
     * @param SourceRepository $items
     * @param SourceFilterService $filter
     * @param SourceUpdateService $update
     * @param SourceTransformer $transform
     * @param SourceDeleteService $delete
     */
    public function boot(
        SourceRepository $items,
        SourceFilterService $filter,
        SourceUpdateService $update,
        SourceTransformer $transform,
        SourceDeleteService $delete
    ) {
        $this->items     = $items;
        $this->transform = $transform;
        $this->filter    = $filter;
        $this->update    = $update;
        $this->delete    = $delete;
    }

    /**
     * Filter the repository.
     */
    public function filter()
    {
        $this->items->filter([$this->filter, 'viewable']);
    }
}
