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

namespace Przelewy24\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethod
{
    private $specialName;

    private $name;

    private $id;

    private $group;

    private $subgroup;

    private $status;

    private $imgUrl;

    private $mobileImgUrl;

    private $mobile;

    private $frontUrl;

    private $type;

    private $availabilityHours = [];

    public function getAvailabilityHours(): array
    {
        return $this->availabilityHours;
    }

    public function setAvailabilityHours(array $availabilityHours): PaymentMethod
    {
        $this->availabilityHours = $availabilityHours;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     *
     * @return PaymentMethod
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return PaymentMethod
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImgUrl()
    {
        return $this->imgUrl;
    }

    /**
     * @param mixed $imgUrl
     *
     * @return PaymentMethod
     */
    public function setImgUrl($imgUrl)
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     *
     * @return PaymentMethod
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobileImgUrl()
    {
        return $this->mobileImgUrl;
    }

    /**
     * @param mixed $mobileImgUrl
     *
     * @return PaymentMethod
     */
    public function setMobileImgUrl($mobileImgUrl)
    {
        $this->mobileImgUrl = $mobileImgUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return PaymentMethod
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpecialName()
    {
        return !empty($this->specialName)
            ? $this->specialName
            : $this->name;
    }

    /**
     * @param mixed $specialName
     *
     * @return PaymentMethod
     */
    public function setSpecialName($specialName)
    {
        $this->specialName = $specialName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return PaymentMethod
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubgroup()
    {
        return $this->subgroup;
    }

    /**
     * @param mixed $subgroup
     *
     * @return PaymentMethod
     */
    public function setSubgroup($subgroup)
    {
        $this->subgroup = $subgroup;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrontUrl()
    {
        return $this->frontUrl;
    }

    /**
     * @param mixed $frontUrl
     *
     * @return PaymentMethod
     */
    public function setFrontUrl($frontUrl)
    {
        $this->frontUrl = $frontUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return PaymentMethod
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
