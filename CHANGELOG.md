## Changelog
* 2.1.18 - 2019.01.23
    * New: Subscriptions interface, now core is compatible with subscriptions plugin.
    * Fix: Css issues on product page.
    * Fix: Not visible categories and tags in gutenberg editor.
    * Fix: Translations.
    * Fix: Possible notice on Order edit page.
    * Fix: Extensions Interface.
    * Fix: Init tax service before endpoints.
    * Fix: Possible fatal error on checkout page.
* 2.1.17 - 2018.09.25
    * New: Possibility to change product list style in all widgets.
    * Fix: Rewrote all frontend css files.
    * Fix: Fix fatal error on order Page.
* 2.1.16 - 2018.09.05
    * New: EU Vat validation.
    * New: Support for guttenberg.
    * Fix: jQuery flot error.
    * Fix: Do not show stock fields in quick edit for variable products.
    * Fix: Glyph icons in admin panel.
    * Fix: Improved extensions interface.
    * Fix: Notice on first page of setup wizard.
    * Fix: All shipping methods now use Method3 interface.
    * Fix: Handle shipping exceptions.
    * Fix: Show product sizes on product page.
    * Fix: Blueimp minification.
    * Fix: Fatal error on Pay page, about invalid order ID.
* 2.1.15 - 2018.07.23
    * New: For downloads use url from product, if it still exists, otherwise use url from item meta.
    * New: Round prices for CZK currency.
    * Fix: Select shipping method automatically if there is only one available.
    * Fix: Properly create cart from order.
    * Fix: Issues with removing variation images.
    * Fix: Notice on product with no price when applying tax.
    * Fix: Properly apply shipping fields to order.
* 2.1.14 - 2018.06.25
    * New: Allow to change on-hold orders to processing on order list.
    * New: Added wp-cli to Travis.
    * Fix: External plugins now can modify shipping costs.
* 2.1.13 - 2018.05.29
    * New: Orders now stores actual currency and order prices in admin area will be shown using this currency.
    * Fix: Situations where order has no shipping.
    * Fix: Removed wordpress fields from REST API.
    * Fix: Invalid postcode validation on editing address.
    * Fix: Properly update states on country change in edit address page.
    * Fix: Fatal error after manually adding items to order.
    * Fix: Avoid stock html input errors.
    * Fix: Add babel framework to add scripts compatibility with older browsers.
* 2.1.12 - 2018.04.09
    * New: Allow to hide out of stock variations.
    * Fix: Properly show product stock status.
* 2.1.11.2 - 2018.03.20
    * Fix: Properly save settings on Payment page with no default gateway selected.
* 2.1.11.1 - 2018.03.20
    * Updated all vendors and removed unused files.
* 2.1.11 - 2018.03.19
    * New: Order processing fee.
    * Fix: Properly show product list, when shop page is set as static front page.
    * Fix: Properly hide/show stock column in admin product list.
    * Fix: Add product review only when rating was posted.
    * Fix: Notices on Thank you page, when order does not have payment method.
    * Fix: Php/wp memory limit, added *G value parsing.
    * Fix: Errors on cart/checkout page when switching shipping location with inproperly configured shipping method present.
    * Fix: Renamed `coffee-script` module to `coffeescript` in gulp.
    * Fix: Hide discount section in My Account Order preview, when discount was not used.
    * Fix: Show information about backorder on product page.
* 2.1.10 - 2018.01.23
    * New: WorldPay gateway.
    * New: Template error handler.
    * Fix: Unify payment and shipping settings tabs.
    * Fix: Update product quantity in cart properly.
    * Fix: `Disable manage stock and set ins stock status for all products` tool now works for all products.
    * Fix: Show exception message when trying to add review without rating.
    * Fix: Changed text domain in places where it was missing or invalid.
    * Fix: Properly remove item from cart via url.
