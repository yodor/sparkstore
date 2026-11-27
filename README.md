# sparkstore

The e-commerce layer of [SparkBox](https://github.com/yodor/sparkbox): products, cart, checkout, accounts and the
store admin, on the SparkBox module engine (`lib/store`, the public files in `storefront`).

## A new store site

From the checkouts on the server (sparkbox next to sparkstore), into an empty folder:

```
php sparkstore/tools/deploy.php --title="My Shop" --folder=/home/<user>/sites/myshop \
    --db=<name>:<password> --admin=<email>:<password>
```

The core site of `sparkbox/tools/deploy.php` with the store: the products module, the store features
(`--features=shop,fast_order` - the default; `--features=` a catalog), `config/include.php` and `config/store.php`, the store frame (`StoreFrame` in `class/templates/init.php`), the
`storefront` link, every table of the store beans. `--update --folder=<site root>` brings the SparkBox part of an
existing store site up to date (a dry run without `--write`). See the SparkBox README, "Setting up a site".

## Features

The catalog (products, the product page, admin Store) is the store layer itself. The rest are features a site
enables in `config/include.php` after `include_once("sparkstore.php")` (see "Features" in the SparkBox
`docs/module-engine.md`):

- `shop` (`lib/store/features/shop`): the checkout and the account modules, admin Orders (the delivery settings,
  the courier offices) and Clients, the order and account mails. The data model (orders, cart, delivery, couriers)
  stays in the store library.
- `fast_order` (`lib/store/features/fast_order`): the Fast Order button of the product page - a name and a phone
  mailed to the shop, no stored order; the checkout guest page offers it too.

```php
Features::Enable("shop", "fast_order");   // a shop
Features::Enable("fast_order");           // a catalog with fast orders
                                          // nothing: a catalog
```

## Catalog parts

Brands, sections, classes (with their attributes) and variants (options) are optional parts of the product data, all
on by default. A site switches off the ones it does not use in its site init (`class/templates/init.php`):

```php
CatalogParts::Disable(CatalogParts::BRANDS, CatalogParts::VARIANTS);   // the bookshop
```

A disabled part leaves the admin as a whole: its pages answer 404 and leave the menu, the products list has none of
its columns, filters, search columns or row actions, the product editor none of its inputs. A site still chooses and
orders the list columns with `listFields` - columns of a disabled part are dropped. The storefront follows the data.
`lib/store/utils/CatalogParts.php` lists what each part covers.

## Courier offices (Speedy, Econt)

The checkout office step chooses the office from a local copy of the couriers' office lists (`courier_offices`):
offices only (no lockers), the ones accepting cash. The admin refreshes the lists: Orders > Delivery > Courier
Offices > "Обнови офисите" (a failed answer keeps the old list).

- Econt: public list, no account needed.
- Speedy: the API account in Orders > Delivery (or, until it is set there, the site's `config/speedy.php` - not in git):

```php
<?php
return array("username" => "...", "password" => "...");
```

A store site from before the office lists runs `courier-offices-migration.sql` once (the patch notes).
