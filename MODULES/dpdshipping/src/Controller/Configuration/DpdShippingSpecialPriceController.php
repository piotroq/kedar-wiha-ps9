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

namespace DpdShipping\Controller\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Config\Config;
use DpdShipping\Domain\Configuration\SpecialPrice\Command\AddSpecialPriceCommand;
use DpdShipping\Domain\Configuration\SpecialPrice\Query\GetSpecialPriceList;
use DpdShipping\Util\ValidateUserUtil;
use Media;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Shop;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DpdShippingSpecialPriceController extends FrameworkBundleAdminController
{
    private $textFormDataHandler;
    private $router;
    private $translator;

    public function __construct($textFormDataHandler, $router, $translator)
    {
        $this->textFormDataHandler = $textFormDataHandler;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function index(Request $request): Response
    {
        $textForm = $this->textFormDataHandler->getForm();
        $textForm->handleRequest($request);

        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $errors = $this->textFormDataHandler->save($textForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));

                return $this->redirectToRoute('dpdshipping_special_price_form');
            }

            $this->flashErrors($errors);
        }

        Media::addJsDef([
            'dpdshipping_special_price_export_ajax_url' => $this->router->generate('dpdshipping_special_price_export_action'),
            'dpdshipping_special_price_import_ajax_url' => $this->router->generate('dpdshipping_special_price_import_action'),
            'dpdshipping_token' => sha1(_COOKIE_KEY_ . 'dpdshipping')
        ]);

        return $this->render('@Modules/dpdshipping/views/templates/admin/configuration/special-price-form.html.twig', [
            'form' => $textForm->createView(),
            'shopContext' => Shop::getContext()
        ]);
    }

    public function exportAction(Request $request): Response
    {
        if (!ValidateUserUtil::validateEmployeeSessionDpdShippingToken($request))
            return new JsonResponse(['success' => false, 'data' => 'INVALID TOKEN']);

        $specialPriceList = $this->getQueryBus()->handle(new GetSpecialPriceList());
        $list = [];
        foreach ($specialPriceList as $item) {
            $array = [];
            $array['isoCountry'] = $item->getIsoCountry();
            $array['priceFrom'] = $item->getPriceFrom();
            $array['priceTo'] = $item->getPriceTo();
            $array['weightFrom'] = $item->getWeightFrom();
            $array['weightTo'] = $item->getWeightTo();
            $array['parcelPrice'] = $item->getParcelPrice();
            $array['carrierType'] = $item->getCarrierType();
            $array['codPrice'] = $item->getCodPrice();

            $list[] = $array;
        }

        $csvData = "isoCountry;priceFrom;priceTo;weightFrom;weightTo;parcelPrice;carrierType;codPrice\n";
        foreach ($list as $row) {
            $csvData .= implode(';', $row) . "\n";
        }

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="export-dpd-ceny-specjalne-prestashop.csv"');

        return $response;
    }


    public function importAction(Request $request): JsonResponse
    {
        if (!ValidateUserUtil::validateEmployeeSessionDpdShippingToken($request))
            return new JsonResponse(['success' => false, 'data' => 'INVALID TOKEN']);

        $file = $request->files->get('csvFile');

        if ($file && $file->isValid()) {
            $csvData = file($file->getRealPath());
            $array = [];
            foreach ($csvData as $index => $line) {
                if ($index === 0) continue;

                $columns = str_getcsv($line, ';');

                $specialPriceRow = [
                    'isoCountry' => $columns[0] ?? null,
                    'priceFrom' => (float)($columns[1] ?? 0),
                    'priceTo' => (float)($columns[2] ?? 0),
                    'weightFrom' => (float)($columns[3] ?? 0),
                    'weightTo' => (float)($columns[4] ?? 0),
                    'parcelPrice' => (float)($columns[5] ?? 0),
                    'carrierType' => $this->getCarrierType($columns[6] ?? null),
                    'codPrice' => (float)($columns[7] ?? 0),
                ];
                $array[] = $specialPriceRow;
            }

            foreach (Shop::getContextListShopID() as $idShop) {
                $this->getCommandBus()->handle(new AddSpecialPriceCommand($array, $idShop));
            }

            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

            return new JsonResponse(['success' => true]);

        }
        return new JsonResponse(['success' => false]);
    }

    private function getCarrierType($param)
    {
        if ($param == "1" || $param == "3")
            return Config::DPD_STANDARD;
        if ($param == "2")
            return Config::DPD_STANDARD_COD;
        if ($param == "4")
            return Config::DPD_PICKUP;
        if ($param == "5")
            return Config::DPD_PICKUP_COD;
        if ($param == "6")
            return Config::DPD_SWIP_BOX;

        return $param;
    }
}