* 2.1.9 - 2017.11.27
    * Fix: Fix readme.txt.
    * Fix: Add Company name and Vat number to thank you page.
    * Fix: Add Vat number variable to order emails.
    * Fix: Added filter to determine proper template for external product types.
    * Fix: Added action before checkout payment methods.
* 2.1.8 - 2017.10.30
    * New: Default variation image will be shown if variable product has no featured image.
    * Fix: Checkout/cart page to allow external product types from plugins.
    * Fix: Product filter widget.
    * Fix: Form controls in payment/shipping methods.
    * Fix: Issues with product category WP editor.
* 2.1.7 - 2017.09.27:
    * Fix: Now plugin is available to translate.
    * Fix: Do not add thousand separator to paypal amounts.
    * Fix: Improve product and order search mechanism.
    * Fix: Category description editor.
    * Fix: Selects in payment gateways.
* 2.1.6.1 - 2017.09.21:
    * Fix: Added BN code to paypal plugin.
* 2.1.6 - 2017.09.20:
    * New: Cron.
    * New: Ability to sort option in attributes.
    * New: Setup wizard.
    * Fix: Category description editor errors.
    * Fix: Paypal now changes status to refunded.
    * Fix: Issues with advanced flat rate.
    * Fix: Unified button text in settings.
    * Fix: Ability to close popup on payment settings.
    * Fix: CSV customer reports now properly show first and last name.
    * Fix: Properly display bank account number.
    * Fix: Paypal payment link on some weird server configuration.
* 2.1.5 - 2017.08.16:
    * New: Product quick edit fields.
    * New: Product Categories admin page.
    * New: Order status email variable.
    * Fix: Special characters in product and order fields.
    * Fix: Properly show variation sku in order email.
    * Fix: Sale price display.
    * Fix: Properly display select attribute on product page.
    * Fix: Do not destroy session on logout.
    * Fix: Stock amount now can not be less than 0.
    * Fix: Add email variables from JS1.
* 2.1.4 - 2017.06.14:
    * New: Email action for order status change for admin.
    * New: Possibility to show prices with and without tax.
    * New: All forms now have data parameter.
    * New: Shipping and Payment interfaces.
    * Fix: Do not allow to add to cart variations with not enough stock.
    * Fix: Show only selected attribute options for specified variable product.
    * Fix: Api responses now properly includes result counts, next and prev paths, created/updated objects.
    * Fix: PayPal error when order amount was more than 999.
    * Fix: E-mail footer not included in Jigoshop emails.
    * Fix: Non-existent tax classes supplied to TaxService.
    * Fix: Not possible to disable stock manage in product variation.
    * Fix: Price filter widget, do not allow to set the same price as min and max.
    * Fix: Fatal error when up-sell or cross-sell product was deleted.
    * Fix: Do not return null in ajax product find when id doesn't math any product.
* 2.1.3 - 2017.05.25:
    * New: Allow to prepend product permalink with Wordpress permalink.
    * New: Tool to fix order items migration.
    * Fix: Fatal error on order edit page caused by Free shipping.
    * Fix: Properly migrate order items.
    * Fix: Discount migration.
* 2.1.2 - 2017.05.11:
    * New: Possibility to get country code in ISO 3166-1 alfa-3 standard.
    * Fix: Properly display custom settings tabs.
    * Fix: Properly migrate stock status.
* 2.1.1 - 2017.05.10:
    * New: Tools to remove zombie variations and meta.
    * Fix: Remove debug method from, create variations from all attributes.
    * Fix: Hide out of stock products option.
    * Fix: Adding related products to cart.
    * Fix: Fatal error related to wrong namespace use.
    * Fix: Allow to query product tags and categories at the same time.
    * Fix: Adding custom settings tab by using Integration.
    * Fix: Monthly report on dashboard should not predict future.
    * Fix: Dynamically remove shipping from cart if shipping is not required.
    * Fix: Coupon usage limit.
    * Fix: Missing tax classes options in variation form.
    * Fix: Improved prompt box for variation bulk actions.
    * Fix: Properly migrate product attachments.
    * Fix: Add products subtotal to order api response.
