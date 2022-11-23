<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Core;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Core\Registry;

/**
 * Extension for OXID class Header
 */
class Header extends Header_parent
{
    /**
     * foun10: Set cookies needed on smartproxy
     */
    public function sendHeader()
    {
        $smartProxy = Registry::get(SmartProxy::class);

        // Set cookies needed for smartproxy usage
        $smartProxy->setCookies();

        // Set header to let smartproxy know there must be no cache for this call
        if (!$smartProxy->isCacheableCall()) {
            $this->setHeader('x-sc-sp-cache: no');
        } else {
            $this->setHeader('x-sc-sp-cache: yes');
        }

        parent::sendHeader();
    }
}
