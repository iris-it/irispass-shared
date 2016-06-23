<?php

namespace Irisit\IrispassShared\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sub',
        'name',
        'preferred_username',
        'given_name',
        'family_name',
        'email',
        'resource_access',
        'settings'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $casts = [
        'resource_access' => 'json',
        'settings' => 'json'
    ];

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return config_path('irispass.user_primary_key');
    }
}