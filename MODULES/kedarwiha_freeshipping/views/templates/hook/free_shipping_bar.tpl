{**
 * Pasek postępu darmowej dostawy
 * Plik: modules/kedarwiha_freeshipping/views/templates/hook/free_shipping_bar.tpl
 **}
<div
  class="kw-free-shipping-bar"
  role="status"
  aria-live="polite"
  aria-label="{if $kw_fs_is_free}{l s='Masz darmową dostawę!' d='Modules.Kedarwihafs.Shop'}{else}{l s='Postęp do darmowej dostawy' d='Modules.Kedarwihafs.Shop'}{/if}"
>
  {if $kw_fs_is_free}
    {* SUKCES — masz darmową dostawę *}
    <div class="kw-free-shipping-bar__success">
      <span class="material-symbols-outlined kw-free-shipping-bar__icon" aria-hidden="true">local_shipping</span>
      <strong>{l s='🎉 Masz darmową dostawę!' d='Modules.Kedarwihafs.Shop'}</strong>
      <span class="kw-free-shipping-bar__sub">
        {l s='Twoje zamówienie kwalifikuje się do bezpłatnej wysyłki.' d='Modules.Kedarwihafs.Shop'}
      </span>
    </div>
  {else}
    {* POSTĘP — ile brakuje *}
    <div class="kw-free-shipping-bar__progress-wrap">
      <div class="kw-free-shipping-bar__label">
        <span class="material-symbols-outlined kw-free-shipping-bar__icon" aria-hidden="true">local_shipping</span>
        <span>
          {l s='Dodaj produkty za' d='Modules.Kedarwihafs.Shop'}
          <strong>{$kw_fs_remaining|string_format:"%.2f"} {$kw_fs_currency}</strong>
          {l s='netto aby otrzymać darmową dostawę' d='Modules.Kedarwihafs.Shop'}
        </span>
      </div>
      <div
        class="kw-free-shipping-bar__track"
        role="progressbar"
        aria-valuenow="{$kw_fs_percent}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="{$kw_fs_percent}% do darmowej dostawy"
      >
        <div
          class="kw-free-shipping-bar__fill"
          style="width: {$kw_fs_percent}%"
        ></div>
      </div>
      <div class="kw-free-shipping-bar__values">
        <span>{$kw_fs_total_nett|string_format:"%.2f"} {$kw_fs_currency}</span>
        <span>{$kw_fs_threshold|string_format:"%.0f"} {$kw_fs_currency} netto</span>
      </div>
    </div>
  {/if}
</div>

<style>
.kw-free-shipping-bar {
  background: #f8f8f8;
  border: 1px solid rgba(21,21,21,0.1);
  border-radius: 10px;
  padding: 0.875rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.kw-free-shipping-bar__success {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #2E7D32;
}
.kw-free-shipping-bar__success strong { font-size: 0.9rem; }
.kw-free-shipping-bar__sub { color: #606060; font-size: 0.8rem; }

.kw-free-shipping-bar__icon { font-size: 20px; flex-shrink: 0; }

.kw-free-shipping-bar__label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.5rem;
  color: #151515;
}

.kw-free-shipping-bar__track {
  height: 8px;
  background: rgba(21,21,21,0.1);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.35rem;
}
.kw-free-shipping-bar__fill {
  height: 100%;
  background: linear-gradient(90deg, #8B0000, #C62828);
  border-radius: 4px;
  transition: width 0.4s ease;
}

.kw-free-shipping-bar__values {
  display: flex;
  justify-content: space-between;
  color: #606060;
  font-size: 0.72rem;
}
</style>
