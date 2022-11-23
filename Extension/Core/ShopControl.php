<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Core;

use foun10\SmartProxy\Core\SmartProxy;
use foun10\SmartProxy\Core\SmartProxyCacheTags;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class ShopControl extends ShopControl_parent
{
    /**
     * @param FrontendController $controller
     * @return void
     */
    protected function sendAdditionalHeaders($controller)
    {
        parent::sendAdditionalHeaders($controller);

        $smartProxy = Registry::get(SmartProxy::class);

        // Set cookies needed for smartproxy usage
        $smartProxy->setCookies();

        if (!$smartProxy->isCacheableCall()) {
            // Set header to let smartproxy know there must be no cache for this call
            Registry::getUtils()->setHeader('x-sc-sp-cache: no');
        } else {
            Registry::getUtils()->setHeader('x-sc-sp-cache: yes');

            // Add tags for cacheable calls
            $cacheTags = Registry::get(SmartProxyCacheTags::class);
            $tags = $cacheTags->getCacheTagsForController($controller);

            Registry::getUtils()->setHeader('x-cache-tags: ' . implode(',', $tags));
        }
    }
}
