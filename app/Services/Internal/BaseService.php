<?php

namespace App\Services\Internal;

use App\Models\User;
use GuzzleHttp\RequestOptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class BaseService
{
    protected $user;

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    public function log(string $message, int $severity)
    {
        // add inside log
        app('RunCloud.InternalSDK')
            ->service('bigdata')
            ->post('/internal/log/account')
            ->payload([
                RequestOptions::JSON => [
                    'user_id'  => $this->user->id,
                    'severity' => $severity,
                    'content'  => $message,
                ],
            ])
            ->queue();
    }

    public function notify($payload)
    {
        app('RunCloud.InternalSDK')
            ->service('notification')
            ->post('/internal/global-notifications')
            ->payload([
                RequestOptions::JSON => $payload,
            ])->queue();
    }
}
