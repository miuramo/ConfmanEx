<?php

namespace App\Actions;

use Laravel\Passkeys\Actions\VerifyPasskey as BaseVerifyPasskey;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Support\WebAuthn;
use Webauthn\CredentialRecord;

class VerifyPasskey extends BaseVerifyPasskey
{
    /**
     * Update the passkey after successful authentication.
     * credential JSON と共に counter / backed_up を個別カラムにも反映する。
     */
    public function updatePasskey(Passkey $passkey, CredentialRecord $source): void
    {
        $passkey->forceFill([
            'credential'  => json_decode(WebAuthn::toJson($source), true, flags: JSON_THROW_ON_ERROR),
            'last_used_at' => now(),
            'counter'      => $source->counter,
            'backed_up'    => $source->backupStatus ?? false,
            'device_type'  => ($source->backupEligible === true) ? 'multi_device' : 'single_device',
        ])->save();
    }
}
