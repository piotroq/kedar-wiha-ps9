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

declare(strict_types=1);

namespace DpdShipping\Controller\ShippingHistory;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Grid\ShippingHistory\ShippingHistoryFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Shop;

class DpdShippingShippingHistoryDetailController extends FrameworkBundleAdminController
{
    private $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function index(ShippingHistoryFilters $filters)
    {
        return $this->render(
            '@Modules/dpdshipping/views/templates/admin/shippingHistory/shipping-history-detail-form.html.twig',
            [
                'layoutTitle' => $this->translator->trans('Shipping history', [], 'Modules.Dpdshipping.Admin'),
                'orderId' => \Tools::getValue('orderId'),
                'shopContext' => Shop::getContext()
            ]
        );
    }
}
