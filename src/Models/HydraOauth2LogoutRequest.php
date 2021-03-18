<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string challenge
 * @property string verifier
 * @property string subject
 * @property string sid
 * @property null|string client_id
 * @property string request_url
 * @property string redir_url
 * @property bool was_used
 * @property bool accepted
 * @property bool rejected
 * @property bool rp_initiated
 */
class HydraOauth2LogoutRequest extends Model
{
    protected $table = 'hydra_oauth2_logout_request';

    protected $guarded = [];

    protected $primaryKey = 'challenge';

    public $timestamps = false;

    public $incrementing = false;
}
