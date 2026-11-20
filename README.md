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
