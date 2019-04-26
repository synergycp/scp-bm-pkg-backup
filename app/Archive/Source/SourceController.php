<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api;

/**
 * Routing for Archive Source API Requests.
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

    protected function checkCanEdit()
    {
        if (!$this->permission->has('pkg.backup.write')) {
            abort(403, 'You do not have access to the backups package.');
        }
    }
}
