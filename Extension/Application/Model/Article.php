<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Application\Model;

use foun10\SmartProxy\Core\SmartProxy;
use foun10\SmartProxy\Core\SmartProxyCacheTags;

class Article extends Article_parent
{
    protected function _resetCache($productId = null)
    {
        parent::_resetCache($productId);

        if ($this->clearSmartProxyCache()) {
            $tags = [];
            $smartProxy = oxNew(SmartProxy::class);
            $smartProxyCacheTags = oxNew(SmartProxyCacheTags::class);

            foreach ($this->getCategoryIds() as $categoryId) {
                $tags[] = $smartProxyCacheTags::CACHE_TAG_ALIST . '-' . $categoryId;
            }

            $tags = array_merge($tags, $smartProxyCacheTags->getCacheTagsForDetailsProduct($this));

            $smartProxy->clearCacheByTag($tags);
        }
    }

    /**
     * Extend to prevent smartproxy cache clear
     *
     * @return bool
     */
    protected function clearSmartProxyCache(): bool
    {
        return true;
    }
}
