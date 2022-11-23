<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Controller;

use foun10\SmartProxy\Core\SmartProxy;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Header;
use OxidEsales\Eshop\Core\Output;
use OxidEsales\Eshop\Core\Registry;

/**
 * Controller to return dynamic tracking data
 */
class TrackingData extends FrontendController
{
    public function render()
    {
        $identifier = Registry::getRequest()->getRequestEscapedParameter('identifier') ?? '';

        $header = Registry::get(Header::class);
        $header->sendHeader();

        // Send tracking data as json string
        print $this->getTrackingData($identifier);
        exit;
    }

    protected function getTrackingData(string $identifier): string
    {
        $return = null;

        if ($identifier === 'trbo_current_basket') {
            $return = ['aaaa' => 'bbb'];
        }

        return \json_encode($return) ?: '';
    }
}
