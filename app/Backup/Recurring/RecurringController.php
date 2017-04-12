<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api;

/**
 * Routing for Abuse Report API Requests.
 */
class RecurringController extends Api\Controller
{
    use Api\Traits\CreateResource;
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var RecurringRepository
     */
    protected $items;
    /**
     * @var RecurringFilterService
     */
    protected $filter;

    /**
     * @var RecurringTransformer
     */
    protected $transform;

    /**
     * @param RecurringRepository $items
     * @param RecurringFilterService $filter
     * @param RecurringTransformer $transform
     */
    public function boot(
        RecurringRepository $items,
        RecurringFilterService $filter,
        RecurringTransformer $transform
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