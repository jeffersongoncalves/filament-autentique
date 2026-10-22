<div class="filament-hidden">

![Filament Autentique](https://raw.githubusercontent.com/jeffersongoncalves/filament-autentique/2.x/art/jeffersongoncalves-filament-autentique.png)

</div>

# Filament Autentique

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-autentique.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-autentique)
[![PHP Version](https://img.shields.io/packagist/dependency-v/jeffersongoncalves/filament-autentique/php.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-autentique)
[![Tests](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-autentique/tests.yml?branch=2.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-autentique/actions?query=workflow%3ATests+branch%3A2.x)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-autentique.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-autentique)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-autentique.svg?style=flat-square)](LICENSE.md)

Send documents for **e-signature with [Autentique](https://autentique.com.br)** from your Filament panel and follow every signer until the document is done.

Built on [jeffersongoncalves/laravel-autentique](https://github.com/jeffersongoncalves/laravel-autentique), which holds the API client (rate limited to Autentique's 60 requests/minute), the database tables, the queued jobs and the signed webhook route.

## Features

- **Send document** — upload a file, add signers (email, or a name for a signature link) with their action (sign, sign as a witness, approve, acknowledge), message, reject/order options. The upload runs on the queue (`CreateDocument`), so the rate limit never blocks the panel.
- **Documents** — status (pending, signed, rejected), signed/total counter, sandbox flag, linked model, filters by status and sandbox.
- **Signers** — viewed/signed/rejected times, copyable signature links, **resend** the request and **create a link** for open signers.
- **Signed PDF** download, **sync** from the API, and **delete** (at Autentique first, then locally).
- **Webhook calls** — every webhook received, its payload, errors and a **reprocess** action.
- **19 languages**, Filament 3, 4 and 5.

## Compatibility

| Branch | Filament | Package version |
|--------|----------|-----------------|
| 1.x | 3.x | `^1.0` |
| 2.x | 4.x | `^2.0` |
| 3.x | 5.x | `^3.0` |

## Installation

```bash
composer require jeffersongoncalves/filament-autentique:"^2.0"
php artisan vendor:publish --tag="autentique-config"
php artisan vendor:publish --tag="autentique-migrations"
php artisan migrate
```

Configure the API token (and the webhook secret to receive status updates) — see [laravel-autentique](https://github.com/jeffersongoncalves/laravel-autentique#installation):

```dotenv
AUTENTIQUE_TOKEN=your-token
AUTENTIQUE_WEBHOOK_SECRET=your-webhook-secret
```

Run a queue worker: sending, syncing and webhook processing are queued jobs.

Register the plugin in your panel provider:

```php
use JeffersonGoncalves\Filament\Autentique\AutentiquePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AutentiquePlugin::make()
                // ->navigationGroup('Contracts')
                // ->disk('s3')               // where uploads are stored (default: the panel's default disk)
                // ->directory('autentique')  // directory on that disk
                // ->webhookCalls(false)      // hide the "Webhook calls" resource
                ,
        ]);
}
```

## Usage

- **Documents → Send document** queues the upload. The document shows up in the list once Autentique has received it, and its status follows the webhooks (`document.*`, `signature.*`).
- Without webhooks, use **Sync** on a document (or dispatch `SyncDocument` on a schedule) to refresh it from the API.
- To link documents to your own models, dispatch `CreateDocument` with `documentable:` from your code (see laravel-autentique); the document page shows the linked record.

Access follows your policies for `JeffersonGoncalves\Autentique\Models\Document` and `WebhookCall`, like any Filament resource.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
