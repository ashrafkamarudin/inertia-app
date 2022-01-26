<?php

namespace App\Http\Controllers;

use App\Helpers\Filter;
use Illuminate\Http\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    public function index()
    {
        Auth::loginUsingId(111);

        dd(app('RunCloud.InternalSDK')
            ->service('account')
            ->get('/internal/resources/find/User/first')
            ->payload([
                \GuzzleHttp\RequestOptions::JSON => [
                    'where' => [
                        'id' => 111
                    ],
                    'includes' => ['roles'],
                ],
            ])
            ->execute()->roles);

        $user  = auth()->user();
        $owner = request('owner', 'all');

        // dd($user);

        $result = app('RunCloud.InternalSDK')
            ->multiService([
                'servers' => app('RunCloud.InternalSDK')
                    ->service('server')
                    ->get('/internal/servers')
                    ->payload([
                        RequestOptions::JSON => Filter::request(request(), [
                            'user_id'       => $user->id,
                            'perPage'       => 20,
                            'page'          => request('page'),
                            'owner'         => $owner,
                            'teamServerIDs' => $user->teamServerIDs,
                            'select'        => [],
                            'with_tags'     => true,
                        ]),
                    ]),
                
                'personal_servers' => app('RunCloud.InternalSDK')
                    ->service('server')
                    ->get('/internal/servers')
                    ->payload([
                        RequestOptions::JSON => Filter::request(request(), [
                            'user_id'       => $user->isUser() ? $user->id : $user->owner_id,
                            'select'        => [],
                            'perPage'       => 20,
                            'page'          => request('page'),
                            'owner'         => $owner,
                            'teamServerIDs' => $user->teamServerIDs,
                            'select'        => [],
                            'with_tags'     => true,
                        ]),
                    ]),

                'tags'    => app('RunCloud.InternalSDK')
                    ->service('bigdata')
                    ->get('/internal/resources/find/Tag/get')
                    ->payload([
                        RequestOptions::JSON => [
                            'where'      => [
                                'user_id' => $user->id,
                            ],
                            'orWhereHas' => [
                                'taggables' => [
                                    'where'   => [
                                        'taggable_type' => 'Server',
                                    ],
                                    'whereIn' => [
                                        'taggable_id' => $user->teamServerIDs,
                                    ],
                                ],
                            ],
                            'select'     => ['id', 'name'],
                        ],
                    ]),
            ])
            ->execute();

        $serverGeoRecords = [];
        $userIDs          = [];

        $servers                   = collect($result['servers']->data);
        $personal_servers          = collect($result['personal_servers']->data);
        $recentViewedServers       = collect()->pad($servers->count(), -1);
        $recentViewedServersCookie = collect(json_decode(request()->cookie('rvs', '[]')));
        $latestAgentVersions = [
            'nginx' => getLatestAgentVersion('nginx'),
            'ols'   => getLatestAgentVersion('ols'),
        ];

        foreach ($servers as $server) {
            $serverGeoRecords[$server->id] = getGeoRecordFromIpAddress($server->ipAddress);
            $server->url                   = serverGoToURL($server);

            if (($search = $recentViewedServersCookie->search($server->id)) !== false) {
                $recentViewedServers[$search] = $server;
            }

            $server->canViewServerHealth = isServerOwnerCan($server->user_id, 'server:health:view');
            $userIDs[] = $server->user_id;

            if ($server->agentVersion !== null) {
                $server->isAgentLatestVersion = isLatestAgentVersion($server->agentVersion, $latestAgentVersions[$server->webServerType]);
            }

            if ($server->user_id !== $user->id) {
                $server->isOwner = false;
            } else {
                $server->isOwner = true;
            }
        }

        $recentViewedServers = $recentViewedServers->reject(function ($server) {
            return $server === -1;
        });

        $tagged = [];
        foreach ($servers as $server) {
            $tagged = array_merge($tagged, $server->tags);
        };
        $tagged = array_unique($tagged);

        // TODO : NEED TO LIMIT SERVER DATA TO FRONT-END
        dd($result['servers']);

        // return view('servers.index', [
        //     'servers'             => Paginator::generate($result['servers'], null, ['pageName' => 'page']),
        //     'serverGeoRecords'    => $serverGeoRecords,
        //     'tags'                => collect($result['tags']),
        //     'tagged'              => collect($tagged),
        //     'recentViewedServers' => $recentViewedServers,
        //     'personal_servers'    => $personal_servers
        // ]);
    }

    public function show()
    {
        # code...
    }
}
