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
        'settings',
        'role_id'
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
     * this assign roles to an user (obvious isn'it ?)
     *
     * @param $role
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function assignRole($role)
    {
        $role = Role::where('name', $role)->firstOrFail();

        return $this->role()->associate($role)->save();
    }

    /**
     * check if user has role
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->role->name == $role;
        }

        return false;
    }

    /**
     * check if user has role
     *
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            foreach ($this->role->permissions as $permissionRole) {
                if ($permissionRole->name == $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * An user has many roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function role()
    {
        return $this->belongsTo('Irisit\IrispassShared\Model\Role');
    }


    /**
     * An user has one organization
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function provider()
    {
        return $this->hasOne('Irisit\IrispassShared\Model\UserProvider');
    }

    /**
     * An user has one organization
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function organization()
    {
        return $this->belongsTo('Irisit\IrispassShared\Model\Organization');
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
