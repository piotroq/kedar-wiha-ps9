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

namespace Przelewy24\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\AccountNotFoundApiException;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\ConnectionFailedApiException;
use Przelewy24\Configuration\Enum\FormTypeEnum;
use Przelewy24\Event\Adapter\EventDispatcher;
use Przelewy24\Event\SaveConfigEvent;
use Przelewy24\Factory\Form\FormTabFactory;
use Przelewy24\Factory\Tabs\TabsFactory;
use Przelewy24\Form\Type\AccountType;
use Przelewy24\Form\Type\CredentialsType;
use Przelewy24\Migrator\ConfigurationMigrator;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\CredentialsConfigurationProvider;
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends FrameworkBundleAdminController
{
    /**
     * @var Translator
     */
    private $translator;

    private $oldModuleInstalled;

    public function __construct(Translator $translator, bool $oldModuleInstalled)
    {
        $this->translator = $translator;
        $this->oldModuleInstalled = $oldModuleInstalled;
    }

    public function indexAction(
        $type,
        FormFactoryInterface $formFactory,
        TabsFactory $tabsFactory,
        Przelewy24Config $przelewy24Config,
        ConnectionFactory $connectionFactory
    ) {
        Przlewy24AccountModel::fillAccount();
        $model = Przlewy24AccountModel::getFirstAccountByContext();
        $przelewy24Config->setAccount($model, true, false, true);
        $tabs = $tabsFactory->factory($przelewy24Config);
        $tabs->getTabs()->getKey(FormTypeEnum::getIdByType($type))->setActive();
        $formBuilder = $formFactory->createBuilder(AccountType::class, ['id_account' => $model->id]);
        $form = $formBuilder->getForm();
        try {
            $connectionFactory->factory($model);
        } catch (AccountNotFoundApiException $e) {
            $form->addError(new FormError($this->translator->trans('Not configured credentials', [], 'Modules.Przelewy24payment.Form')));
        } catch (ConnectionFailedApiException $e) {
            $form->addError(new FormError($this->translator->trans('Credentials incorrect', [], 'Modules.Przelewy24payment.Form')));
        }

        return $this->render('@Modules/przelewy24payment/views/templates/admin/config/form.html.twig', [
            'old_module_installed' => $this->oldModuleInstalled,
            'migration_url' => $this->generateUrl('przelewy24.migrateOldConfiguration'),
            'help_link' => false,
            'sandbox_mode' => $model->test_mode,
            'form' => $form->createView(),
            'tabs' => $tabs,
        ]);
    }

    public function migrateOldConfigurationAction(ConfigurationMigrator $configurationMigrator)
    {
        try {
            $configurationMigrator->migrate();
        } catch (\Exception $e) {
            $this->addFlash('error', $this->translator->trans('Configuration migration failed', [], 'Modules.Przelewy24payment.Form'));

            return $this->redirectToRoute('przelewy24.index');
        }
        $logs = $configurationMigrator->getLogs();
        foreach ($logs as $log) {
            $this->addFlash(key($log), current($log));
        }
        if (empty($logs)) {
            $this->addFlash('warning', $this->translator->trans('Noting to migrate', [], 'Modules.Przelewy24payment.Form'));
        }

        return $this->redirectToRoute('przelewy24.index');
    }

    public function changeAccountAction(
        Request $request,
        TabsFactory $tabsFactory,
        Przelewy24Config $przelewy24Config
    ) {
        $id_account = $request->get('id_account');
        $model = new Przlewy24AccountModel($id_account);
        $przelewy24Config->setAccount($model, false);
        $tabs = $tabsFactory->factory($przelewy24Config);

        return $this->json([
            'content' => $tabs->render(),
        ]);
    }

    public function saveFormAction(
        $type,
        Request $request,
        Przelewy24Config $przelewy24Config,
        FormTabFactory $formTabFactory,
        CommandBusInterface $commandBus,
        ConnectionFactory $connectionFactory,
        FormTypeEnum $formTypeEnum,
        EventDispatcher $eventDispatcher
    ) {
        $idAccount = $request->get('id_account');
        $model = new Przlewy24AccountModel((int) $idAccount);
        $przelewy24Config->setAccount($model, true, false, true);
        $oldConfig = clone $przelewy24Config;
        $oldConfig->cloneObjectByType($type);
        $form = $formTabFactory->factory($przelewy24Config, $type);
        $form->handleRequest($request);
        $success = true;
        $message = false;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $command = $form->getData();
                $handlerResult = $commandBus->handle($command);
                $event = new SaveConfigEvent($command, $oldConfig);
                $eventDispatcher->dispatch($event, SaveConfigEvent::NAME);
                if (!$handlerResult) {
                    $form->addError(new FormError($this->translator->trans('Error saving configuration', [], 'Modules.Przelewy24payment.Form')));
                    $success = false;
                } else {
                    $idAccount = $command->getIdAccount();
                    $model = new Przlewy24AccountModel($idAccount);
                    $connectionFactory->factory($model);
                }
            } catch (AccountNotFoundApiException $e) {
                $form->addError(new FormError($this->translator->trans('Not configured credentials', [], 'Modules.Przelewy24payment.Form')));
            } catch (ConnectionFailedApiException $e) {
                $form->addError(new FormError($this->translator->trans('Credentials incorrect', [], 'Modules.Przelewy24payment.Form')));
            } catch (\Exception $e) {
                $success = false;
                $form->addError(new FormError($this->translator->trans('Error saving configuration', [], 'Modules.Przelewy24payment.Form')));
            }
        }
        if ($success && $form->isValid()) {
            $message = $this->translator->trans('Configuration saved', [], 'Modules.Przelewy24payment.Form');
        }
        $redirect = $formTypeEnum->isRedirect($type) ? $this->generateUrl('przelewy24.index', ['type' => $type]) : false;

        $content = $this->render('@Modules/przelewy24payment/views/templates/admin/config/form_card.html.twig', [
            'form' => $form->createView(),
            'title' => $formTypeEnum->getTabName($type),
            'message' => $message,
        ])->getContent();

        return $this->json([
            'content' => $content,
            'redirect' => $redirect,
            'status' => $success && $form->isValid(),
        ]);
    }

    public function changeTestModeAction(
        Request $request,
        CredentialsConfigurationProvider $configurationProvider,
        FormFactoryInterface $formFactory,
        FormTypeEnum $formTypeEnum
    ) {
        $model = new Przlewy24AccountModel($request->get('id_account'));
        $model->test_mode = $request->get('test_mode');
        $formBuilder = $formFactory->createBuilder(CredentialsType::class, $configurationProvider->getConfiguration($model));
        $form = $formBuilder->getForm();

        $content = $this->render('@Modules/przelewy24payment/views/templates/admin/config/form_card.html.twig', [
            'form' => $form->createView(),
            'title' => $formTypeEnum->getTabName(FormTypeEnum::CREDENTIALS),
        ])->getContent();

        return $this->json([
            'content' => $content,
        ]);
    }
}
