=== Pre-Order Deposit Manager for WooCommerce ===
Contributors: byot, alkesh7
Tags: woocommerce, pre-order, deposit, payments, orders
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
WC requires at least: 6.0
WC tested up to: 11.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers pay a 30% deposit on pre-order products, then collect the rest automatically or manually once the item is ready.

== Description ==

Pre-Order Deposit Manager for WooCommerce lets customers pay a 30% deposit on pre-order products and settle the remaining 70% once the item is ready for delivery — either automatically through a saved card or manually by the store admin.

= Features =

* Enable "Pre-Order Deposit" on any simple or variable product
* Automatic 30% deposit calculation shown on the product page, cart, and checkout
* Custom order statuses: Deposit Paid, Ready for Delivery, Completed
* Order admin panel showing full total, deposit paid, and remaining balance
* One-click "Mark Ready & Notify" to email the customer when the product is ready
* Optional automatic charging of the remaining balance using the customer's saved payment token (Stripe and other token-compatible gateways)
* Manual fallback if no saved card is available
* Custom order list column highlighting pre-order status and remaining balance
* Branded HTML + plain-text email notification when the order is ready
* Compatible with WooCommerce High-Performance Order Storage (HPOS)

= How it works =

1. A customer buys a pre-order product and pays only 30% of the price at checkout.
2. The order is placed in the "Pre-Order: Deposit Paid" status.
3. When the product is ready, the admin clicks "Mark Ready & Notify" on the order screen.
4. The customer receives an email letting them know the remaining balance is due.
5. The admin can trigger "Auto-Charge Remainder" (if a saved card is available) or collect the remaining payment manually, then mark the order completed.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pre-order-deposit-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Edit any product, open the "Product data" panel, and check "Pre-Order Deposit".
4. Save the product — the deposit pricing will appear automatically on the front end.

== Frequently Asked Questions ==

= Does this work with variable products? =

Yes. The "Pre-Order Deposit" option is available on both simple and variable products.

= What happens if the customer has no saved payment method? =

The admin can still mark the order ready and notify the customer; the remaining balance can then be collected manually (e.g. by sending an invoice or taking payment over the phone) instead of using the automatic charge feature.

= Is this compatible with WooCommerce's High-Performance Order Storage (HPOS)? =

Yes, the plugin declares and supports HPOS compatibility.

== Screenshots ==

1. Deposit pricing shown on the product page.
2. Pre-order payment breakdown in the order admin panel.

== Changelog ==

= 1.0.1 =
* Added WooCommerce HPOS (High-Performance Order Storage) compatibility.
* Fixed the "Pre-Order: Completed" status being silently truncated by WordPress's 20-character post_status column limit.
* Fixed a missing nonce check on saving the product-level pre-order setting.
* Fixed unescaped output across the admin panel, cart notice, and email templates.
* Fixed two crash bugs: a plugin-activation fatal error, and a fatal error in the "Mark Ready & Notify" flow.
* Fixed the plugin's text domain to match its slug, and wrapped remaining untranslated strings.
* Renamed the plugin to "Pre-Order Deposit Manager for WooCommerce".
* Declared compatibility with WooCommerce 11.0 and WordPress 7.1.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Fixes a data-integrity bug where the "Completed" pre-order status could not be saved correctly, plus several security and compatibility fixes. Upgrading is recommended for all users.
