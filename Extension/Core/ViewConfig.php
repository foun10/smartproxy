<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Core;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Core\Registry;

/**
 * Extension for OXID class ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    /**
     * Checks if smart proxy logics should be applied
     *
     * @return bool
     */
    public function isSmartProxyActive()
    {
        $smartProxy = Registry::get(SmartProxy::class);
        return $smartProxy->isActive();
    }

    /**
     * Returns array of html input value replacements
     *
     * @return array
     */
    public function getInputValueReplacements()
    {
        $smartProxy = Registry::get(SmartProxy::class);
        return $smartProxy->getInputValueReplacements();
    }

    /**
     * Returns array of mandatory cookies
     *
     * @return array
     */
    public function getMandatoryCookies()
    {
        $smartProxy = Registry::get(SmartProxy::class);
        return $smartProxy->getMandatoryCookies();
    }

    /**
     * Returns array of url parameter that should trigger cookie refresh from remote
     *
     * @return array
     */
    public function getRefreshParameter()
    {
        $smartProxy = Registry::get(SmartProxy::class);
        return $smartProxy->getRefreshParameter();
    }

    /**
     * Returns if JS debug should be used
     *
     * @return bool
     */
    public function getJsDebug()
    {
        return (bool) Registry::getConfig()->getConfigParam('FOUN10_SMART_PROXY_JS_DEBUG');
    }
}
