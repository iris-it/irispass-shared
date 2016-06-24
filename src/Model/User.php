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
        return config('irispass.user_primary_key');
    }

    /**
     * An user has one organization
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function organization()
    {
        return $this->hasOne('Irisit\IrispassShared\Model\Organization');
    }

    /**
     * An user can be in many groups
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('Irisit\IrispassShared\Model\UserGroup', 'groups_users_pivot', 'user_id', 'group_id')->withTimestamps();
    }
}
