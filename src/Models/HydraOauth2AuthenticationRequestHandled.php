<?php

namespace Laradra\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string challenge
 * @property string subject
 * @property bool remember
 * @property int remember_for
 * @property string error
 * @property string acr
 * @property mixed requested_at
 * @property null|\Carbon\Carbon authenticated_at
 * @property bool was_used
 * @property null|string forced_subject_identifier
 * @property string context
 */
class HydraOauth2AuthenticationRequestHandled extends Model
{
    protected $table = 'hydra_oauth2_authentication_request_handled';

    protected $guarded = [];

    protected $primaryKey = 'challenge';

    public $timestamps = false;

    public $incrementing = false;
}
