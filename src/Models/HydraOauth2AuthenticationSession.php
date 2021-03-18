<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property mixed authenticated_at
 * @property string subject
 * @property bool remember
 */
class HydraOauth2AuthenticationSession extends Model
{
    protected $table = 'hydra_oauth2_authentication_session';

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $incrementing = false;
}
