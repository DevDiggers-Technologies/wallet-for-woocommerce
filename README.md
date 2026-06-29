# DevDiggers Wallet for WooCommerce

**Contributors:** DevDiggers

**Tags:** woocommerce wallet, cashback, store credit, digital wallet, wallet payment

**WordPress**
  * `Requires at least: 6.2`
  * `Tested up to: 7.0`

**WooCommerce**
  * `Requires at least: 9.0`
  * `Tested up to: 10.8.1`

**PHP**
  * `Requires PHP: 7.0`

**Stable tag:** `1.0.0`

**License:** GPLv3 or later

## Description

DevDiggers Wallet for WooCommerce gives every customer a wallet they can top up, spend at checkout, and track from their account. The wallet works as a normal WooCommerce payment gateway, so once a customer has a balance they pay for an order in one click.

Store owners run it from the WordPress admin. You can credit or debit any customer, import balances in bulk from a CSV, set up cart based cashback, and watch balances and transactions from one dashboard. Refunds go back to the wallet, so a returned order does not turn into a manual payout.

It fits stores that want store credit, a cashback program, prepaid balances, refunds without gateway fees, or a faster repeat checkout.

## How It Works

A hidden top-up product is created on activation. A customer opens the wallet page in My Account, enters an amount, and checks out to add funds. The wallet then appears as a payment method at checkout, and the balance pays for the order. Every credit and debit is written to the customer's transaction history.

## Free Features

**Payments and top-up**
* Turn the wallet on or off for the whole store.
* Wallet payment gateway so customers pay for orders with their balance.
* Top-up with min/max limits and quick preset amounts.
* Pick the order status that confirms a top-up.
* Limit top-ups to specific payment gateways.
* Fixed or percentage debit limits.

**Cashback**
* Cart total cashback rules with amount ranges (fixed or percentage).
* Maximum cap, minimum order value, and skip-sale-products option.
* Cashback messages on the cart, checkout, view order and order received pages.

**Customer wallet page**
* My Account wallet page with balance, today's credit/debit and AJAX paginated transactions.
* Shortcodes for wallet balance, top-up and transactions (tags set in settings).
* Send money to another customer straight from the wallet (peer to peer transfer).
* Order refunds credited back to the wallet automatically.

**Admin management**
* Registration credit for new customers on signup.
* Manual credit/debit for any single customer.
* Bulk credit/debit via CSV import.
* Setup wizard and a full Configuration screen.
* Dashboard with balance, transactions, users and spend.

**Emails and compatibility**
* WooCommerce email notifications for credit, debit and manual adjustments, with editable templates.
* Compatible with WooCommerce HPOS and the block based Cart and Checkout.
* Translation ready.

## Free vs Pro

| Capability | Free | Pro |
| --- | :---: | :---: |
| Wallet top-up and wallet checkout | Yes | Yes |
| Cart total cashback | Yes | Yes |
| Manual and bulk (CSV) adjustments | Yes | Yes |
| Transaction history and refunds to wallet | Yes | Yes |
| Wallet emails, HPOS, block checkout | Yes | Yes |
| Send money to another customer (peer to peer) | Yes | Yes |
| Partial payment (wallet plus gateway) | No | Yes |
| Request money from another customer | No | Yes |
| Wallet withdrawals with approval, charges, limits | No | Yes |
| OTP email verification | No | Yes |
| Referral program | No | Yes |
| Advanced cashback (product, category, role, gateway, top-up) | No | Yes |
| First order cashback, cashback expiry and reminders | No | Yes |
| Export wallet data to CSV | No | Yes |

[Upgrade to DevDiggers Wallet for WooCommerce Pro](https://devdiggers.com/product/woocommerce-wallet-management/)

## Installation

1. Upload the `devdiggers-wallet-for-woocommerce` folder to `/wp-content/plugins/`, or install it from the Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open the **Wallet** menu and run the setup wizard, or configure it from **Wallet > Configuration**.

Requires WooCommerce installed and active.

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
Yes. It declares HPOS compatibility and registers a payment method for the block based Cart and Checkout.

**Are refunds returned to the wallet?**
Yes. Refunding a WooCommerce order credits the amount back to the customer's wallet automatically.

**Is my data safe if I upgrade to Pro later?**
Yes. The free plugin never deletes data, and it stops loading when the Pro plugin is active so the two never conflict.

## Support

For questions, contact [DevDiggers](https://devdiggers.com/contact/) or email support@devdiggers.com.

## Changelog

**1.0.0**
* Initial free release.
