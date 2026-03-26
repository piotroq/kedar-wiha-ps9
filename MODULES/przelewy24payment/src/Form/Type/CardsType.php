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
use Przelewy24\Model\Dto\CardsConfig;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class CardsType extends AbstractType
{
    /**
     * @var Translator
     */
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
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::CARDS]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'payment_in_store', SwitchType::class, [
                    'label' => $this->translator->trans('Card payment inside the store', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Enables fast and secure card payments via Przelewy24 without leaving the store', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'one_click_card', SwitchType::class, [
                    'label' => $this->translator->trans('One Click Card Payment', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Enables quick payments with a previously saved card via Przelewy24 - no need to re-enter details. Requires the "Card payment inside the store" option to be active', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'click_to_pay', SwitchType::class, [
                    'label' => $this->translator->trans('Click to Pay enabled', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Allows registered users to pay through Click to Pay, if the "One Click Card Payment" option is not enabled. Requires the "Card payment inside the store" option to be active', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'click_to_pay_guest', SwitchType::class, [
                    'label' => $this->translator->trans('Click to Pay active for guests', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Allows unregistered users to make quick card payments through Click to Pay without having to create an account. Requires the "Click to Pay enabled" option to be active', [], 'Modules.Przelewy24payment.Form'),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CardsConfig::class,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
