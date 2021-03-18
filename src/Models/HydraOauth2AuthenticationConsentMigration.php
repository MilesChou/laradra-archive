<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property null|\Carbon\Carbon applied_at
 */
class HydraOauth2AuthenticationConsentMigration extends Model
{
    protected $table = 'hydra_oauth2_authentication_consent_migration';

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $incrementing = false;
}
