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

namespace Przelewy24\Dto\Javascript;

if (!defined('_PS_VERSION_')) {
    exit;
}
#[\AllowDynamicProperties]
class Config implements \JsonSerializable
{
    /* @var ?int $merchantId */
    private $merchantId;

    /* @var ?string $sessionId */
    private $sessionId;

    /* @var ?string $sign */
    private $sign;

    /* @var ?string $mode */
    private $mode;

    /* @var ?string $lang */
    private $lang;

    /* @var ?Options $options */
    private $options;

    public function __construct()
    {
        $this->options = new Options();
        $this->options->size = ['height' => '515px'];
        $this->options->styles = [
            'border' => [
                'form' => [
                    'color' => '#dbe6e9',
                    'width' => '1px',
                    'radius' => '6px',
                ],
            ],
        ];
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): Config
    {
        $this->lang = $lang;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function setMerchantId(?int $merchantId): Config
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): Config
    {
        $this->mode = $mode;

        return $this;
    }

    public function getOptions(): ?Options
    {
        return $this->options;
    }

    public function setOptions(?Options $options): Config
    {
        $this->options = $options;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): Config
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(?string $sign): Config
    {
        $this->sign = $sign;

        return $this;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
