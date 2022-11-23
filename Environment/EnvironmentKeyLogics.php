<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Environment;

use OxidEsales\Eshop\Core\Registry;

class EnvironmentKeyLogics
{
    /**
     * Returns environment key for current user/session
     *
     * @return string|null
     */
    public function getEnvironmentKey(): string
    {
        $environmentKey = '0';

        $values = $this->getEnvironmentValues();

        if (!empty($values)) {
            $environmentKey = $this->createHashForValues($values);
        }

        return $environmentKey;
    }

    /**
     * Returns array of collected environment values
     *
     * @return array
     */
    protected function getEnvironmentValues(): array
    {
        $session = Registry::getSession();

        $values['sorting'] = serialize($session->getVariable('aSorting'));
        $values['loggedIn'] = (bool) $session->getVariable('usr');
        $values['productsPerPage'] = $session->getVariable('_artperpage');
        $values['currency'] = Registry::getConfig()->getShopCurrency();
        $values['language'] = Registry::getLang()->getBaseLanguage();
        $values['shop'] = Registry::getConfig()->getShopId();

        return $this->customizeEnvironmentValues($values);
    }

    /**
     * Returns a hash for given value array
     *
     * @param array $values
     * @return string
     */
    protected function createHashForValues(array $values): string
    {
        ksort($values);
        return md5(implode('|', $values));
    }

    /**
     * Use this function to customize environment key - project specific
     *
     * @param array $values
     * @return array
     */
    protected function customizeEnvironmentValues(array $values): array
    {
        return $values;
    }
}
