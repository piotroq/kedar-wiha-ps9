<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Provider\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Helper\Style\StyleHelper;
use Przelewy24\Model\Dto\AppleConfig;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\Interfaces\ConfigurationProviderInterface;
use Przelewy24\Security\Encryptor\Encryptor;

class AppleConfigurationProvider extends AbstractConfigurationProvider implements ConfigurationProviderInterface
{
    public const CERTS = ['cert', 'private_key'];

    protected function getType(): string
    {
        return 'apple';
    }

    protected function getObject(): object
    {
        return new AppleConfig();
    }

    public function getConfiguration(Przlewy24AccountModel $model, $fillCerts = false)
    {
        $config = $model->getTypeConfig($this->getType());
        $object = $this->getObject();
        $object->setIdAccount($model->id);
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                $seter = StyleHelper::seterForUnderscoreField($key);
                if (is_callable([$object, $seter])) {
                    $value = $this->_isCert($key) ? $this->_getCertContent($value, $fillCerts) : $value;
                    $object->{$seter}($value);
                }
            }
        }

        return $object;
    }

    private function _isCert($key)
    {
        return in_array($key, self::CERTS);
    }

    private function _getCertContent($value, $fillCerts)
    {
        if (!$fillCerts || empty($value)) {
            return null;
        }
        try {
            $encryptor = new Encryptor();

            return $encryptor->decrypt($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
