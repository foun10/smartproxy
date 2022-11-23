<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Controller;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Header;
use OxidEsales\Eshop\Core\Output;
use OxidEsales\Eshop\Core\Registry;

/**
 * Controller to set up cookies and cookie values
 */
class EnvironmentSetter extends FrontendController
{
    public function render()
    {
        $outputManager = oxNew(Output::class);
        $outputManager->setCharset($this->getCharSet());
        $outputManager->setOutputFormat(Output::OUTPUT_FORMAT_JSON);
        $outputManager->sendHeaders();

        $header = Registry::get(Header::class);
        $header->sendHeader();

        $smartProxy = Registry::get(SmartProxy::class);

        // Send cookie values
        print \json_encode([
            SmartProxy::COOKIE_SESSION_ID => $smartProxy->getCookieSessionId(),
            SmartProxy::COOKIE_SESSION_TOKEN => $smartProxy->getCookieSessionToken(),
        ]);
        exit;
    }
}
