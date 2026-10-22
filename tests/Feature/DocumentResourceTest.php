<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use JeffersonGoncalves\Autentique\Jobs\CreateDocument;
use JeffersonGoncalves\Autentique\Jobs\ProcessWebhookCall;
use JeffersonGoncalves\Autentique\Jobs\SyncDocument;
use JeffersonGoncalves\Autentique\Models\Document;
use JeffersonGoncalves\Autentique\Models\Signature;
use JeffersonGoncalves\Autentique\Models\WebhookCall;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages\ListDocuments;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages\ViewDocument;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\RelationManagers\SignaturesRelationManager;
use JeffersonGoncalves\Filament\Autentique\Resources\WebhookCallResource\Pages\ListWebhookCalls;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('test'));

    $this->actingAs(User::query()->forceCreate(['name' => 'Ana']));

    $this->document = Document::sync([
        'id' => 'doc-1',
        'name' => 'Contract',
        'files' => ['original' => 'https://files/original.pdf', 'signed' => 'https://files/signed.pdf'],
        'signatures' => [
            ['public_id' => 'sig-1', 'email' => 'ana@example.com', 'action' => ['name' => 'SIGN'], 'signed' => ['created_at' => '2025-01-01 10:00:00']],
            ['public_id' => 'sig-2', 'email' => 'bia@example.com', 'action' => ['name' => 'SIGN'], 'signed' => null],
        ],
    ]);
});

it('lists documents and filters by status', function () {
    $signed = Document::sync(['id' => 'doc-2', 'name' => 'Signed one', 'signatures' => [['public_id' => 'sig-3', 'signed' => ['created_at' => '2025-01-01 10:00:00']]]]);

    Livewire::test(ListDocuments::class)
        ->assertCanSeeTableRecords([$this->document, $signed])
        ->filterTable('status', Document::STATUS_SIGNED)
        ->assertCanSeeTableRecords([$signed])
        ->assertCanNotSeeTableRecords([$this->document]);
});

it('queues the upload from the send action', function () {
    Storage::fake('local');
    Queue::fake([CreateDocument::class]);

    // Filament 3 keys repeater items by UUID and merges action data into the default item: set the items directly.
    Livewire::test(ListDocuments::class)
        ->mountAction('send')
        ->set('mountedActionsData.0.signers', [
            'a' => ['email' => 'client@example.com', 'name' => null, 'action' => 'SIGN'],
            'b' => ['email' => null, 'name' => 'Witness', 'action' => 'SIGN_AS_A_WITNESS'],
        ])
        ->setActionData([
            'name' => 'Service contract',
            'file' => UploadedFile::fake()->create('contract.pdf', 10, 'application/pdf'),
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    Queue::assertPushed(CreateDocument::class, fn (CreateDocument $job) => $job->document['name'] === 'Service contract'
        && $job->disk === 'local'
        && Storage::disk('local')->exists($job->path)
        && $job->signers === [['email' => 'client@example.com', 'action' => 'SIGN'], ['name' => 'Witness', 'action' => 'SIGN_AS_A_WITNESS']]);
});

it('requires an email or a name for each signer', function () {
    Storage::fake('local');
    Queue::fake([CreateDocument::class]);

    Livewire::test(ListDocuments::class)
        ->mountAction('send')
        ->set('mountedActionsData.0.signers', ['a' => ['email' => null, 'name' => null, 'action' => 'SIGN']])
        ->setActionData([
            'name' => 'Service contract',
            'file' => UploadedFile::fake()->create('contract.pdf', 10, 'application/pdf'),
        ])
        ->callMountedAction()
        ->assertHasActionErrors(['signers.a.email' => 'required_without']);

    Queue::assertNothingPushed();
});

it('queues a sync from the table', function () {
    Queue::fake([SyncDocument::class]);

    Livewire::test(ListDocuments::class)->callTableAction('sync', $this->document);

    Queue::assertPushed(SyncDocument::class, fn (SyncDocument $job) => $job->autentiqueId === 'doc-1');
});

it('deletes at Autentique before deleting locally', function () {
    Http::fake(['*' => Http::response(['data' => ['deleteDocument' => true]])]);

    Livewire::test(ViewDocument::class, ['record' => $this->document->getRouteKey()])->callAction('delete');

    Http::assertSent(fn (Request $request) => str_contains($request['query'], 'deleteDocument(id: "doc-1")'));
    expect($this->document->fresh()->trashed())->toBeTrue();
});

it('keeps the document when Autentique refuses the delete', function () {
    Http::fake(['*' => Http::response(['errors' => [['message' => 'Document not found']], 'data' => ['deleteDocument' => null]])]);

    Livewire::test(ListDocuments::class)->callTableAction('delete', $this->document);

    expect($this->document->fresh()->trashed())->toBeFalse();
});

it('resends and creates links for open signatures', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['data' => ['resendSignatures' => true]])
            ->push(['data' => ['createLinkToSignature' => ['short_link' => 'https://assina.ae/abc']]]),
    ]);

    $open = Signature::query()->where('public_id', 'sig-2')->firstOrFail();

    Livewire::test(SignaturesRelationManager::class, ['ownerRecord' => $this->document, 'pageClass' => ViewDocument::class])
        ->assertCanSeeTableRecords($this->document->signatures)
        ->assertTableActionHidden('resend', Signature::query()->where('public_id', 'sig-1')->firstOrFail())
        ->callTableAction('resend', $open)
        ->callTableAction('createLink', $open);

    Http::assertSent(fn (Request $request) => str_contains($request['query'], 'resendSignatures(public_ids: ["sig-2"])'));
    expect($open->fresh()->link)->toBe('https://assina.ae/abc');
});

it('lists and reprocesses webhook calls', function () {
    Queue::fake([ProcessWebhookCall::class]);

    $call = WebhookCall::query()->create(['event_id' => 'evt-1', 'type' => 'document.updated', 'payload' => ['event' => ['id' => 'evt-1']], 'exception' => 'boom']);

    Livewire::test(ListWebhookCalls::class)
        ->assertCanSeeTableRecords([$call])
        ->callTableAction('reprocess', $call);

    expect($call->fresh()->exception)->toBeNull();
    Queue::assertPushed(ProcessWebhookCall::class);
});
