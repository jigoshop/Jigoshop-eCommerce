var AdminProductVariable,bind=function(t,e){return function(){return t.apply(e,arguments)}};AdminProductVariable=function(){function t(t){this.params=t,this.bulkAction=bind(this.bulkAction,this),this.removeVariation=bind(this.removeVariation,this),this.updateVariation=bind(this.updateVariation,this),jQuery("#product-type").on("change",this.removeParameters),jQuery("#do-bulk-action").on("click",this.bulkAction),jQuery("#product-variations").on("click",".remove-variation",this.removeVariation).on("click",".show-variation",function(t){var e;return e=jQuery(t.target),jQuery(".list-group-item-text",e.closest("li")).slideToggle(function(){return jQuery("span",e).toggleClass("glyphicon-collapse-down").toggleClass("glyphicon-collapse-up")})}).on("change","select.variation-attribute",this.updateVariation).on("change",".list-group-item-text input.form-control",this.updateVariation).on("change",'.list-group-item-text input[type="checkbox"]',this.updateVariation).on("change",".list-group-item-text select.form-control",this.updateVariation).on("click",".set_variation_image",this.setImage).on("click",".remove_variation_image",this.removeImage).on("change",'input[type="checkbox"].default_variation',function(t){return jQuery('input[type="checkbox"].default_variation').not(jQuery(t.target)).prop("checked",!1)}).on("change",'input[type="checkbox"].stock-manage',function(t){var e;return e=jQuery(t.target).closest("li"),jQuery(t.target).is(":checked")?(jQuery("div.manual-stock-status",e).slideUp(),jQuery(".stock-status",e).slideDown()):(jQuery("div.manual-stock-status",e).slideDown(),jQuery(".stock-status",e).slideUp())}).on("jigoshop.variation.add",function(t){return function(){return t.connectImage(0,jQuery(".set_variation_image").last())}}(this)).on("click",".schedule",function(t){return t.preventDefault(),jQuery(t.target).closest("fieldset").find(".not-active").removeClass("not-active"),jQuery(t.target).addClass("not-active")}).on("click",".cancel-schedule",function(t){var e;return t.preventDefault(),e=jQuery(t.target).closest("fieldset"),e.find(".not-active").removeClass("not-active"),jQuery(t.target).addClass("not-active"),e.find(".datepicker").addClass("not-active"),e.find("input.daterange-from").val(""),e.find("input.daterange-to").val("").trigger("change")}),jQuery(".set_variation_image").each(this.connectImage)}return t.prototype.ADD_VARIATION="1",t.prototype.CREATE_VARIATIONS_FROM_ALL_ATTRIBUTES="2",t.prototype.REMOVE_ALL_VARIATIONS="3",t.prototype.SET_PRODUCT_TYPE="4-(.*)",t.prototype.SET_REGULAR_PRICES="5",t.prototype.INCREASE_REGULAR_PRICES="6",t.prototype.DECREASE_REGULAR_PRICES="7",t.prototype.SET_SALE_PRICES="8",t.prototype.INCREASE_SALE_PRICES="9",t.prototype.DECREASE_SALE_PRICES="10",t.prototype.SET_SCHEDULED_SALE_DATES="11",t.prototype.TOGGLE_MANAGE_STOCK="12",t.prototype.SET_STOCK="13",t.prototype.INCREASE_STOCK="14",t.prototype.DECREASE_STOCK="15",t.prototype.SET_LENGTH="16",t.prototype.SET_WIDTH="17",t.prototype.SET_HEIGHT="18",t.prototype.SET_WEIGHT="19",t.prototype.SET_DOWNLOAD_LIMIT="20",t.prototype.params={i18n:{confirm_remove:"",variation_removed:"",saved:"",create_all_variations_confirmation:"",remove_all_variations:"",set_field:"",modify_field:"",sale_start_date:"",sale_end_date:"",buttons:{done:"",cancel:"",next:"",back:"",yes:"",no:""}}},t.prototype.disableUpdate=!1,t.prototype.removeParameters=function(t){var e;return e=jQuery(t.target),"variable"===e.val()?jQuery(".product_regular_price_field").slideUp():void 0},t.prototype.addVariation=function(t){var e;return t.preventDefault(),e=jQuery("#product-variations"),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.add_variation",product_id:e.closest(".jigoshop").data("id")}}).done(function(t){return function(i){return null!=i.success&&i.success?(t.disableUpdate=!0,jQuery(i.html).hide().appendTo(e).slideDown(function(){return t.disableUpdate=!1}).trigger("jigoshop.variation.add")):jigoshop.addMessage("danger",i.error,6e3)}}(this))},t.prototype.updateVariation=function(t){var e,i,o,r,a,s,n,u,c,p,d,l,h;if(!this.disableUpdate){for(e=jQuery("#product-variations"),i=jQuery(t.target).closest("li.list-group-item"),a=function(t){return"checkbox"===t.type||"radio"===t.type?t.checked:"select-multiple"===t.type?jQuery(t).val():t.value},o={},r=jQuery("select.variation-attribute",i).toArray(),s=0,u=r.length;u>s;s++)p=r[s],h=/(?:^|\s)product\[variation]\[\d+]\[attribute]\[(.*?)](?:\s|$)/g.exec(p.name),o[h[1]]=a(p);for(d={},l=jQuery('.list-group-item-text input.form-control, .list-group-item-text input[type="checkbox"], .list-group-item-text select.form-control',i).toArray(),n=0,c=l.length;c>n;n++)p=l[n],h=/(?:^|\s)product\[variation]\[\d+]\[product]\[(.*?)](\[(.*?)])?(?:\s|$)/g.exec(p.name),null!=h[3]?(d[h[1]]={},d[h[1]][h[3]]=a(p)):d[h[1]]=a(p);return jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.save_variation",product_id:e.closest(".jigoshop").data("id"),variation_id:i.data("id"),attributes:o,product:d}}).done(function(t){return function(e){return null!=e.success&&e.success?(i.trigger("jigoshop.variation.update"),jigoshop.addMessage("success",t.params.i18n.saved,2e3)):jigoshop.addMessage("danger",e.error,6e3)}}(this))}},t.prototype.removeVariation=function(t){var e;return t.preventDefault(),confirm(this.params.i18n.confirm_remove)?(e=jQuery(t.target).closest("li"),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.remove_variation",product_id:e.closest(".jigoshop").data("id"),variation_id:e.data("id")}}).done(function(t){return function(i){return null!=i.success&&i.success?(e.trigger("jigoshop.variation.remove"),e.slideUp(function(){return e.remove()}),jigoshop.addMessage("success",t.params.i18n.variation_removed,2e3)):jigoshop.addMessage("danger",i.error,6e3)}}(this))):void 0},t.prototype.connectImage=function(t,e){var i,o,r;return i=jQuery(e),o=i.next(".remove_variation_image"),r=jQuery("img",i.parent()),i.jigoshop_media({field:!1,bind:!1,thumbnail:r,callback:function(t){return o.show(),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.set_variation_image",product_id:i.closest(".jigoshop").data("id"),variation_id:i.closest(".variation").data("id"),image_id:t.id}}).done(function(t){return null!=t.success&&t.success?void 0:jigoshop.addMessage("danger",t.error,6e3)})},library:{type:"image"}})},t.prototype.setImage=function(t){return t.preventDefault(),jQuery(t.target).trigger("jigoshop_media")},t.prototype.removeImage=function(t){var e,i;return t.preventDefault(),e=jQuery(t.target),i=jQuery("img",e.parent()),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.set_variation_image",product_id:e.closest(".jigoshop").data("id"),variation_id:e.closest(".variation").data("id"),image_id:-1}}).done(function(t){return null!=t.success&&t.success?(i.prop("src",t.url).prop("width",150).prop("height",150).prop("srcset",""),e.hide()):jigoshop.addMessage("danger",t.error,6e3)})},t.prototype.bulkAction=function(t){var e;switch(jQuery("#variation-bulk-actions").val()){case this.ADD_VARIATION:return this.addVariation(t);case this.CREATE_VARIATIONS_FROM_ALL_ATTRIBUTES:return this.createVariationsFromAllAttributes();case this.REMOVE_ALL_VARIATIONS:return this.removeAllVariations();case this.SET_REGULAR_PRICES:return this.setFields("regular-price",this.params.i18n.set_field);case this.INCREASE_REGULAR_PRICES:return this.modifyFields("regular-price",this.params.i18n.modify_field,1);case this.DECREASE_REGULAR_PRICES:return this.modifyFields("regular-price",this.params.i18n.modify_field,-1);case this.SET_SALE_PRICES:return this.setFields("sale-price",this.params.i18n.set_field);case this.INCREASE_SALE_PRICES:return this.modifyFields("sale-price",this.params.i18n.modify_field,1);case this.DECREASE_SALE_PRICES:return this.modifyFields("sale-price",this.params.i18n.modify_field,-1);case this.SET_SCHEDULED_SALE_DATES:return this.setDates();case this.TOGGLE_MANAGE_STOCK:return this.toggleCheckboxes("stock-manage");case this.SET_STOCK:return this.setFields("stock-stock",this.params.i18n.set_field);case this.INCREASE_STOCK:return this.modifyFields("stock-stock",this.params.i18n.modify_field,1);case this.DECREASE_STOCK:return this.modifyFields("stock-stock",this.params.i18n.modify_field,-1);case this.SET_LENGTH:return this.setFields("size-length",this.params.i18n.set_field);case this.SET_WIDTH:return this.setFields("size-width",this.params.i18n.set_field);case this.SET_HEIGHT:return this.setFields("size-height",this.params.i18n.set_field);case this.SET_WEIGHT:return this.setFields("size-weight",this.params.i18n.set_field);case this.SET_DOWNLOAD_LIMIT:return this.setFields("download-limit",this.params.i18n.set_field)}return(e=jQuery("#variation-bulk-actions").val().match(/4-([a-z]+)/))?jQuery("select.variation-type","#product-variations").val(e).trigger("change"):void 0},t.prototype.removeAllVariations=function(){var t;return t={},t[this.params.i18n.buttons.yes]=!0,t[this.params.i18n.buttons.no]=!1,jQuery.prompt(this.params.i18n.remove_all_variations,{title:jQuery("#variation-bulk-actions option:selected").html(),buttons:t,classes:{box:"",fade:"",prompt:"jigoshop",close:"",title:"lead",message:"",buttons:"",button:"btn",defaultButton:"btn-primary"},submit:function(t,e,i,o){return e?(jigoshop.block(jQuery("#product-variations").closest(".jigoshop")),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.remove_all_variations",product_id:jQuery("#product-variations").closest(".jigoshop").data("id")}}).done(function(t){return null!=t.success&&t.success?jQuery("#product-variations").slideUp(function(){return jigoshop.unblock(jQuery("#product-variations").closest(".jigoshop")),jQuery("#product-variations li").remove(),jQuery("#product-variations").show()}):void 0})):void 0},zIndex:99999})},t.prototype.setFields=function(t,e){var i;return i={},i[this.params.i18n.buttons.done]=!0,i[this.params.i18n.buttons.cancel]=!1,jQuery.prompt(e+'<input type="text" class="form-control" name="value"></input>',{title:jQuery("#variation-bulk-actions option:selected").html(),buttons:i,focus:'input[type="text"]',classes:{box:"",fade:"",prompt:"jigoshop",close:"",title:"lead",message:"",buttons:"",button:"btn",defaultButton:"btn-primary"},submit:function(e,i,o,r){return i?jQuery("input."+t,"#product-variations").val(r.value).trigger("change"):void 0},zIndex:99999})},t.prototype.modifyFields=function(t,e,i){var o;return o={},o[this.params.i18n.buttons.done]=!0,o[this.params.i18n.buttons.cancel]=!1,jQuery.prompt(e+'<input type="text" class="form-control" name="value"></input>',{title:jQuery("#variation-bulk-actions option:selected").html(),buttons:o,focus:'input[type="text"]',classes:{box:"",fade:"",prompt:"jigoshop",close:"",title:"lead",message:"",buttons:"",button:"btn",defaultButton:"btn-primary"},submit:function(e,o,r,a){return o?a.value.search("%")>0?(a.value=Number(a.value.replace(/%|,| /,"")),isNaN(a.value)?alert("Invalid number"):(a.value=1+i*(a.value/100),jQuery("input."+t,"#product-variations").each(function(){return jQuery(this).val(Math.round(jQuery(this).val()*a.value*100)/100).trigger("change")}))):(a.value=a.value.replace(/,| /,""),isNaN(a.value)?alert("Invalid number"):jQuery("input."+t,"#product-variations").each(function(){return jQuery(this).val(Number(jQuery(this).val())+i*a.value).trigger("change")})):void 0},zIndex:99999})},t.prototype.toggleCheckboxes=function(t){return jQuery('input[type="checkbox"].'+t,"#product-variations").each(function(){return jQuery(this).prop("checked",!jQuery(this).is(":checked")).trigger("change")})},t.prototype.setDates=function(){var t,e,i;return e={},t={},e[this.params.i18n.buttons.next]=!0,e[this.params.i18n.buttons.cancel]=!1,t[this.params.i18n.buttons.done]=1,t[this.params.i18n.buttons.back]=-1,t[this.params.i18n.buttons.cancel]=0,i={set_start_date:{title:jQuery("#variation-bulk-actions option:selected").html(),html:this.params.i18n.sale_start_date+'<input type="text" class="form-control" name="from"></input>',buttons:e,submit:function(t,e,i,o){return e?jQuery.prompt.goToState("set_end_date"):jQuery.prompt.close(),!1}},set_end_date:{title:jQuery("#variation-bulk-actions option:selected").html(),html:this.params.i18n.sale_end_date+'<input type="text" class="form-control" name="to"></input>',buttons:t,submit:function(t,e,i,o){if(0===e)jQuery.prompt.close();else{if(1===e)return!0;-1===e&&jQuery.prompt.goToState("set_start_date")}return!1}}},jQuery.prompt(i,{classes:{box:"",fade:"",prompt:"jigoshop",close:"",title:"lead",message:"",buttons:"",button:"btn",defaultButton:"btn-primary"},close:function(t,e,i,o){return e?(jQuery("input.daterange-from","#product-variations").val(o.from),jQuery("input.daterange-to","#product-variations").val(o.to).trigger("change"),jQuery("a.schedule","#product-variations").trigger("click")):void 0},loaded:function(t){return jQuery('input[type="text"]',t.target).datepicker({autoclose:!0,todayHighlight:!0,clearBtn:!0,todayBtn:"linked"})},zIndex:99999})},t.prototype.createVariationsFromAllAttributes=function(){var t;return t={},t[this.params.i18n.buttons.yes]=!0,t[this.params.i18n.buttons.no]=!1,jQuery.prompt(this.params.i18n.create_all_variations_confirmation,{title:jQuery("#variation-bulk-actions option:selected").html(),buttons:t,classes:{box:"",fade:"",prompt:"jigoshop",close:"",title:"lead",message:"",buttons:"",button:"btn",defaultButton:"btn-primary"},submit:function(t,e,i,o){var r;return e?(jigoshop.block(jQuery("#product-variations").closest(".jigoshop")),r=jQuery("#product-variations"),jQuery.ajax({url:jigoshop.getAjaxUrl(),type:"post",dataType:"json",data:{action:"jigoshop.admin.product.create_variations_from_all_attributes",product_id:r.closest(".jigoshop").data("id")}}).done(function(t){return function(e){return null!=e.success&&e.success?(t.disableUpdate=!0,jigoshop.unblock(jQuery("#product-variations").closest(".jigoshop")),jQuery(e.html).hide().appendTo(r).slideDown(function(){return t.disableUpdate=!1}),jQuery(".set_variation_image",e.html).each(t.connectImage)):void 0}}(this))):void 0},zIndex:99999})},t}(),jQuery(function(){return new AdminProductVariable(jigoshop_admin_product_variable)});