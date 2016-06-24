<?php

namespace Irisit\IrispassShared\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class Website extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'websites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['identifier', 'username', 'email', 'is_active', 'url'];

    protected $casts = [
        'is_active' => 'boolean'
    ];


    public function organization()
    {
        return $this->belongsTo('Irisit\IrispassShared\Model\Organization', 'organization_id');
    }

}
