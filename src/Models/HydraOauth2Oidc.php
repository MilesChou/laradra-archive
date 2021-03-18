<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string signature
 * @property string request_id
 * @property mixed requested_at
 * @property string client_id
 * @property string scope
 * @property string granted_scope
 * @property string form_data
 * @property string session_data
 * @property string subject
 * @property bool active
 * @property string requested_audience
 * @property string granted_audience
 * @property null|string challenge_id
 */
class HydraOauth2Oidc extends Model
{
    protected $table = 'hydra_oauth2_oidc';

    protected $guarded = [];

    protected $primaryKey = 'signature';

    public $timestamps = false;

    public $incrementing = false;
}
