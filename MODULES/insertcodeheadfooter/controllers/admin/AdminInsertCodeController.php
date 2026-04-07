<?php
/**
 * Admin Controller for Insert Code HTML to HEAD/FOOTER module.
 *
 * Not registered in admin menu (no Tab record). Configuration is
 * accessed exclusively through Module Manager > Configure.
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2024-2026 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminInsertCodeController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        if ($this->module) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules', true)
                . '&configure=' . $this->module->name
            );
        }
    }
}
