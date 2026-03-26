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
use Przelewy24\Model\Dto\StateConfig;
use Przelewy24\Provider\Choice\StatusChoiceProvider;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class StateType extends AbstractType
{
    private $translator;

    /**
     * @var StatusChoiceProvider
     */
    private $statusChoiceProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        Translator $translator,
        StatusChoiceProvider $statusChoiceProvider,
        RouterInterface $router
    ) {
        $this->translator = $translator;
        $this->statusChoiceProvider = $statusChoiceProvider;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('przelewy24.saveForm', ['type' => FormTypeEnum::STATE]))
            ->add(
                'id_account', HiddenType::class, []
            )
            ->add(
                'id_state_before_payment', ChoiceType::class, [
                    'label' => $this->translator->trans('Status before completing payment', [], 'Modules.Przelewy24payment.Form'),
                    'choices' => $this->statusChoiceProvider->getChoices(),
                    'attr' => [
                        'class' => 'custom-select--lg',
                    ],
                ]
            )
            ->add(
                'id_state_after_payment', ChoiceType::class, [
                    'label' => $this->translator->trans('Status after completing payment', [], 'Modules.Przelewy24payment.Form'),
                    'choices' => $this->statusChoiceProvider->getChoices(),
                    'attr' => [
                        'class' => 'custom-select--lg',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StateConfig::class,
        ]);
    }
}
