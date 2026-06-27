=== DevDiggers Wallet for WooCommerce ===
Contributors: devdiggers
Tags: woocommerce wallet, cashback, store credit, digital wallet, wallet payment
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
WC requires at least: 9.0
WC tested up to: 10.9.1
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WooCommerce wallet for store credit, cashback and top-ups. Customers pay at checkout with their balance and track every transaction.

== Description ==

**DevDiggers Wallet for WooCommerce** gives every customer a wallet they can top up, spend at checkout, and track from their account. The wallet works as a normal WooCommerce payment gateway, so once a customer has a balance they pay for an order in one click without re-entering card details.

Store owners run the whole thing from the WordPress admin. You can credit or debit any customer, import balances in bulk from a CSV, set up cart based cashback, and watch balances and transactions from one dashboard. Refunds go straight back to the wallet, so a returned order does not turn into a manual payout.

This is a good fit for stores that want store credit, a loyalty style cashback program, prepaid balances, refunds without gateway fees, or a faster repeat checkout for regular buyers.

= Why store owners use it =

* Repeat customers check out faster because the balance is already there.
* Refunds become wallet credit, which keeps money in the store instead of going back to the card.
* Cashback runs on the cart total, so you reward bigger orders without coupon codes.
* Everything lives in WooCommerce, so balances, orders and refunds stay connected.

= How it works =

A hidden top-up product is created when you activate the plugin. A customer opens the wallet page in My Account, enters an amount, and checks out to add funds. From then on the wallet appears as a payment method at checkout and the balance pays for the order. Every credit and debit is written to the transaction history the customer can see.

= Wallet payments and top-up =

Customers fund the wallet, then spend it at checkout like any other payment method.

* Turn the wallet on or off for the whole store.
* Wallet payment gateway so customers pay for orders with their balance.
* Top-up with minimum and maximum limits and quick preset amounts.
* Pick the order status that confirms a top-up.
* Limit top-ups to specific payment gateways.
* Fixed or percentage debit limits for paying with the wallet.

= Cashback =

Reward customers with wallet credit based on what they spend.

* Cart total cashback rules with amount ranges and fixed or percentage rewards.
* Maximum cashback cap, minimum order value, and an option to skip sale products.
* Cashback messages on shop, product, cart, checkout and order pages.

= Customer wallet page =

Customers manage the wallet from a tab inside WooCommerce My Account.

* Balance, today's credit and debit, and full transaction history with AJAX pagination.
* Shortcodes for the wallet balance, top-up form and transactions, so you can place them on any page.
* Order refunds are credited back to the wallet automatically.

= Admin management =

Store owners control balances and settings from the WordPress admin.

* Give new customers a registration credit on signup.
* Credit or debit any single customer's wallet by hand.
* Credit or debit many customers at once by importing a CSV file.
* A setup wizard for a quick start, plus a Configuration screen for the details.
* A dashboard with balance, transactions, users and spend at a glance.

= Emails and compatibility =

* WooCommerce email notifications for wallet credit, debit and manual adjustments, with editable templates.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* Works with the block based Cart and Checkout.
* Translation ready.

= Free vs Pro =

The free plugin runs the complete wallet workflow: top-up, wallet checkout, cart cashback, manual and bulk adjustments, transaction history, refunds to wallet, and the customer wallet page. Most stores can launch on the free version alone.

