<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Extension\Core\Cache\DynamicContent;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Core\Registry;

class ContentCache extends ContentCache_parent
{
    /**
     * Renders dynamic contents within cached contents.
     *
     * @param string $output static contents
     *
     * @return string
     */
    public function processCache($output)
    {
        $smartProxy = Registry::get(SmartProxy::class);

        if ($smartProxy->isActive()) {
            // Replace dynamic content cache placeholders with smartproxy placeholders
            $output = str_replace([
                static::SESSION_ID_PLACEHOLDER,
                static::SESSION_STOKEN_PLACEHOLDER,
                static::SESSION_FULL_ID_PLACEHOLDER,
                static::SESSION_FULL_ID_AMP_PLACEHOLDER,
                static::SESSION_FULL_ID_QUE_PLACEHOLDER
            ], [
                sprintf(SmartProxy::PLACEHOLDER_ENCLOSURE, SmartProxy::COOKIE_SESSION_ID),
                sprintf(SmartProxy::PLACEHOLDER_ENCLOSURE, SmartProxy::COOKIE_SESSION_TOKEN),
                '',
                '',
                ''
            ], $output);
        }

        return parent::processCache($output);
    }
}