* 2.1 - 2017.04.26:
    * New: Admin notices.
    * New: My Downloads panel in my account.
    * New: Continents in Advanced flat rate shipping.
    * New: Dimensions and weight fields for product variations.
    * New: Replace product featured image with image from selected variation.
    * New: Discount entity.
    * New: Blueimp gallery for products.
    * New: Ability to add attachments to email.
    * New: RenderPay Interface
    * New: Allow to prioritize rates in advanced flat rate.
    * New: Variation bulk actions.
    * Fix: Product and order search.
    * Fix: Product filtering in admin panel.
    * Fix: Do not show migration if there is no reason to migrate.
    * Fix: Properly save and downloa all downloadable items.
    * Fix: Download link for variation.
    * Fix: Product category and tag queries.
    * Fix: Discount summary report.
    * Fix: Coupon usage count.
    * Fix: Fee in Advanced flat rate now can be set as float.
    * Fix: Wordpress links in dashboard.
    * Fix: Fix today report graphs, now it do not show graph to next hour.
    * Fix: Do not display shipping rates in admin panel when order does not require shipping.
    * Fix: Disable post title in order edit page.
    * Fix: Properly show downloadable fields after changing variation type.
    * Fix: Fix category thumbnail styles.
    * Fix: Properly show unpaid order list in my account.
    * Fix: Save product dynamically after product type change.
    * Fix: Do not allow to set variable product sale.
    * Fix: Only billing option.
    * Fix: Properly remove product fields from cart.
    * Fix: Shipping rate title in cart after ajax refresh.
    * Fix: Properly remove variation featured image.
* 2.0.10.2 - 2017.03.22:
    * Fix: Sanitized product title on product save in Wordpress panel.
    * Fix: Post excerpt was saved as product description.
    * Fix: Fatal error on Order page when DOMDocument is missing.
    * Fix: Discounts not being removed.
    * Disabled: Discount Summary report due to serious issue.
* 2.0.10.1 - 2017.03.21:
    * Fix: Fatal error on product save.
* 2.0.10 - 2017.03.21:
    * Fix: Added Layout settings tab.
    * Fix: Some action hook names.
    * Fix: Fatal error when order was saved in admin panel triggered by multiple method shippings.
    * Fix: Variation prices on product page.
    * Fix: Item price suffix is Account Orders, Checkout Pay, Thant You pages.
    * Fix: Displaying file attachments on product page.
    * Fix: Fix fatal error on activation triggered by options helper.
    * Fix: Tax helper should return tax for Taxable products.
    * Fix: Properly interpret ignore meta queries option.
    * Fix: Now coupons and emails are available via api.
    * Fix: Displaying free instead of price not announced.
    * Fix: Edit address button.
    * Fix: External view product button on product list.
    * Fix: Notice on order list when product was removed.
    * Fix: Show all orders on Account Orders page.
    * Fix: Show view product button when product doesn't have price.
    * Fix: Notice about undefined key in session for recently viewed products widget.
    * Fix: Don't set shipping method when shipping is not required.
    * Fix: Notice caused by dashboard monthly report.
    * Fix: Order migration when product no longer exists.
    * Fix: Input coupon field on checkout page.
    * Fix: Fatal error when product does not have default tax classes selected.
    * Fix: Fatal error when attribute was removed from product.
    * Fix: Query Interceptor for custom Jigoshop pages.
    * Fix: Default payment gateway select.
    * Fix: Reports for variable products.
    * Fix: Custom permalinks for products.
* 2.0.9 - 2017.02.13:
    * Fix: Support Twentyseventeen theme.
    * Fix: Improve product reviews.
    * Fix: Integrate up sells cross sells plugin.
    * Fix: Integrate add flat rate plugin.
    * Fix: Improve tax settings.
    * Fix: Fatal error when ordered product does not exists.
