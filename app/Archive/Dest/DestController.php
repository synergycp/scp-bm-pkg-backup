<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Api;

/**
 * Routing for Archive Destinations API Requests.
 */
class DestController extends Api\Controller
{
    use Api\Traits\CreateResource;
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var DestRepository
     */
    protected $items;

    /**
     * @var DestFilterService
     */
    protected $filter;

    /**
     * @var DestTransformer
     */
    protected $transform;

    /**
     * @var DestUpdateService
     */
    protected $update;

    /**
     * @var DestDeleteService
     */
    protected $delete;

    /**
     * @param DestRepository $items
     * @param DestFilterService $filter
     * @param DestUpdateService $update
     * @param DestDeleteService $delete
     * @param DestTransformer $transform
     */
    public function boot(
        DestRepository $items,
        DestFilterService $filter,
        DestUpdateService $update,
        DestDeleteService $delete,
        DestTransformer $transform
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
