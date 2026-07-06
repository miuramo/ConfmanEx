<?php

namespace App\Actions;

use Laravel\Passkeys\Actions\StorePasskey as BaseStorePasskey;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\CredentialRecord;

class StorePasskey extends BaseStorePasskey
{
    /**
     * Create the passkey record for the user.
     * 登録時に counter, transports, backed_up, device_type を一緒に保存する。
     */
    public function createPasskey(
        PasskeyUser $user,
        string $name,
        CredentialRecord $source
    ): Passkey {
        $credentialId = Base64UrlSafe::encodeUnpadded($source->publicKeyCredentialId);

        /** @var \App\Models\Passkey $passkey */
        $passkey = $user->passkeys()->create([
            'name'          => $name,
            'credential_id' => $credentialId,
            'credential'    => json_decode(WebAuthn::toJson($source), true, flags: JSON_THROW_ON_ERROR),
            'counter'       => $source->counter,
            'transports'    => $source->transports ?: null,
            'backed_up'     => $source->backupStatus ?? false,
            'device_type'   => ($source->backupEligible === true) ? 'multi_device' : 'single_device',
        ]);

        return $passkey;
    }
}
