<?php

namespace Irisit\IrispassShared\Model;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'name',
        'address',
        'address_comp',
        'phone',
        'email',
        'website',
        'is_active',
        'status',
        'siret_number',
        'siren_number',
        'tva_number',
        'ape_number',
        'licence_id',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * An organization belongs to an user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('Irisit\IrispassShared\Model\User', 'user_id');
    }

    /**
     * An organization has many users
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('Irisit\IrispassShared\Model\User');
    }

    /**
     * An organization has many groups
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany('Irisit\IrispassShared\Model\UserGroup');
    }

    /**
     * An organization has one website
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function website()
    {
        return $this->hasOne('Irisit\IrispassShared\Model\Website');
    }

    public function licence()
    {
        return $this->belongsTo('Irisit\IrispassShared\Model\Licence');
    }

}
