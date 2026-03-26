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
use Przelewy24\Model\Dto\TimeConfig;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class TimeConfigType extends AbstractType
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
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::TIME]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'wait_for_result', SwitchType::class, [
                    'label' => $this->translator->trans('Wait for a result of the transaction ', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Enabling this option makes the customer wait on the Przelewy24 page for payment confirmation after leaving the bank’s site. Choosing "No" redirects the customer immediately back to the store.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'time_limit', IntegerType::class, [
                    'label' => $this->translator->trans('Time limit', [], 'Modules.Przelewy24payment.Form'),
                    'data' => 15,
                    'required' => false,
                    'label_attr' => [
                        'popover' => $this->translator->trans('Time limit in minutes after order placement during which the payment can be completed via the Przelewy24 payment form. Enter 0 for no limit - The order will not be cancelled', [], 'Modules.Przelewy24payment.Form'),
                    ],
                    'attr' => [
                        'class' => 'form-control--sm',
                        'min' => 0,
                        'max' => 99,
                    ],
                    'help' => $this->translator->trans('The time limit to complete the payment after it is initiated is 15 minutes. You can extend or disable it by entering 0.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'time_limit_fast_transfer', IntegerType::class, [
                    'label' => $this->translator->trans('Time limit fast transfer', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                    'label_attr' => [
                        'popover' => $this->translator->trans('Time has to be in hours', [], 'Modules.Przelewy24payment.Form'),
                    ],
                    'attr' => [
                        'class' => 'form-control--sm',
                        'min' => 0,
                        'max' => 99,
                    ],
                    'help' => $this->translator->trans('Time limit for payment confirmation for fast payment methods (in hours)', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'time_limit_long_term', IntegerType::class, [
                    'label' => $this->translator->trans('Time limit long term', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                    'label_attr' => [
                        'popover' => $this->translator->trans('Time has to be in hours', [], 'Modules.Przelewy24payment.Form'),
                    ],
                    'attr' => [
                        'class' => 'form-control--sm',
                        'min' => 0,
                        'max' => 99,
                    ],
                    'help' => $this->translator->trans('Time limit for payment confirmation for methods with longer processing time (in hours)', [], 'Modules.Przelewy24payment.Form'),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TimeConfig::class,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
