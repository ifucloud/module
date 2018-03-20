<?php

namespace Ifucloud\Module\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Service.
 *
 * @package namespace App\Entities;
 */
class Service extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ser_name',
        'ser_state',
        'ser_code',
        'ser_secret',
        'oauth_token',
        'ser_desc',
    ];

}
