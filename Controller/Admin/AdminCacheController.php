<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Controller\Admin;

use foun10\SmartProxy\Core\SmartProxy;
use foun10\SmartProxy\Core\SmartProxyCacheTags;
use OxidEsales\Eshop\Core\Cache\DynamicContent\ContentCache;
use OxidEsales\Eshop\Core\Cache\Generic\Cache;
use OxidEsales\Eshop\Core\Registry;

class AdminCacheController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_sThisTemplate = 'foun10/SmartProxy/admin/cache.tpl';

    /**
     * Return available cache tags to clear for
     *
     * @return array
     */
    public function getAllCacheTags(): array
    {
        $cacheTags = SmartProxyCacheTags::CACHE_TAGS;
        $cacheTags[] = 'start';

        return $cacheTags;
    }

    /**
     * Function to clear smartproxy cache by tags
     */
    public function clearCacheByTags()
    {
        $request = Registry::getRequest();
        $smartProxy = Registry::get(SmartProxy::class);

        $clear = $request->getRequestEscapedParameter('clear') ?: [];
        $clearDefinedTags = $request->getRequestEscapedParameter('clearByDefinedTags') ?: '';

        if (in_array(SmartProxyCacheTags::CACHE_TAG_HTML, $clear)) {
            $this->clearAllCaches();
        } else {
            $tags = array_merge($clear, explode(';', $clearDefinedTags));
            $tags = array_map('trim', $tags);
            $tags = array_filter($tags);

            if (count($tags) > 0) {
                $smartProxy->clearCacheByTag($tags);

                // Clear OXID content cache, too
                $this->clearOxidContentCache();

                $this->_aViewData['success'] = true;
            }
        }
    }

    /**
     * Function to clear all known caches
     */
    public function clearAllCaches()
    {
        // Clear Smartproxy cache
        $smartProxy = Registry::get(SmartProxy::class);
        $smartProxy->clearHtmlCache();

        $this->clearOxidCaches();

        $this->_aViewData['success'] = true;
    }

    /**
     * Function to clear all OXID caches
     */
    protected function clearOxidCaches()
    {
        $this->clearOxidContentCache();
        $this->clearOxidBackendCache();
    }

    /**
     * Function to clear OXID dynamic content cache
     */
    protected function clearOxidContentCache()
    {
        // Clear OXID content cache + Clear OXID file cache
        $cache = oxNew(ContentCache::class);
        $cache->reset(true);
    }

    /**
     * Function to clear OXID backend cache
     */
    protected function clearOxidBackendCache()
    {
        // Clear OXID backend cache
        $backendCache = Registry::get(Cache::class);
        $backendCache->flush();
    }
}
