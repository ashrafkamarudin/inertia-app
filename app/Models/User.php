<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function isUser()
    {
        return $this->userType == 'user';
    }

    public function getRolesAttribute()
    {
         // quick fix for first sdk call when no role yet from account.
        if (!Cache::has('userrole') or config('cache.default') !== 'redis')
            return Collection::make(['Dummy Role']);

        return Cache::remember('userrole', now()->addHour(), fn() => app('RunCloud.InternalSDK')
            ->service('account')
            ->get('/internal/resources/find/User/first')
            ->payload([
                \GuzzleHttp\RequestOptions::JSON => [
                    Arr::dot('where.id') => auth()->user()->id,
                    'includes' => ['roles'],
                ],
            ])
            ->execute()->roles
        );
    }

    public function getTeamServerIDsAttribute()
    {
       return [];
    }
}
