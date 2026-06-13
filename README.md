# Wallet Management for WooCommerce

**Contributors:** DevDiggers

**Tags:** wallet, store credit, cashback, digital wallet, account funds

**WordPress**
  * `Requires at least: 6.2`
  * `Tested up to: 7.0`

**WooCommerce**
  * `Requires at least: 9.0`
  * `Tested up to: 10.8`

**PHP**
  * `Requires PHP: 7.0`

**Stable tag:** `1.0.0`

**License:** GPLv3 or later

## Description

Wallet Management for WooCommerce adds a digital wallet to your WooCommerce store. Customers top up their wallet, pay for orders directly with their balance, earn cart based cashback, and track every transaction from the My Account page. Store owners manage balances, adjust funds, bulk-import balances, and configure cashback from a clean admin dashboard.

The wallet works as a standard WooCommerce payment gateway, so once a customer has a balance they can check out in a single click.

## How It Works

A hidden top-up product is created on activation. Customers enter an amount on their My Account wallet page and check out to add funds. The wallet then appears as a payment method at checkout, and the balance pays for the order. Every credit and debit is recorded in the customer's transaction history.

## Free Features

**Payments and top-up**
* Enable or disable the wallet across the whole store.
* Wallet payment gateway so customers pay for orders with their balance.
* Top-up with min/max limits and quick preset amounts.
* Configurable top-up confirmation order status.
* Restrict top-ups to specific payment gateways.
* Fixed or percentage based debit limits.

**Cashback**
* Cart-total cashback rules with amount ranges (fixed or percentage).
* Maximum cap, minimum order value, and exclude-sale-products controls.
* Cashback messages on shop, product, cart, checkout and order pages.

**Customer wallet page**
* My Account wallet page with balance, today's credit/debit and AJAX-paginated transactions.
* Shortcodes for wallet balance, top-up and transactions (tags set in settings).
* Order refunds credited back to the wallet automatically.

**Admin management**
* Registration credit for new customers on signup.
* Manual credit/debit for any single customer.
* Bulk credit/debit via CSV import.
* Setup wizard and a full Configuration screen.
* Dashboard with balance, transactions, users and spend overview.

**Emails and compatibility**
* WooCommerce email notifications for credit, debit and manual adjustments, with editable templates.
* Compatible with WooCommerce HPOS and the block-based Cart and Checkout.
* Translation ready.

## Pro Features

Upgrade to [Wallet Management for WooCommerce Pro](https://devdiggers.com/product/woocommerce-wallet-management/) to add:

* Partial payments (wallet plus another gateway on one order).
* Send and request money between customers (peer-to-peer transfers).
* Wallet withdrawals with admin approval, charges and limits.
* OTP email verification for sensitive wallet operations.
* Referral program rewarding both the referrer and the new customer.
* Advanced cashback: per product, per category, on top-ups, by user role and by payment gateway.
* First-order cashback, cashback expiry and expiry reminder emails.
* Export customer wallet data to CSV.

## Installation

1. Upload the `wallet-management-for-woocommerce` folder to `/wp-content/plugins/`, or install it from the Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open the **Wallet** menu and run the setup wizard, or configure it from **Wallet > Configuration**.

Requires WooCommerce to be installed and active.

## Build From Source

The admin and frontend assets are compiled from `src/` with webpack and Babel.

```bash
npm install
npm run build
```

Compiled output lands in `assets/js` and `assets/css`. Build config lives in `webpack.config.js` and `babel.config.js`.

## Frequently Asked Questions

**Does this require WooCommerce?**
Yes. The plugin registers the wallet as a WooCommerce payment gateway.

**How do customers add money to their wallet?**
A hidden "Wallet Topup" product is created on activation. Customers enter an amount on the My Account wallet page and check out to top up.

**Does it work with HPOS and block checkout?**
Yes. It declares HPOS compatibility and registers a payment method for the block-based Cart and Checkout.

**Is my data safe if I later upgrade to Pro?**
Yes. The Free plugin never deletes data, and it stops loading when the Pro plugin is active so the two never conflict.

## Support

For questions, contact [DevDiggers](https://devdiggers.com/contact/) or email support@devdiggers.com.

## Changelog

**1.0.0**
* Initial free release.
