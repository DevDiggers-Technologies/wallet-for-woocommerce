=== Wallet Management for WooCommerce ===
Contributors: devdiggers
Tags: wallet, store credit, cashback, digital wallet, account funds
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.0
WC requires at least: 9.0
WC tested up to: 10.8
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Give your WooCommerce customers a virtual wallet. Let them top up, pay at checkout with their balance, earn cart cashback and track every transaction.

== Description ==

**Wallet Management for WooCommerce** adds a digital wallet to your WooCommerce store. Customers top up their wallet, pay for orders directly with their balance, earn cart based cashback, and review all of their wallet transactions from the My Account page. Store owners get a clean admin dashboard to manage balances, adjust funds, import balances in bulk, and configure cashback.

The wallet works as a standard WooCommerce payment gateway. Once a customer has a balance they can check out in a single click, with no need to re-enter payment details every time.

= Quick Links =

* [View Demo](https://devdiggers.com/product/woocommerce-wallet-management/)
* [Documentation](https://devdiggers.com/woocommerce-wallet-management/)
* [Upgrade to Pro](https://devdiggers.com/product/woocommerce-wallet-management/)
* [Contact Support](https://devdiggers.com/contact/)

= How It Works =

A hidden top-up product is created when you activate the plugin. Customers enter an amount on their My Account wallet page and check out to add funds. After that, the wallet shows up as a payment method at checkout and the balance is used to pay for the order. Every credit and debit is recorded and shown in the customer's transaction history.

=== Wallet Payments And Top-Up ===

Customers fund their wallet and then spend it at checkout like any other payment method.

* Enable or disable the wallet for your whole store.
* Wallet payment gateway, so customers pay for orders with their balance.
* Top-up with configurable minimum and maximum limits and quick preset amounts.
* Choose the order status that confirms a top-up.
* Restrict top-ups to specific payment gateways.
* Fixed or percentage based debit limits for paying with the wallet.

=== Cashback ===

Reward customers with wallet credit based on their cart total.

* Cart-total based cashback rules with amount ranges and fixed or percentage rewards.
* Maximum cashback cap, minimum order value and exclude-sale-products controls.
* Cashback messages on shop, product, cart, checkout and order pages.

=== Customer Wallet Page ===

Customers manage everything from a wallet tab inside WooCommerce My Account.

* Balance, today's credit and debit, and full transaction history with AJAX pagination.
* Dynamic shortcodes to display wallet balance, top-up and transactions on any page.
* Order refunds are credited back to the wallet automatically.

=== Admin Management ===

Store owners control balances and settings from the WordPress admin.

* Welcome new customers with a registration credit on signup.
* Manually credit or debit any single customer's wallet from the backend.
* Bulk credit or debit customer balances by importing a CSV file.
* Setup wizard for a quick start, plus a Configuration screen for full control.
* Admin dashboard with balance, transactions, users and spend overview.

=== Emails And Compatibility ===

* WooCommerce email notifications for wallet credit, debit and manual adjustments, with editable templates.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* Works with the block-based Cart and Checkout.
* Translation ready.

= Free vs Pro =

The free plugin runs the full wallet workflow: top-up, wallet checkout, cart cashback, manual and bulk adjustments, transactions and refunds.

Upgrade to [Wallet Management for WooCommerce Pro](https://devdiggers.com/product/woocommerce-wallet-management/) to add:

* Partial payments, so part of an order is paid with the wallet and the rest with another gateway.
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

= Does it work with HPOS and block checkout? =

Yes. The plugin declares compatibility with High-Performance Order Storage and registers a payment method for the block-based Cart and Checkout.

= Which shortcodes are included? =

The plugin registers shortcodes for the wallet balance, top-up form and transaction list. The shortcode tags are set on the wallet settings screen, so you can place wallet content on any page.

= Is my existing data safe if I later upgrade to Pro? =

Yes. The Free plugin does not delete any data. If the Pro plugin is active, the Free plugin stops loading so the two never conflict.

= Where can I get help? =

Use the [DevDiggers contact page](https://devdiggers.com/contact/) or the plugin support forum on WordPress.org.

== External services ==

This plugin connects to DevDiggers (the plugin author) for the two optional features below. No data leaves your site unless you actively use one of them.

1. DevDiggers newsletter subscription
What it does: If you choose to subscribe from the plugin's onboarding/dashboard, the email address you type and your site URL are sent to DevDiggers to add you to the newsletter list.
When: Only when you submit the subscribe form. No data is sent otherwise.
Service endpoint: https://devdiggers.com/
Terms: https://devdiggers.com/terms-and-conditions/ | Privacy: https://devdiggers.com/privacy-policy/

2. DevDiggers extensions list
What it does: On the plugin's "DevDiggers extensions" admin screen, the plugin requests the public list of DevDiggers plugins so it can display them. No personal data is sent.
When: Only when you open that admin screen.
Service endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins
Terms: https://devdiggers.com/terms-and-conditions/ | Privacy: https://devdiggers.com/privacy-policy/

== Source code and build tools ==

The admin and frontend JavaScript and CSS are compiled from the `src/` folder with webpack and Babel. The uncompiled source ships inside the plugin zip in the `src/` directory.

To build the assets from source:

1. Run `npm install` to install the build dependencies listed in `package.json`.
2. Run `npm run build` to compile the production assets into `assets/js` and `assets/css`.

The build configuration is in `webpack.config.js` and `babel.config.js`.

== Changelog ==

= 1.0.0 =
* Initial free release.

== Upgrade Notice ==

= 1.0.0 =
Initial free release of Wallet Management for WooCommerce.
