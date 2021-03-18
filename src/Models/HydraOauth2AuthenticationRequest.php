<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string challenge
 * @property string requested_scope
 * @property string verifier
 * @property string csrf
 * @property string subject
 * @property string request_url
 * @property bool skip
 * @property string client_id
 * @property mixed requested_at
 * @property null|\Carbon\Carbon authenticated_at
 * @property string oidc_context
 * @property null|string login_session_id
 * @property string requested_at_audience
 */
class HydraOauth2AuthenticationRequest extends Model
{


    protected $table = 'hydra_oauth2_authentication_request';

    protected $guarded = [];

    protected $primaryKey = 'challenge';

    public $timestamps = false;

    public $incrementing = false;
}
