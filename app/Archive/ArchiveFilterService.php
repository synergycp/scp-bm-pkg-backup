<?php

namespace Packages\Backup\App\Archive;

use App\Api\ApiAuthService;
use App\Server\ServerRepository;
use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class ArchiveFilterService extends FilterService
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
     * @var ReportListRequest
     */
    protected $request;

    protected $requestClass = ArchiveListRequest::class;

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

        if ($this->request->exists('status')) {
            $query->where('status', $this->request->input('status'));
        }

        return $query->orderBy('id', 'desc');
    }
}
