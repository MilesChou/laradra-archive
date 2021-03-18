<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property string client_name
 * @property string client_secret
 * @property string redirect_uris
 * @property string grant_types
 * @property string response_types
 * @property string scope
 * @property string owner
 * @property string policy_uri
 * @property string tos_uri
 * @property string client_uri
 * @property string logo_uri
 * @property string contacts
 * @property int client_secret_expires_at
 * @property string sector_identifier_uri
 * @property string jwks
 * @property string jwks_uri
 * @property string request_uris
 * @property string token_endpoint_auth_method
 * @property string request_object_signing_alg
 * @property string userinfo_signed_response_alg
 * @property string subject_type
 * @property string allowed_cors_origins
 * @property mixed pk
 * @property string audience
 * @property \Carbon\Carbon
 * @property string frontchannel_logout_uri
 * @property bool frontchannel_logout_session_required
 * @property string post_logout_redirect_uris
 * @property string backchannel_logout_uri
 * @property bool backchannel_logout_session_required
 * @property string metadata
 */
class HydraClient extends Model
{


    protected $table = 'hydra_client';

    protected $guarded = [];

    protected $primaryKey = 'pk';

    public $timestamps = false;

    public $incrementing = false;
}