[DevDiggers Wallet for WooCommerce Pro](https://devdiggers.com/product/woocommerce-wallet-management/) is for stores that want flexible payments, peer to peer money, payouts, and a referral and cashback rewards program.

=== Flexible payments (Pro) ===

* Partial payments, so a customer pays part of an order from the wallet and the rest with another gateway.
* Send and request money between customers as peer to peer transfers.

=== Withdrawals and security (Pro) ===

* Wallet withdrawals with admin approval, withdrawal charges and limits.
* OTP email verification for sensitive wallet actions.

=== Rewards and growth (Pro) ===

* Referral program that rewards both the referrer and the new customer.
* Advanced cashback by product, category, top-up, user role and payment gateway.
* First order cashback, cashback expiry, and expiry reminder emails.

=== Data (Pro) ===

* Export customer wallet data to CSV.

[View Demo](https://devdiggers.com/product/woocommerce-wallet-management/) | [Documentation](https://devdiggers.com/woocommerce-wallet-management/) | [Upgrade to Pro](https://devdiggers.com/product/woocommerce-wallet-management/) | [Support](https://devdiggers.com/contact/)

== Installation ==

1. Upload the `devdiggers-wallet-for-woocommerce` folder to the `/wp-content/plugins/` directory, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to **Wallet** in the admin menu and run the setup wizard, or configure it from **Wallet > Configuration**.

This plugin needs WooCommerce installed and active.

== Frequently Asked Questions ==

= Does this require WooCommerce? =

Yes. The plugin extends WooCommerce and registers the wallet as a WooCommerce payment gateway.

= How do customers add money to their wallet? =

A hidden "Wallet Topup" product is created on activation. Customers enter an amount on the My Account wallet page and check out to top up their balance.

= Can customers pay for part of an order with the wallet? =

Partial payments are a Pro feature. In the free version, a customer pays the full order with the wallet balance through the wallet payment gateway.

= Does it work with HPOS and block checkout? =

Yes. The plugin declares High-Performance Order Storage compatibility and registers a payment method for the block based Cart and Checkout.

= Are refunds returned to the wallet? =

Yes. When you refund a WooCommerce order, the amount is credited back to the customer's wallet automatically.

= Which shortcodes are included? =

The plugin registers shortcodes for the wallet balance, top-up form and transaction list. You set the shortcode tags on the wallet settings screen and place them on any page.

= Is my data safe if I upgrade to Pro later? =

Yes. The free plugin does not delete any data. When the Pro plugin is active, the free plugin stops loading so the two never conflict.

= Where can I get help? =

Use the [DevDiggers contact page](https://devdiggers.com/contact/) or the plugin support forum on WordPress.org.

== External services ==

This plugin relies on the following third-party services provided by DevDiggers (https://devdiggers.com). These connections only happen inside the WordPress admin area and are described below so you know exactly what is sent, why, and when.

**1. DevDiggers extensions directory**

* What it is: A read-only API on devdiggers.com that returns the public list of DevDiggers WooCommerce extensions.
* What it is used for: To display available DevDiggers extensions on the plugin's "Extensions" admin page.
* When data is sent: Only when a logged-in administrator opens the "Extensions" admin page. The response is cached for 24 hours, so the request is not repeated on every page load.
* What data is sent: A standard outbound HTTP request only (your server's IP address and a plugin user-agent string, as with any web request). No personal data and no store data are sent.
* Endpoint: https://devdiggers.com/wp-json/ddwcs/v1/plugins

**2. Newsletter subscription (optional)**

* What it is: A contact/newsletter endpoint on devdiggers.com.
* What it is used for: To add your email address to the DevDiggers newsletter, only if you explicitly choose to subscribe.
* When data is sent: Only when an administrator submits the optional newsletter form in the plugin dashboard. Nothing is sent automatically.
* What data is sent: The email address you enter and your site URL.
* Endpoint: https://devdiggers.com/

These services are provided by DevDiggers. By using them you agree to the DevDiggers Terms and Conditions (https://devdiggers.com/terms-and-conditions/) and Privacy Policy (https://devdiggers.com/privacy-policy/).

== Source code and build tools ==

The admin and frontend JavaScript and CSS are compiled from the `src/` folder with webpack and Babel. The complete, human-readable source code (admin, frontend, and all build configuration) is published in our public GitHub repository:

https://github.com/DevDiggers-Technologies/wallet-for-woocommerce

To build the assets from source:

1. Run `npm install` to install the build dependencies listed in `package.json`.
2. Run `npm run build` to compile the production assets into `assets/js` and `assets/css`.

The build configuration is in `webpack.config.js` and `babel.config.js`.

== Changelog ==

= 1.0.0 =
* Initial free release.
