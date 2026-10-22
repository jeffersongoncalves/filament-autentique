## Filament Autentique

Filament resources for Autentique e-signatures (documents, signers, webhook calls) on top of jeffersongoncalves/laravel-autentique.

### Installation

@verbatim
<code-snippet name="Install the plugin" lang="bash">
composer require jeffersongoncalves/filament-autentique:"^2.0"
php artisan vendor:publish --tag="autentique-migrations"
php artisan migrate
</code-snippet>
@endverbatim

### Register Plugin

@verbatim
<code-snippet name="Register in PanelProvider" lang="php">
use JeffersonGoncalves\Filament\Autentique\AutentiquePlugin;

$panel->plugins([
    AutentiquePlugin::make()
        // ->navigationGroup('Contracts')
        // ->disk('s3')
        // ->webhookCalls(false)
        ,
]);
</code-snippet>
@endverbatim

### Architecture
- `DocumentResource` (list + view, signers relation manager) and `WebhookCallResource` over the laravel-autentique models
- "Send document" dispatches `JeffersonGoncalves\Autentique\Jobs\CreateDocument`; "Sync" dispatches `SyncDocument`; delete calls the API before the local soft delete
- Translations under `filament-autentique::autentique.*`

### Best Practices
- Run a queue worker and configure `AUTENTIQUE_WEBHOOK_SECRET` so statuses update without manual syncs
- Use a private disk for the uploaded documents
- Restrict access with policies on `Document` and `WebhookCall`
