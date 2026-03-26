<?php
class PosWishlistMyCartModuleFrontController extends ModuleFrontController
{

    public $id_product;

    public function init()
    {
        parent::init();

        $this->id_product = (int)Tools::getValue('id_product');
    }

    public function postProcess()
    {
        if (Tools::getValue('action') == 'remove') {
            $this->ajaxProcessRemove();
        } elseif (Tools::getValue('action') == 'add') {
            $this->ajaxProcessAdd();
        } elseif (Tools::getValue('action') == 'removeAll') {
            $this->ajaxProcessRemoveAll();
        }
		 elseif (Tools::getValue('action') == 'checkCompare') {
            $this->checkCompare();
        }
    }

    /**
     * Add product to compare.
     */
    public function ajaxProcessAdd()
    {   
      
      $action = Tools::getValue('action');
      $add = (!strcmp($action, 'add') ? 1 : 0);
      $delete = (!strcmp($action, 'delete') ? 1 : 0);
      $id_wishlist = (int)Tools::getValue('id_wishlist');
      $id_product = (int)Tools::getValue('id_product');
      $quantity = (int)Tools::getValue('quantity');
      $id_product_attribute = (int)Tools::getValue('id_product_attribute');
      //dd($action,$add,$delete,$id_wishlist,$id_product,$quantity,$id_product_attribute);

      if ($this->context->customer->isLogged()) {
         header('Content-Type: application/json');
          if ($id_wishlist && WishListClass::exists($id_wishlist, $this->context->customer->id) === true)
            $this->context->cookie->id_wishlist = (int)$id_wishlist;

          if ((int)$this->context->cookie->id_wishlist > 0 && !WishListClass::exists($this->context->cookie->id_wishlist, $this->context->customer->id))
            $this->context->cookie->id_wishlist = '';

          if (empty($this->context->cookie->id_wishlist) === true || $this->context->cookie->id_wishlist == false)
            $this->context->smarty->assign('error', true);

          if (($add || $delete) && empty($id_product) === false)
          {
            if (!isset($this->context->cookie->id_wishlist) || $this->context->cookie->id_wishlist == '')
            {
              $wishlist = new WishListClass();
              $wishlist->id_shop = $this->context->shop->id;
              $wishlist->id_shop_group = $this->context->shop->id_shop_group;
              $wishlist->default = 1;

              $wishlist->name = $this->l('My wishlist');
              $wishlist->id_customer = (int)$this->context->customer->id;
              list($us, $s) = explode(' ', microtime());
              srand($s * $us);
              $wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$this->context->customer->id), 0, 16));
              $wishlist->add();
            }

            if ($add && $quantity)
              WishListClass::addProduct($this->context->customer->id, $id_product, $id_product_attribute, $quantity);
            else if ($delete)
              WishListClass::removeProduct($this->context->cookie->id_wishlist, $this->context->customer->id, $id_product, $id_product_attribute);
          }
          //echo (int)Db::getInstance()->getValue('SELECT count(id_wishlist_product) FROM '._DB_PREFIX_.'poswishlist w, '._DB_PREFIX_.'poswishlist_product wp where w.id_wishlist = wp.id_wishlist and w.id_customer='.(int)$context->customer->id);
          $count = (int)Db::getInstance()->getValue('SELECT count(id_wishlist_product) FROM '._DB_PREFIX_.'poswishlist w, '._DB_PREFIX_.'poswishlist_product wp where w.id_wishlist = wp.id_wishlist and w.id_customer='.(int)$this->context->customer->id);

          $this->ajaxRender(json_encode(array(
                'success' => true,
                'count' => $count ?? 0
            )));
            die('');
      } else {
        echo $this->l('You must be logged in to manage your wishlist.', 'cart');
           $this->ajaxRender(json_encode(array(
                'success' => false,
            )));
        die('');
      }


    }


    
}