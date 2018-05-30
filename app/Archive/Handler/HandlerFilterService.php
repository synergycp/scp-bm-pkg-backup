<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Api\ApiAuthService;
use App\Server\ServerRepository;
use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class HandlerFilterService extends FilterService
{
    /**
     * @var ApiAuthService
     */
    protected $auth;
    /**
     * @var ServerRepository
     */
    protected $servers;
    /**
     * @var HandlerListRequest
     */
    protected $request;

    protected $requestClass = HandlerListRequest::class;

    /**
     * @param ApiAuthService $auth
     * @param ServerRepository $servers
     */
    public function boot(
        ApiAuthService $auth,
        ServerRepository $servers
    ) {
        $this->auth    = $auth;
        $this->servers = $servers;
    }

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

        switch ($type = $this->request->input('type')) {
            case 'source':
                $query->where('type', HandlerType::SOURCE);
                break;
            case 'destination':
                $query->where('type', HandlerType::DEST);
                break;
            default:
                abort(409, "There are no source with '{$type}' type");
        }

        return $query;
    }
}
