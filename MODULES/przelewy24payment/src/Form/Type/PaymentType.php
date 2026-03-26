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

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Przelewy24\Configuration\Enum\FormTypeEnum;
use Przelewy24\Model\Dto\PaymentConfig;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class PaymentType extends AbstractType
{
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;
    private $formRenderer;

    public function __construct(
        Translator $translator,
        RouterInterface $router,
        FormRendererInterface $formRenderer
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->formRenderer = $formRenderer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::PAYMENT]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'payment_method_in_main', SwitchType::class, [
                    'label' => $this->translator->trans('Show payment methods in main payment method', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'js-przelewy24-payment-switch',
                        'data-target' => '.payment_method_in_main_list',
                    ],
                    'help' => $this->translator->trans('Move the methods you want to highlight from the window below and set their order within the main P24 payment method.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'payment_method_in_main_list', PaymentMethodDragAndDropType::class, [
                    'label' => $this->translator->trans('Payment method in main list', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                ]
            )
            ->add(
                'payment_method_separate', SwitchType::class, [
                    'label' => $this->translator->trans('Show payment methods separated', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'js-przelewy24-payment-switch',
                        'data-target' => '.payment_method_separate_list',
                    ],
                    'help' => $this->translator->trans('Move the methods you want to highlight from the window below and arrange them as separate payment methods.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'payment_method_separate_list', PaymentMethodDragAndDropType::class, [
                    'label' => $this->translator->trans('Payment method in separate list', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                ]
            )
            ->add(
                'payment_method_name_list', CollectionType::class, [
                    'entry_type' => PaymentMethodType::class,
                    'entry_options' => ['label' => false],
                    'label' => $this->translator->trans('Custom payment method description', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'przelewy24-payments__list',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        //        $resolver->setRequired('payment_method_name_list');
        //        $resolver->setRequired('payment_method_main_list');
        //        $resolver->setRequired('payment_method_separate_list');

        //        $resolver->setAllowedTypes('payment_method_name_list', PaymentMethodCollection::class);
        //        $resolver->setAllowedTypes('payment_method_main_list', PaymentMethodCollection::class);
        //        $resolver->setAllowedTypes('payment_method_separate_list', PaymentMethodCollection::class);

        $resolver->setDefaults([
            'data_class' => PaymentConfig::class,
            //            'payment_method_name_list' => new PaymentMethodCollection(),
            //            'payment_method_main_list' => new PaymentMethodCollection(),
            //            'payment_method_separate_list' => new PaymentMethodCollection(),
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
