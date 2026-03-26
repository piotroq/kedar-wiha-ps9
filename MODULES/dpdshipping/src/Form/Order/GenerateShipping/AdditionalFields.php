<?php
/**
 * Copyright 2024 DPD Polska Sp. z o.o.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    DPD Polska Sp. z o.o.
 * @copyright 2024 DPD Polska Sp. z o.o.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 */

namespace DpdShipping\Form\Order\GenerateShipping;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdditionalFields
{
    public const ORDER_NUMBER = 'ORDER_NUMBER';
    public const ORDER_NUMBER_EMPIK = 'ORDER_NUMBER_EMPIK';
    public const ORDER_ID = 'ORDER_ID';
    public const INVOICE_NUMBER = 'INVOICE_NUMBER';
    public const PRODUCT_INDEX = 'PRODUCT_INDEX';
    public const PRODUCT_NAME = 'PRODUCT_NAME';
    public const STATIC_VALUE = 'STATIC_VALUE';
    public const STATIC_VALUE_ONLY_FOR_EMPIK = 'STATIC_VALUE_ONLY_FOR_EMPIK';
}
