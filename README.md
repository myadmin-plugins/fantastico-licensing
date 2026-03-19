# Fantastico Licensing Plugin for MyAdmin

[![Tests](https://github.com/detain/myadmin-fantastico-licensing/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-fantastico-licensing/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-fantastico-licensing/version)](https://packagist.org/packages/detain/myadmin-fantastico-licensing)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-fantastico-licensing/downloads)](https://packagist.org/packages/detain/myadmin-fantastico-licensing)
[![License](https://poser.pugx.org/detain/myadmin-fantastico-licensing/license)](https://packagist.org/packages/detain/myadmin-fantastico-licensing)

A MyAdmin plugin that provides Fantastico licensing integration. Enables purchasing, activation, IP management, and administration of Fantastico server and VPS license types through the MyAdmin platform.

## Features

- Sell Fantastico licenses for dedicated servers and VPS instances
- Activate and reactivate licenses with automatic IP assignment
- Change IP addresses on existing licenses
- Reuse canceled or expired licenses for cost optimization
- Admin interface for license management and reporting
- Integration with Symfony EventDispatcher for hook-based architecture

## Requirements

- PHP >= 5.0
- ext-soap
- Symfony EventDispatcher ^5.0
- detain/fantastico-licensing

## Installation

```sh
composer require detain/myadmin-fantastico-licensing
```

## Usage

The plugin registers event hooks automatically when loaded by the MyAdmin plugin system. It handles:

- `function.requirements` - Registers page and function requirements
- `licenses.settings` - Provides admin settings for Fantastico credentials
- `licenses.activate` / `licenses.reactivate` - Handles license activation
- `licenses.change_ip` - Manages IP address changes
- `ui.menu` - Adds admin menu entries

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

The Fantastico Licensing Plugin is licensed under the LGPL-2.1 license.
