<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Core;

use foun10\SmartProxy\Environment\EnvironmentKeyLogics;
use foun10\SmartProxy\Environment\SessionCookieLogics;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class SmartProxy
{
    const EMPTY_COOKIE_VALUE = 'empty';

    const COOKIE_ENVIRONMENT_KEY = 'smartproxy_env_key';
    const COOKIE_SESSION_TOKEN = 'smartproxy_stoken';
    const COOKIE_SESSION_ID = 'smartproxy_sid';
    const PLACEHOLDER_ENCLOSURE = '###%s###';

    protected $cookiesSet = false;

    /**
     * Checks if smart proxy logics should be applied
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (isAdmin() || $_SERVER['REQUEST_METHOD'] === 'POST' || !$this->isCacheableCall()) {
            return false;
        }

        return (bool) Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_ACTIVE_GENERAL');
    }

    /**
     * Set up cookies used for smartproxy
     */
    public function setCookies(): void
    {
        if ($this->cookiesSet || (bool) Registry::getRequest()->getRequestEscapedParameter('skipSession')) {
            return;
        }

        $utilsServer = Registry::getUtilsServer();
        $cookieSettings = [0, '/', null, true, false, false];

        $utilsServer->setOxCookie(
            self::COOKIE_ENVIRONMENT_KEY,
            $this->getCookieEnvironmentKey(),
            ...$cookieSettings
        );

        $utilsServer->setOxCookie(
            self::COOKIE_SESSION_TOKEN,
            $this->getCookieSessionToken() ?? self::EMPTY_COOKIE_VALUE,
            ...$cookieSettings
        );

        $utilsServer->setOxCookie(
            self::COOKIE_SESSION_ID,
            $this->getCookieSessionId() ?? self::EMPTY_COOKIE_VALUE,
            ...$cookieSettings
        );

        $this->setAdditionalCookies($cookieSettings);

        $this->cookiesSet = true;
    }

    /**
     * Set additional cookies
     */
    protected function setAdditionalCookies(?array $cookieSettings): void
    {
        // Use extension to set additional cookies if needed
    }

    /**
     * Returns environment key
     *
     * @return string
     */
    public function getCookieEnvironmentKey(): string
    {
        $environmentKeyLogics = Registry::get(EnvironmentKeyLogics::class);
        return $environmentKeyLogics->getEnvironmentKey() ?? self::COOKIE_ENVIRONMENT_KEY;
    }

    /**
     * Returns session challenge token
     *
     * @return string|null
     */
    public function getCookieSessionToken(): ?string
    {
        $sessionCookieLogics = Registry::get(SessionCookieLogics::class);
        return $sessionCookieLogics->getSessionToken();
    }

    /**
     * Returns session id
     *
     * @return string|null
     */
    public function getCookieSessionId():? string
    {
        $sessionCookieLogics = Registry::get(SessionCookieLogics::class);
        return $sessionCookieLogics->getSessionId();
    }

    /**
     * Returns array of html input value replacements
     *
     * @return array
     */
    public function getInputValueReplacements(): array
    {
        return [
            self::COOKIE_SESSION_TOKEN,
            self::COOKIE_SESSION_ID,
        ];
    }

    /**
     * Returns array of mandatory cookies
     *
     * @return array
     */
    public function getMandatoryCookies(): array
    {
        return [
            self::COOKIE_ENVIRONMENT_KEY,
        ];
    }

    /**
     * Returns array of url parameter that should trigger cookie refresh from remote
     *
     * @return array
     */
    public function getRefreshParameter(): array
    {
        return Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_REFRESH_PARAMETER') ?? [];
    }

    /**
     * Checks if call is cacheable
     *
     * @return bool
     */
    public function isCacheableCall(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_ACTIVE_GENERAL')
            && $this->isCachableByController()
            && $this->isCacheableByParameter();
    }

    /**
     * Checks if controller is cacheable
     *
     * @param FrontendController|null $controller
     * @return bool
     */
    public function isCachableByController(?FrontendController $controller = null): bool
    {
        $controller = $controller ?? Registry::getConfig()->getTopActiveView();

        $className = $controller->getClassKey() ?: Registry::getRequest()->getRequestEscapedParameter('cl');

        return method_exists($controller, 'isSmartProxyCachable')
            ? $controller->isSmartProxyCachable()
            : $this->isCachableByControllerClassName($className);
    }

    /**
     * Checks if controller name is cacheable
     *
     * @param $className
     * @return bool
     */
    public function isCachableByControllerClassName($className): bool
    {
        $cachableControllerNames = Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_CACHEABLE_CONTROLLERS') ?? [];
        return in_array($className, $cachableControllerNames);
    }

    /**
     * Checks if there is any parameter that prevents caching
     *
     * @param array|null $parameter
     * @return bool
     */
    public function isCacheableByParameter(?array $parameter = []): bool
    {
        $cacheable = true;
        $request = Registry::getRequest();
        $nonCachableParameters = Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_NON_CACHEABLE_PARAMETERS') ?? [];

        foreach ($nonCachableParameters as $nonCachableParameter) {
            @list($key, $value) = explode('=', $nonCachableParameter);
            $parameterToCheck = empty($parameter) ? $request->getRequestParameter($key) : $parameter[$key];

            if ($parameterToCheck && $value === null
                || $parameterToCheck === $value
            ) {
                $cacheable = false;
                break;
            }
        }

        return $cacheable;
    }

    public function clearHtmlCache(): void
    {
        $config = Registry::getConfig();

        $rundeckJob = oxNew(RundeckJobRunner::class, [
            'rundeckSubdomain' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_SUBDOMAIN'),
            'rundeckJobId' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_JOB_HTML_CLEAR'),
            'authToken' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_AUTHTOKEN'),
        ]);
        $rundeckJob->runJob();
    }

    public function clearCacheByTag(array $tags): void
    {
        $config = Registry::getConfig();

        $rundeckJob = oxNew(RundeckJobRunner::class, [
            'rundeckSubdomain' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_SUBDOMAIN'),
            'rundeckJobId' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_JOB_TAG_CLEAR'),
            'authToken' => $config->getConfigParam('FOUN10_SMART_PROXY_RUNDECK_AUTHTOKEN'),
        ]);

        foreach ($tags as $tag) {
            $rundeckJob->runJob(['SearchString' => $tag]);
        }
    }
}