* 2.0.8 - 2017.02.01:
    * Fix: Allow to remove all attachments.
    * Fix: Do not recalculate discounts for placed orders.
    * Fix: Allow to change attribute option value.
    * Fix: Add tax classes column to order item table.
    * Fix: Allow to suppress all emails for current request.
    * Fix: Do not use new tax definitions for saved orders.
    * Fix: Allow to use prices including tax.
    * Fix: Use Slim 3 instead of handmade framework.
    * Fix: Rewrite ApiDeprecated class to Endpoint.
    * Fix: File downloading.
    * Fix: Allow to select default variation.
    * Fix: Variation sale datepickers.
    * Fix: Improve attachments structure.
    * Fix: Do shortcodes in product description. 
    * Fix: Bank transfer fields values.
    * Fix: Improve cart RWD.
    * Fix: Downloadable product should be a child of simple product.
    * Fix: Flat rate shipping should ignore non shippable products.
    * Fix: Force rewrite permaling on every Jigoshop eCommerce update.
* 2.0.7 - 2016.12.28:
    * Fix: Downloadable email link.
    * Fix: Updating shipping methods in checkout.
    * Fix: Order link in my account.
    * Fix: Fatal error on product page.
* 2.0.6 - 2016.12.2:
    * Fix: Do not allow to add external products to cart.
    * Fix: Fix external product url.
    * Fix: Checkout fields validation.
    * Fix: Checkout registration.
    * Fix: Remove usage of `price` meta from purchasable products.
    * Fix: Properly handle account creation errors on checkout.
* 2.0.5 - 2016.11.24:
    * Fix: Updating customer on checkout page.
    * Fix: Removed unnecessary filter.
* 2.0.4.1 - 2016.11.23:
    * Fix: Tax calculation.
* 2.0.4 - 2016.11.23:
    * Fix: Display dates in dashboard report chart.
    * Fix: Allow to recalculate taxes for existing order.
    * Fix: Add missing templates for external and virtual products.
    * Fix: Allow to search product list by SKU.
    * Fix: Properly calculate tax for saved orders.
    * Fix: Show only assigned options for variable products.
    * Fix: Properly change order status after bank transfer/cheque/COD payment.
* 2.0.3 - 2016.11.17:
    * Fix: Product and Order filtering on admin.
    * Fix: Sending stock emails when product has no stock managing.
    * Fix: Use only meta to store email template actions, to prevent issues with option field.
    * Fix: Permalinks in my account.
    * Fix: Properly change state and postcode on checkout.
    * Fix: Fix typo in default email action name.
    * Fix: Duplicated customer role.
    * Fix: Not displaying persistent warrnings and errors.
    * Fix: Fatal error durring order migration when product variation does not exists.
    * Fix: Typo in reports filter button.
    * Fix: Allow to change order date.
    * Fix: Cart tax calculation for logged users.
    * Fix: Do not create product when doing an autosave.
    * Fix: Various fixes for virtual products.
    * Fix: Properly display price field for every product type.
    * Fix: Properly display url field for external product.
* 2.0.2 - 2016.11.7:
    * Fix: Paypal response url.
    * Fix: Shipping totals in reports by date.
    * Fix: Fatal error when order has no shipping method.
    * Fix: Order status after completed Paypal payment.
* 2.0.1 - 2016.11.2:
    * Fix: Migration alert message.
    * Fix: Updated Jigoshop logotypes.
    * Fix: Admin Order and Product list search.
    * Fix: Fatal error on admin order item variable template.
    * Fix: Paypal response handling.
    * Fix: Duplicatd foregin keys.
    * Fix: Show add to cart button for free products.
    * Fix: Change order status if payment is not required.
    * Fix: Properly clear cart after checkout.
    * Fix: Stock reports.
    * Fix: Ajax product search.
* 2.0.0 - 2016.10.27:
    * Full rewrite of core.