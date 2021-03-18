<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string subject
 * @property string client_id
 * @property string subject_obfuscated
 */
class HydraOauth2ObfuscatedAuthenticationSession extends Model
{
    protected $table = 'hydra_oauth2_obfuscated_authentication_session';

    protected $guarded = [];

    protected $primaryKey = null;

    public $timestamps = false;

    public $incrementing = false;
}
