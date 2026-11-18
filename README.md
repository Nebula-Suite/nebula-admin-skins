# Nebula Admin Skins

Thirteen extra colour schemes for the Nebula admin theme. Nebula itself ships six skins (Slate, Indigo, Tokyo Night, High Contrast and the two colour blind presets). The rest live here, so a shop installs only the ones it wants.

| Id | Label | Extra sections |
| --- | --- | --- |
| `amber-large` | Amber Large | density |
| `amber` | Amber |  |
| `dracula` | Dracula |  |
| `emerald` | Emerald |  |
| `flat` | Flat (No Radius) | density |
| `indigo-compact` | Indigo Compact | density |
| `magento-classic` | Magento Classic (2.4 Admin) |  |
| `matrix` | Matrix | density, shadows, transitions |
| `nord` | Nord (Arctic) |  |
| `nostalgia` | Nostalgia (Magento 1) |  |
| `qoliber-fire` | Qoliber Fire 🔥 |  |
| `rose` | Rose |  |
| `siteation` | Siteation |  |

## Requirements

Nebula admin 1.3 or later (`nebula/module-skin` ^1.3) on Magento Open Source or MageOS, PHP 8.3, 8.4 or 8.5.

## Install all thirteen with Composer

```sh
composer config repositories.nebula-admin-skins vcs https://github.com/Nebula-Suite/nebula-admin-skins
composer require nebula/module-admin-skins
php bin/magento setup:upgrade
php bin/magento cache:clean config
```

The skins then appear in the Color Scheme dropdown under Stores › Configuration › Nebula › Nebula Admin Theme › Settings. To pick one from the command line, use its id:

```sh
php bin/magento config:set nebula/theme/skin dracula
php bin/magento cache:clean config
```

Set the skin at Default Config. Nebula reads it at default scope.

## Install a single skin

A skin is one JSON file, and Nebula picks up skin files from every enabled module. To use just one, copy its file from `view/adminhtml/nebula/skin/` into the same directory of a module of your own (for example `app/code/Vendor/AdminSkin/view/adminhtml/nebula/skin/nord.json`), then run `setup:upgrade` if the module is new and clean the config cache.

## Removing the package

```sh
php bin/magento config:set nebula/theme/skin slate
composer remove nebula/module-admin-skins
php bin/magento setup:upgrade
```

Setting Slate first is tidy but not required. When the saved skin is no longer installed, Nebula falls back to Slate on its own.

## Primary fills

Four skins nudge their primary button fill from the published palette so the label clears 4.5:1 (WCAG AA for normal text). Nebula's own skins keep no exceptions, and neither do these.

| Skin | Mode | Fill | Was |
| --- | --- | --- | --- |
| `flat` | dark | `#6063f1` | `#6366f1` (4.47:1) |
| `indigo-compact` | dark | `#6063f1` | `#6366f1` (4.47:1) |
| `nord` | light | `#5477a3` | `#5e81ac` (4.40:1) |
| `nostalgia` | light and dark | `#cf4704` to `#a83c04` | `#f65405` to `#c34706` |

When you copy one of these skins into your own module, keep the adjusted fill, or measure the pair again.

## Writing your own

The file format, the sections and dark mode are documented on the Create your own skin page of the Nebula documentation (Theming › Create your own skin). Any file here is a working example to start from.

## Tests

`Test/Unit/SkinContrastTest.php` holds these skins to the same 4.5:1 checks Nebula runs on its own: grid row text, the Yes and No badge labels, and the label on the primary fill, in light and dark mode. Run it from a Magento install that has this package:

```sh
vendor/bin/phpunit -c vendor/nebula/module-admin-skins/phpunit.xml.dist
```

## License

Distributed under the [Nebula Community License (NCL) v1.0](LICENSE.md). Composer uses `proprietary` because NCL has no registered SPDX identifier.
