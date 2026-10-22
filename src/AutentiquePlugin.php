<?php

namespace JeffersonGoncalves\Filament\Autentique;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource;
use JeffersonGoncalves\Filament\Autentique\Resources\WebhookCallResource;

class AutentiquePlugin implements Plugin
{
    protected string|Closure|null $navigationGroup = null;

    protected bool $webhookCalls = true;

    protected ?string $disk = null;

    protected string $directory = 'autentique';

    public function getId(): string
    {
        return 'filament-autentique';
    }

    public function register(Panel $panel): void
    {
        $panel->resources($this->webhookCalls
            ? [DocumentResource::class, WebhookCallResource::class]
            : [DocumentResource::class]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /** The panel's instance when registered, otherwise the defaults. */
    public static function current(): static
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $id = app(static::class)->getId();

        /** @var static $plugin */
        $plugin = $panel?->hasPlugin($id) ? $panel->getPlugin($id) : static::make();

        return $plugin;
    }

    /** Put the resources in one of the panel's navigation groups. */
    public function navigationGroup(string|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        $group = value($this->navigationGroup);

        return is_string($group) ? $group : null;
    }

    /** Show the "Webhook calls" resource (stored webhooks, with reprocess). On by default. */
    public function webhookCalls(bool $condition = true): static
    {
        $this->webhookCalls = $condition;

        return $this;
    }

    public function hasWebhookCalls(): bool
    {
        return $this->webhookCalls;
    }

    /** Filesystem disk for the uploaded files (null = the panel's default disk). */
    public function disk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk ?? (string) config('filament.default_filesystem_disk', 'local');
    }

    /** Directory of the uploaded files on the disk. */
    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
