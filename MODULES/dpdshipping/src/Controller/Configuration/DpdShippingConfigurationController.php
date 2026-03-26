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

use DpdShipping\Domain\Configuration\Connection\Query\GetConnectionList;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Shop;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DpdShippingConfigurationController extends FrameworkBundleAdminController
{
    private $queryBus;
    private $textFormDataHandler;
    private $translator;

    public function __construct($queryBus, $textFormDataHandler, $translator)
    {
        $this->queryBus = $queryBus;
        $this->textFormDataHandler = $textFormDataHandler;
        $this->translator = $translator;
    }

    public function index(Request $request): Response
    {
        $form = $this->textFormDataHandler->getForm();
        $form->add('empikDpdApiForStoreDelivery', ChoiceType::class, [
            'choices' => $this->getChoices(),
            'choice_loader' => null,
            'label' => $this->translator->trans('API account for Empik Store delivery', [], 'Modules.Dpdshipping.AdminConfiguration'),
            'choice_translation_domain' => false,
            'placeholder' => '---',
            'required' => false
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->textFormDataHandler->save($form->getData());
            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
                return $this->redirectToRoute('dpdshipping_configuration_form');
            }
            $this->flashErrors($errors);
        }
        return $this->render('@Modules/dpdshipping/views/templates/admin/configuration/configuration-form.html.twig', [
            'form' => $form->createView(),
            'shopContext' => Shop::getContext(),
        ]);
    }

    public function getChoices(): array
    {
        $connections = $this->queryBus->handle(new GetConnectionList(Shop::getContextListShopID()));

        $choices = [];
        foreach ($connections as $connection) {
            $choices[$connection->getName() . ', MASTERFID: ' . $connection->getMasterFid()] = (string)$connection->getId();
        }
        return $choices;
    }
}
