<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Environment;

use OxidEsales\Eshop\Core\Registry;

class SessionCookieLogics
{
    /**
     * Returns session challenge token
     *
     * @return string|null
     */
    public function getSessionToken(): ?string
    {
        $session = Registry::getSession();

        return $session->getSessionChallengeToken() ?: null;
    }

    /**
     * Returns session id
     *
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        $session = Registry::getSession();

        return $session->getId() ?: null;
    }
}
