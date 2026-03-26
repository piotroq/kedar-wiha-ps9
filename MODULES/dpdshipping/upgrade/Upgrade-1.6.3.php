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

function upgrade_module_1_6_3($module)
{
    $db = Db::getInstance();
    $config  = _DB_PREFIX_ . 'dpdshipping_configuration';
    $conn    = _DB_PREFIX_ . 'dpdshipping_connection';
    $payer   = _DB_PREFIX_ . 'dpdshipping_payer';

    $queries = [];

    $queries[] = 'INSERT IGNORE INTO `'.$conn.'` (`id_shop`, `name`, `login`, `password`, `master_fid`, `environment`, `is_default`)
    SELECT
        s.id_shop,
        "DPD API" AS name,
        MAX(CASE WHEN s.name = "DPD_API_LOGIN"       THEN s.value END) AS login,
        MAX(CASE WHEN s.name = "DPD_API_PASSWORD"    THEN s.value END) AS password,
        MAX(CASE WHEN s.name = "DPD_API_MASTER_FID"  THEN s.value END) AS master_fid,
        MAX(CASE WHEN s.name = "DPD_API_ENVIRONMENT" THEN s.value END) AS environment,
        1 AS is_default
    FROM `'.$config.'` s
    WHERE s.name IN ("DPD_API_LOGIN","DPD_API_PASSWORD","DPD_API_MASTER_FID","DPD_API_ENVIRONMENT")
    GROUP BY s.id_shop';

    $queries[] = 'UPDATE `'.$payer.'` p
        INNER JOIN `'.$conn.'` c 
            ON c.id_shop = p.id_shop AND c.name = "DPD API"
        SET p.id_connection = c.id';

    $queries[] = 'DELETE c1 FROM `'.$config.'` c1
        WHERE c1.name IN ("DPD_API_LOGIN","DPD_API_PASSWORD","DPD_API_MASTER_FID","DPD_API_ENVIRONMENT")
          AND EXISTS (
              SELECT 1
              FROM `'.$conn.'` cx
              WHERE cx.id_shop = c1.id_shop AND cx.name = "DPD API"
          )';

    foreach ($queries as $q) {
        if (!$db->execute($q)) {
            if (strpos($db->getMsgError(), 'Duplicate') === false
                && strpos($db->getMsgError(), 'already exists') === false) {
                return false;
            }
        }
    }

    return true;
}

