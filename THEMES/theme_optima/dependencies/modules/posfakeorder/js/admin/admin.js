/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*/
jQuery(function($){

		var token_product = $('#admin_info').data('token_product'); 
		var is_ps9 = $('#module_form').data('ps9');
	 
	  if(is_ps9)  {
       var searchUrl = $('#module_form').data('base_url_admin')+'/index.php/sell/catalog/products/search/en?_token='+token_product;	
		
		} else {
			 token_product = $('body').data('token');
			  var searchUrl = $('#module_form').data('base_url_admin')+'/index.php/sell/catalog/products-v2/search/en?_token='+token_product;	
		}
     $("#product_name")
		    // don't navigate away from the field on tab when selecting an item
		    .on("keydown", function(event) {
		      if (event.keyCode === $.ui.keyCode.TAB &&
			    $(this).autocomplete("instance").menu.active) {
			    event.preventDefault();
		      }
		    })
		    .autocomplete({
		      source: function(request, response) {
			$.ajax({
			  url: searchUrl,
			  dataType: "json",
			  data: {
			    query: request.term.split(/,\s*/).pop(),
			    max_rows: 12,
			  },
			  success: function(data) {			   
					console.log('check data', data);
			    const newData =   data.map(function(item) {
			  	 return {"id" : item.id, "label" : item.name, "value" : item.id,img:item.image};
			  })
			    response(newData);
			  }
			});
		      },
		      minLength: 2,
		      focus: function() {
			    // prevent value inserted on focus
			    return false;
		      },
		      select: function(event, ui) {
		        console.log('check select',this.value);
		      
                var terms = this.value.split(/,\s*/);
                    console.log('checktems',terms);
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                var productId = ui.item.value;
                var productName = ui.item.label;

                $('input[name=\'product_name\']').val('');
                $('#product' + productId).remove();
                var divProductName = $('#product-list');
                divProductName.append('<div id="product'+productId+'"><i class="icon-remove text-danger"></i>'+productName+'<input type="hidden" name="products[]" value="'+productId+'"/>');

                $('#product_name').setOptions({
                    extraParams: {excludeIds : getMenuProductsIds()}
                });
                return false;
		      },
		      open: function(event, ui) {
			// when used on a bootstrap's modal dialog, the autocomplete drop-down appears behind the modal.
			// this put it back on top
			$("ul.ui-autocomplete").css("z-index", 5000);
		      }
		    }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		       return $( "<li class=\""+item.id+"\"></li>" )
				.data( "item.autocomplete", item )
				.append( "<div><a>"+ item.label + "</a>" +`<img src="${item.img}" width=30 height=30  />` + "</div>" ) 
				.appendTo( ul );
		    };
            $('#product-list').delegate('.icon-remove', 'click', function(){
                $(this).parent().remove();
            });
   
});

var getMenuProductsIds = function()
{
    if (!$('#inputMenuProducts').val())
        return '-1';
    return $('#inputMenuProducts').val().replace(/\-/g,',');
}


