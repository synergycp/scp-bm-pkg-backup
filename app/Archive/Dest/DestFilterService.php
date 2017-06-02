<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Api\ApiAuthService;
use App\Server\ServerRepository;
use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class DestFilterService extends FilterService
{
    /**
     * @var DestListRequest
     */
    protected $request;

    protected $requestClass = DestListRequest::class;

    /**
     * @param Builder $query
     *
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     * @throws \App\Auth\Exceptions\InvalidIpAddress
     */
    public function viewable(Builder $query)
    {

    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function query(Builder $query)
    {
        $this->prepare()->apply($query);
        // Filter raw text search
        if ($searchText = $this->request->input('q')) {
            $query->search(
                $this->search->search($searchText)
            );
        }
        return $query;
    }
}
