=== Wallet Management for WooCommerce ===
Contributors: devdiggers
Tags: wallet, woocommerce wallet, store credit, cashback, digital wallet
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Give your WooCommerce customers a virtual wallet. Let them top up, pay at checkout with their balance, earn cart cashback and track every transaction.

== Description ==

**Wallet Management for WooCommerce** adds a fully functional digital wallet to your WooCommerce store. Customers can top up their wallet, pay for orders directly with their wallet balance, earn cart based cashback and review all of their wallet transactions from the My Account page. Store owners get a clean admin dashboard to manage balances, adjust funds, import balances in bulk and configure cashback.

The wallet works as a standard WooCommerce payment gateway, so once a customer has a balance they can check out in a single click, no need to re-enter payment details every time.

= Free Features =

* Enable or disable the wallet for your whole store.
* Wallet payment gateway, customers pay for orders with their balance.
* Wallet top-up with a hidden top-up product, configurable minimum and maximum top-up limits and quick top-up preset amounts.
* Choose the order status that confirms a top-up.
* Restrict top-ups to specific payment gateways.
* Fixed or percentage based debit limits for paying with the wallet.
* Welcome new customers with a registration credit on signup.
* Manually credit or debit any single customer's wallet from the backend.
* Bulk credit or debit customer balances by importing a CSV file.
* Create cart-total based cashback rules with amount ranges and fixed or percentage rewards.
* Maximum cashback cap, minimum order value and exclude-sale-products controls.
* Cashback messages on shop, product, cart, checkout and order pages.
* A modern My Account wallet page showing balance, today's credit/debit and full transaction history with AJAX pagination.
* Order refunds are credited back to the wallet automatically.
* Dedicated WooCommerce email notifications for wallet credit, debit and manual adjustments, with editable templates.
* Dynamic shortcodes to display wallet balance, top-up and transactions anywhere.
* Admin dashboard with balance, transactions, users and spend analytics.
* Fully compatible with WooCommerce High-Performance Order Storage (HPOS) and the block-based Cart and Checkout.
* Translation ready.

= Pro Features =

Upgrade to [Wallet Management for WooCommerce Pro](https://devdiggers.com/product/woocommerce-wallet-management/) to unlock:

* Partial payments, pay part of an order with the wallet and the rest with another gateway.
* Send and request money between customers (peer-to-peer transfers).
* Wallet withdrawals with admin approval, withdrawal charges and limits.
* OTP email verification for sensitive wallet operations.
* Referral program that rewards both the referrer and the new customer.
* Advanced cashback rules: per product, per category, on top-ups, by user role and by payment gateway.
* First order cashback, cashback expiry and expiry reminder emails.
* Export customer wallet data to CSV.

== Installation ==

1. Upload the `wallet-management-for-woocommerce` folder to the `/wp-content/plugins/` directory, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to **Wallet** in the admin menu and run the setup wizard, or configure it from **Wallet > Configuration**.

This plugin requires WooCommerce to be installed and active.

== Frequently Asked Questions ==

= Does this require WooCommerce? =

Yes. The plugin extends WooCommerce and registers the wallet as a WooCommerce payment gateway.

= How do customers add money to their wallet? =

A hidden "Wallet Topup" product is created on activation. Customers enter an amount on the My Account wallet page and check out to top up their balance.

= Can customers pay for an order partly with the wallet and partly with another method? =

Partial payments are a Pro feature. In Free, customers pay the full order with their wallet balance through the wallet payment gateway.

= Is my existing data safe if I later upgrade to Pro? =

Yes. The Free plugin does not delete any data. If the Pro plugin is active, the Free plugin stops loading so the two never conflict.

== External services ==

This plugin connects to DevDiggers (the plugin author) for the two optional features below. No data leaves your site unless you actively use one of them.

1. DevDiggers newsletter subscription
What it does: If you choose to subscribe from the plugin's onboarding/dashboard, the email address you type and your site URL are sent to DevDiggers to add you to the newsletter list.
When: Only when you submit the subscribe form. No data is sent otherwise.
Service endpoint: https://devdiggers.com/
Terms: https://devdiggers.com/terms-and-conditions/ — Privacy: https://devdiggers.com/privacy-policy/

2. DevDiggers extensions list
What it does: On the plugin's "DevDiggers extensions" admin screen, the plugin requests the public list of DevDiggers plugins so it can display them. No personal data is sent.
When: Only when you open that admin screen.
Service endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins
Terms: https://devdiggers.com/terms-and-conditions/ — Privacy: https://devdiggers.com/privacy-policy/

== Changelog ==

= 1.0.0 =
* Initial free release.

== Upgrade Notice ==

= 1.0.0 =
Initial free release of Wallet Management for WooCommerce.
