<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string challenge
 * @property string verifier
 * @property string client_id
 * @property string subject
 * @property string request_url
 * @property bool skip
 * @property string requested_scope
 * @property string csrf
 * @property null|\Carbon\Carbon authenticated_at
 * @property mixed requested_at
 * @property string oidc_context
 * @property null|string forced_subject_identifier
 * @property null|string login_session_id
 * @property null|string login_challenge
 * @property string requested_at_audience
 * @property string acr
 * @property string context
 */
class HydraOauth2ConsentRequest extends Model
{
    protected $table = 'hydra_oauth2_consent_request';

    protected $guarded = [];

    protected $primaryKey = 'challenge';

    public $timestamps = false;

    public $incrementing = false;
}
