<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string challenge
 * @property string granted_scope
 * @property bool remember
 * @property int remember_for
 * @property string error
 * @property mixed requested_at
 * @property string session_access_token
 * @property string session_id_token
 * @property null|\Carbon\Carbon authenticated_at
 * @property bool was_used
 * @property string granted_at_audience
 */
class HydraOauth2ConsentRequestHandled extends Model
{
    protected $table = 'hydra_oauth2_consent_request_handled';

    protected $guarded = [];

    protected $primaryKey = 'challenge';

    public $timestamps = false;

    public $incrementing = false;
}
