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

namespace DpdShipping\Grid\Configuration\Connection\Definition\Factory;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Grid\DataColumnFactory;
use PrestaShop\PrestaShop\Core\Grid\Action\ModalOptions;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;


class ConnectionGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    public const GRID_ID = 'dpdshipping_connection';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('DPD Poland API Connection', [], 'Modules.Dpdshipping.AdminConnection');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (DataColumnFactory::create('id_shop'))
                    ->setName($this->trans('Shop', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'id_shop',
                    ])
            )
            ->add(
                (DataColumnFactory::create('name'))
                    ->setName($this->trans('Name', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'name',
                    ])
            )
            ->add(
                (DataColumnFactory::create('login'))
                    ->setName($this->trans('Login', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'login',
                    ])
            )
            ->add(
                (DataColumnFactory::create('master_fid'))
                    ->setName($this->trans('Masterfid', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'master_fid'
                    ])
            )
            ->add(
                (DataColumnFactory::create('environment'))
                    ->setName($this->trans('Environment', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'environment'
                    ])
            )
            ->add(
                (DataColumnFactory::create('is_default'))
                    ->setName($this->trans('Default', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'field' => 'is_default',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Modules.Dpdshipping.AdminConnection'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add((new SubmitRowAction('edit'))
                                ->setName($this->trans('Edit', [], 'Modules.Dpdshipping.AdminConnection'))
                                ->setIcon('edit')
                                ->setOptions([
                                    'method' => 'POST',
                                    'route' => 'dpdshipping_connection_edit_form',
                                    'route_param_name' => 'connectionId',
                                    'route_param_field' => 'id',
                                ]))
                            ->add((new SubmitRowAction('delete'))
                                ->setName($this->trans('Delete', [], 'Modules.Dpdshipping.AdminConnection'))
                                ->setIcon('delete')
                                ->setOptions([
                                    'method' => 'DELETE',
                                    'route' => 'dpdshipping_connection_delete_form',
                                    'route_param_name' => 'connectionId',
                                    'route_param_field' => 'id',
                                    'modal_options' => new ModalOptions([
                                        'title' => $this->trans('Remove selected row?', [], 'Modules.Dpdshipping.AdminConnection'),
                                        'confirm_button_label' => $this->trans('Delete', [], 'Modules.Dpdshipping.AdminConnection'),
                                        'confirm_button_class' => 'btn-secondary',
                                        'close_button_label' => $this->trans('Close', [], 'Modules.Dpdshipping.AdminConnection'),
                                    ]),
                                ])),
                    ])
            );
    }
}
