<?php

namespace Packages\Backup\App\Archive;

use App\Api;

/**
 * Routing for Archives API Requests.
 */
class ArchiveController extends Api\Controller
{
    use Api\Traits\CreateResource;
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var ArchiveRepository
     */
    protected $items;
    /**
     * @var ArchiveFilterService
     */
    protected $filter;
    /**
     * @var ArchiveUpdateService
     */
    protected $update;
    /**
     * @var ArchiveDeleteService
     */
    protected $delete;
    /**
     * @var ArchiveTransformer
     */
    protected $transform;

    /**
     * @param ArchiveRepository $items
     * @param ArchiveTransformer $transform
     * @param ArchiveFilterService $filter
     * @param ArchiveUpdateService $update
     * @param ArchiveDeleteService $delete
     */
    public function boot(
        ArchiveRepository $items,
        ArchiveTransformer $transform,
        ArchiveFilterService $filter,
        ArchiveUpdateService $update,
        ArchiveDeleteService $delete
    ) {
        $this->items     = $items;
        $this->update    = $update;
        $this->delete    = $delete;
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
