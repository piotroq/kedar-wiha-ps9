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
use Przelewy24\Form\Transformer\CertTransformer;
use Przelewy24\Model\Dto\AppleConfig;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AppleType extends AbstractType
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CertTransformer
     */
    private $certTransformer;
    private $formRenderer;

    public function __construct(
        Translator $translator,
        RouterInterface $router,
        CertTransformer $certTransformer,
        FormRendererInterface $formRenderer
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->certTransformer = $certTransformer;
        $this->formRenderer = $formRenderer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::APPLE]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'one_click', SwitchType::class, [
                    'label' => $this->translator->trans('Enable Apple Pay in store', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'id_merchant', TextType::class, [
                    'label' => $this->translator->trans('Apple merchant ID', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                    'help' => $this->translator->trans('Apple Merchant ID is a unique identifier for your store required to set up Apple Pay. You can create and manage it in your Apple Developer Account under Identifiers → Merchant IDs.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'merchant_name', TextType::class, [
                    'label' => $this->translator->trans('Apple merchant name', [], 'Modules.Przelewy24payment.Form'),
                    'attr' => [
                        'class' => 'form-control--sm',
                    ],
                    'help' => $this->translator->trans('Apple Merchant Name is the name of your store shown to customers during Apple Pay checkout. It should be recognizable and match your brand name.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'cert', FileType::class, [
                    'label' => $this->translator->trans('Apple certificate cert', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control--sm',
                        'accept' => '.pem',
                    ],
                    'help' => $this->translator->trans('Apple Pay Certificate (.pem file) is a certificate required for secure communication with Apple Pay. It is generated in your Apple Developer Account and exported in .pem format for payment integration.', [], 'Modules.Przelewy24payment.Form'),
                ]
            )
            ->add(
                'private_key', FileType::class, [
                    'label' => $this->translator->trans('Apple certificate key', [], 'Modules.Przelewy24payment.Form'),
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control--sm',
                        'accept' => '.pem',
                    ],
                    'help' => $this->translator->trans('Apple Certificate Key is the private key needed for Apple Pay. Created together with the certificate in Apple Developer.', [], 'Modules.Przelewy24payment.Form'),
                ]
            );

        $builder->get('cert')->addModelTransformer($this->certTransformer);
        $builder->get('private_key')->addModelTransformer($this->certTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AppleConfig::class,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->formRenderer->setTheme($view, '@Modules/przelewy24payment/views/templates/admin/form_theme/form_theme.html.twig');
    }
}
