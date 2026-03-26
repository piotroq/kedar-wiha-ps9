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

use DpdShipping\Install\DatabaseInstaller;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_0($module)
{
    $db = Db::getInstance();
    $createConnectionTable = $db->execute(DatabaseInstaller::getDpdshippingConnectionCreateTable());

    $alterPayer = $db->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'dpdshipping_payer ADD COLUMN id_connection int(11) NULL AFTER id_shop;');

    return $createConnectionTable && $alterPayer;
}