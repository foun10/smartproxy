<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Core;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Core\Registry;

/**
 * Extension for OXID class Session
 */
class Session extends Session_parent
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function hiddenSid()
    {
        $smartProxy = Registry::get(SmartProxy::class);

        if ($smartProxy->isActive()) {
            return '<input type="hidden" name="stoken" value="' . sprintf(SmartProxy::PLACEHOLDER_ENCLOSURE, SmartProxy::COOKIE_SESSION_TOKEN) . '" />';
        }

        return parent::hiddenSid();
    }

    /**
     * Return false if smartproxy logics are active
     *
     * @param string $url
     *
     * @return bool
     */
    public function isSidNeeded($url = null)
    {
        $smartProxy = Registry::get(SmartProxy::class);

        if ($smartProxy->isActive()) {
            return false;
        }

        return parent::isSidNeeded($url);
    }
}
