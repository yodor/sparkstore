# sparkstore

The e-commerce layer of [SparkBox](https://github.com/yodor/sparkbox): products, cart, checkout, accounts and the
store admin, on the SparkBox module engine (`lib/store`, the public files in `storefront`).

## A new store site

From the checkouts on the server (sparkbox next to sparkstore), into an empty folder:

```
php sparkstore/tools/deploy.php --title="My Shop" --folder=/home/<user>/sites/myshop \
    --db=<name>:<password> --admin=<email>:<password>
```

The core site of `sparkbox/tools/deploy.php` with the store: the store modules (products, account, checkout),
`config/include.php` and `config/store.php`, the store frame (`StoreFrame` in `class/templates/init.php`), the
`storefront` link, every table of the store beans. `--update --folder=<site root>` brings the SparkBox part of an
existing store site up to date (a dry run without `--write`). See the SparkBox README, "Setting up a site".

## Courier offices (Speedy, Econt)

The checkout office step chooses the office from a local copy of the couriers' office lists (`courier_offices`):
offices only (no lockers), the ones accepting cash. The admin refreshes the lists: Orders > Delivery > Courier
Offices > "Обнови офисите" (a failed answer keeps the old list).

- Econt: public list, no account needed.
- Speedy: the account in the site's `config/speedy.php` (not in git):

```php
<?php
return array("username" => "...", "password" => "...");
```

A store site from before the office lists runs `courier-offices-migration.sql` once (the patch notes).
