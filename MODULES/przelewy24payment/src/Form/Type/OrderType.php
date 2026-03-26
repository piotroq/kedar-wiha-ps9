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
use Przelewy24\Model\Dto\OrderConfig;
use Przelewy24\Provider\Choice\OrderIdChoiceProvider;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class OrderType extends AbstractType
{
    private $translator;

    /**
     * @var OrderIdChoiceProvider
     */
    private $orderIdChoiceProvider;

    /**
     * @var RouterInterface
     */
    private $router;
    private $formRenderer;

    public function __construct(
        Translator $translator,
        OrderIdChoiceProvider $orderIdChoiceProvider,
        RouterInterface $router,
        FormRendererInterface $formRenderer
    ) {
        $this->translator = $translator;
        $this->orderIdChoiceProvider = $orderIdChoiceProvider;
        $this->router = $router;
        $this->formRenderer = $formRenderer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::ORDER]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'alter_stock', SwitchType::class, [
                    'label' => $this->translator->trans('Update stock quantity after selecting products for refund', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'accept_in_shop', SwitchType::class, [
                    'label' => $this->translator->trans('P24 terms acceptance in the shop', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Display the Przelewy24 terms acceptance option below the highlighted payment method.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'intro_text', SwitchType::class, [
                    'label' => $this->translator->trans('Display intro text ', [], 'Modules.Przelewy24payment.Form'),
                    'help' => $this->translator->trans('Displays the Przelewy24 logo with information about redirecting to the payment operator and a summary of the order amount.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'order_identification', ChoiceType::class, [
                    'label' => $this->translator->trans('Order id in title ', [], 'Modules.Przelewy24payment.Form'),
                    'choices' => $this->orderIdChoiceProvider->getChoices(),
                    'attr' => [
                        'class' => 'custom-select--md',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderConfig::class,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
