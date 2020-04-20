<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use DateTime;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSTokenSupport;
use Ramsey\Uuid\Uuid;
use function json_decode;

class Claims
{
    /** @var mixed[] The claims config. */
    private $config;
    /** @var ClaimCheckerManager $checker The claim checker. */
    private $checker;
    /** @var string[] $mandatory The mandatory claims. */
    private $mandatory;

    /**
     * @param mixed[] $config The claims configuration.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->setChecker();
    }

    /**
     * Create the checker based on the provided claims configuration.
     */
    private function setChecker(): void
    {
        $checkers = [];

        if (isset($this->config['iss'])) {
            // "iss" (Issuer), see https://tools.ietf.org/html/rfc7519#section-4.1.1
            $checkers[]        = new IssuerChecker([$this->config['iss']]);
            $this->mandatory[] = 'iss';
        }

        if (isset($this->config['aud'])) {
            // "aud" (Audience), see https://tools.ietf.org/html/rfc7519#section-4.1.3
            $checkers[]        = new AudienceChecker($this->config['aud']);
            $this->mandatory[] = 'aud';
        }

        if (isset($this->config['exp'])) {
            // "exp" (Expiration Time), see https://tools.ietf.org/html/rfc7519#section-4.1.4
            $checkers[]        = new ExpirationTimeChecker();
            $this->mandatory[] = 'exp';
        }

        if (isset($this->config['nbf'])) {
            // "nbf" (Not Before), see https://tools.ietf.org/html/rfc7519#section-4.1.5
            $checkers[]        = new NotBeforeChecker();
            $this->mandatory[] = 'nbf';
        }

        if (isset($this->config['iat'])) {
            // "iat" (Issued At), see https://tools.ietf.org/html/rfc7519#section-4.1.6
            $checkers[]        = new IssuedAtChecker();
            $this->mandatory[] = 'iat';
        }

        $this->checker = new ClaimCheckerManager($checkers);
    }

    /**
     * @param JWS      $jws The JWS to check.
     * @param Key|null $key The key to check the algorithm headers.
     *
     * @return bool If the JWS is valid. In practise, it always returns true since an exception will be thrown on
     *              failure.
     *
     * @throws InvalidClaimException
     * @throws MissingMandatoryClaimException
     *
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function check(JWS $jws, ?Key $key = null): bool
    {
        $claims = json_decode($jws->getPayload(), true);
        $this->checker->check($claims);

        if ($key !== null) {
            // If a key is provided, also check the algorithm header
            $algorithmChecker = new HeaderCheckerManager(
                [new AlgorithmChecker([$key->getAlgorithm()->name()])],
                [new JWSTokenSupport()]
            );

            $algorithmChecker->check($jws, 0);
        }

        return true;
    }

    /**
     * Generate claims based on the passed configuration.
     *
     * @return mixed[] The configured claims payload.
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function generate(): array
    {
        $claims = [];

        if (isset($this->config['iss'])) {
            $claims['iss'] = $this->config['iss'];
        }

        if (isset($this->config['aud'])) {
            $claims['aud'] = $this->config['aud'];
        }

        if (isset($this->config['exp'])) {
            $claims['exp'] = (new DateTime($this->config['exp']))->getTimestamp();
        }

        if (isset($this->config['nbf'])) {
            $claims['nbf'] = (new DateTime($this->config['nbf']))->getTimestamp();
        }

        if (isset($this->config['iat'])) {
            $claims['iat'] = (new DateTime($this->config['iat']))->getTimestamp();
        }

        if (isset($this->config['jid'])) {
            $claims['jid'] = Uuid::uuid4();
        }

        return $claims;
    }
}
