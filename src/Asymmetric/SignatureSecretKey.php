<?php
declare(strict_types=1);
namespace ParagonIE\Halite\Asymmetric;

use ParagonIE\Halite\Alerts\{
    CannotPerformOperation,
    InvalidKey,
    InvalidType
};
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Util as CryptoUtil;

/**
 * Class SignatureSecretKey
 * @package ParagonIE\Halite\Asymmetric
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
final class SignatureSecretKey extends SecretKey
{
    /**
     * SignatureSecretKey constructor.
     *
     * @param HiddenString $keyMaterial - The actual key data
     *
     * @throws InvalidKey
     * @throws CannotPerformOperation
     * @throws InvalidType
     */
    public function __construct(HiddenString $keyMaterial)
    {
        if (CryptoUtil::safeStrlen($keyMaterial->getString()) !== \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new InvalidKey(
                'Signature secret key must be CRYPTO_SIGN_SECRETKEYBYTES bytes long'
            );
        }
        parent::__construct($keyMaterial);
        $this->isSigningKey = true;
    }
    
    /**
     * See the appropriate derived class.
     * 
     * @return SignaturePublicKey
     * @throws CannotPerformOperation
     * @throws InvalidType
     */
    public function derivePublicKey()
    {
        $publicKey = \sodium_crypto_sign_publickey_from_secretkey(
            $this->getRawKeyMaterial()
        );
        return new SignaturePublicKey(new HiddenString($publicKey));
    }

    /**
     * Get an encryption secret key from a signing secret key.
     *
     * @return EncryptionSecretKey
     * @throws CannotPerformOperation
     * @throws InvalidType
     */
    public function getEncryptionSecretKey(): EncryptionSecretKey
    {
        $ed25519_sk = $this->getRawKeyMaterial();
        $x25519_sk = \sodium_crypto_sign_ed25519_sk_to_curve25519(
            $ed25519_sk
        );
        return new EncryptionSecretKey(
            new HiddenString($x25519_sk)
        );
    }
}
