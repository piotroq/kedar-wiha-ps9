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

declare(strict_types=1);

namespace Przelewy24\Form\Type;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\FormTypeEnum;
use Przelewy24\Model\Dto\ExtraChargeConfig;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ExtraChargeType extends AbstractType
{
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        Translator $translator,
        RouterInterface $router
    ) {
        $this->translator = $translator;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::EXTRA_CHARGE]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'extra_charge_amount', MoneyType::class, [
                    'label' => $this->translator->trans('Increase payment (amount)', [], 'Modules.Przelewy24payment.Form'),
                    'scale' => 2,
                    'required' => false,
                    'constraints' => [
                        new Assert\Type(['type' => 'float']),
                    ],
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                ]
            )
            ->add(
                'extra_charge_percent', PercentType::class, [
                    'label' => $this->translator->trans('Increase payment (percent)', [], 'Modules.Przelewy24payment.Form'),
                    'type' => 'integer',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                ]
            )
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                if (empty($data)) {
                    return;
                }
                $idAccount = $data->getIdAccount();
                $model = new Przlewy24AccountModel($idAccount);
                $event->getForm()->add('extra_charge_amount', MoneyType::class, [
                    'label' => $this->translator->trans('Increase payment (amount)', [], 'Modules.Przelewy24payment.Form'),
                    'scale' => 2,
                    'currency' => $model->getIsoCurrency(),
                    'required' => false,
                    'constraints' => [
                        new Assert\Type(['type' => 'float']),
                    ],
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExtraChargeConfig::class,
        ]);
    }
}
