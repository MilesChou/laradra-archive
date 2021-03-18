<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string sid
 * @property string kid
 * @property int version
 * @property string keydata
 * @property mixed created_at
 * @property mixed pk
 */
class HydraJwk extends Model
{
    protected $table = 'hydra_jwk';

    protected $guarded = [];

    protected $primaryKey = 'pk';

    public $timestamps = false;

    public $incrementing = false;
}
