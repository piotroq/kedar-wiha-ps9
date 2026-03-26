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
use Przelewy24\Model\Dto\GoogleConfig;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class GoogleType extends AbstractType
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
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::GOOGLE]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'one_click', SwitchType::class, [
                    'label' => $this->translator->trans('Enable Google Pay in store', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'id_merchant', TextType::class, [
                    'label' => $this->translator->trans('Google merchant ID', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                    'help' => $this->translator->trans('Google Merchant ID is your unique store ID used for Google Pay. You can find it in the top right corner of your Google Merchant Center account.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'merchant_name', TextType::class, [
                    'label' => $this->translator->trans('Google merchant name', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                    'help' => $this->translator->trans('Google Merchant Name is the name of your store shown during Google Pay checkout. You can find it in your Google Merchant Center account settings.', [], 'Modules.Przelewy24payment.Form'),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GoogleConfig::class,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
