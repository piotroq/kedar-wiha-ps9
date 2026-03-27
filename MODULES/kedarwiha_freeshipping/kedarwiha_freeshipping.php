<?php
/**
 * KEDAR-WIHA — Darmowa dostawa od 1000 zł netto
 * Fix v1.1: usunięto rejestrację hooka actionCarrierProcess
 * (PS9 wymaga metody hookX dla każdego zarejestrowanego hooka X)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kedarwiha_Freeshipping extends Module
{
    const FREE_SHIPPING_THRESHOLD = 1000.00;

    public function __construct()
    {
        $this->name          = 'kedarwiha_freeshipping';
        $this->tab           = 'shipping_logistics';
        $this->version       = '1.0.0';
        $this->author        = 'KEDAR-WIHA Dev';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = 'Darmowa dostawa od 1000 zł';
        $this->description = 'Automatyczna darmowa dostawa dla zamówień powyżej 1000 zł netto.';
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayCheckoutSummaryTop')
            && $this->createCartRule();
    }

    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    private function createCartRule(): bool
    {
        $exists = Db::getInstance()->getValue(
            "SELECT id_cart_rule FROM `" . _DB_PREFIX_ . "cart_rule`
             WHERE `code` = 'KEDAR_FREESHIP_AUTO'"
        );
        if ($exists) {
            return true;
        }

        $cartRule                    = new CartRule();
        $cartRule->name              = [Configuration::get('PS_LANG_DEFAULT') => 'Darmowa dostawa KEDAR-WIHA'];
        $cartRule->code              = 'KEDAR_FREESHIP_AUTO';
        $cartRule->active            = 1;
        $cartRule->quantity          = 0;
        $cartRule->quantity_per_user = 0;
        $cartRule->minimum_amount    = self::FREE_SHIPPING_THRESHOLD;
        $cartRule->minimum_amount_tax      = 0;
        $cartRule->minimum_amount_shipping = 0;
        $cartRule->free_shipping     = 1;
        $cartRule->date_from         = date('Y-m-d 00:00:00');
        $cartRule->date_to           = date('Y-m-d 00:00:00', strtotime('+10 years'));
        $cartRule->highlight         = 0;
        $cartRule->active_on_first_order = 0;

        return (bool) $cartRule->add();
    }

    public function hookDisplayShoppingCartFooter(array $params): string
    {
        return $this->renderBar();
    }

    public function hookDisplayCheckoutSummaryTop(array $params): string
    {
        return $this->renderBar();
    }

    private function renderBar(): string
    {
        $cart      = $this->context->cart;
        $totalNett = (float) $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $threshold = self::FREE_SHIPPING_THRESHOLD;
        $remaining = max(0, $threshold - $totalNett);
        $percent   = min(100, round(($totalNett / $threshold) * 100));

        $this->context->smarty->assign([
            'kw_fs_total_nett' => $totalNett,
            'kw_fs_threshold'  => $threshold,
            'kw_fs_remaining'  => $remaining,
            'kw_fs_percent'    => $percent,
            'kw_fs_is_free'    => $totalNett >= $threshold,
            'kw_fs_currency'   => $this->context->currency->sign,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/free_shipping_bar.tpl');
    }
}
