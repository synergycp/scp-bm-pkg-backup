<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Api;

/**
 * Routing for Archive Destinations API Requests.
 */
class DestinationController extends Api\Controller
{
    use Api\Traits\CreateResource;
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var DestinationRepository
     */
    protected $items;

    /**
     * @var DestinationFilterService
     */
    protected $filter;

    /**
     * @var DestinationTransformer
     */
    protected $transform;

    /**
     * @var DestinationUpdateService
     */
    protected $update;

    /**
     * @param DestinationRepository $items
     * @param DestinationFilterService $filter
     * @param DestinationUpdateService $update
     * @param DestinationTransformer $transform
     */
    public function boot(
        DestinationRepository $items,
        DestinationFilterService $filter,
        DestinationUpdateService $update,
        DestinationTransformer $transform
    ) {
        $this->items     = $items;
        $this->transform = $transform;
        $this->filter    = $filter;
        $this->update    = $update;
    }

    /**
     * Filter the repository.
     */
    public function filter()
    {
        $this->items->filter([$this->filter, 'viewable']);
    }
}
