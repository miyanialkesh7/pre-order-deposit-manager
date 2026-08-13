# Pre-Order Deposit Manager for WooCommerce

A WooCommerce extension that lets customers pay a 30% deposit on pre-order products and settle the remaining 70% once the item is ready for delivery — either automatically through a saved card or manually by the store admin.

## Features

- Enable "Pre-Order Deposit" on any simple or variable product
- Automatic 30% deposit calculation shown on the product page, cart, and checkout
- Custom order statuses: Deposit Paid, Ready for Delivery, Completed
- Order admin panel showing full total, deposit paid, and remaining balance
- One-click "Mark Ready & Notify" to email the customer when the product is ready
- Optional automatic charging of the remaining balance using the customer's saved payment token (Stripe and other token-compatible gateways)
- Manual fallback if no saved card is available
- Custom order list column highlighting pre-order status and remaining balance
- Branded HTML + plain-text email notification when the order is ready

## Requirements

- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+

## Installation

1. Download the plugin as a ZIP or clone this repository into `wp-content/plugins/`.
2. Activate **Pre-Order Deposit Manager** from the Plugins screen in wp-admin.
3. Edit any product, open the **Product data** panel, and check **Pre-Order Deposit**.
4. Save the product — the deposit pricing will appear automatically on the front end.

## How it works

1. A customer buys a pre-order product and pays only 30% of the price at checkout.
2. The order is placed in the **Pre-Order: Deposit Paid** status.
3. When the product is ready, the admin clicks **Mark Ready & Notify** on the order screen.
4. The customer receives an email letting them know the remaining balance is due.
5. The admin can trigger **Auto-Charge Remainder** (if a saved card is available) or collect the remaining payment manually, then mark the order completed.

## Folder structure

```
pre-order-deposit-manager/
├── wc-preorder-deposit.php
├── includes/
│   ├── class-wcpd-product.php
│   ├── class-wcpd-cart.php
│   ├── class-wcpd-order.php
│   ├── class-wcpd-admin.php
│   ├── class-wcpd-payment.php
│   └── class-wcpd-emails.php
├── assets/
│   ├── admin.css
│   ├── admin.js
│   ├── public.css
│   └── public.js
└── templates/
    └── emails/
        ├── preorder-ready.php
        └── plain/
            └── preorder-ready.php
```

## License

GPLv2 or later, same as WordPress and WooCommerce.
