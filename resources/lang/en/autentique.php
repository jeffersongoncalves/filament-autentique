<?php

return [
    'document' => [
        'label' => 'Document',
        'plural_label' => 'Documents',
        'fields' => [
            'name' => 'Name',
            'status' => 'Status',
            'signatures' => 'Signed',
            'sandbox' => 'Sandbox',
            'autentique_id' => 'Autentique ID',
            'documentable' => 'Linked to',
            'created_at' => 'Created at',
            'finished_at' => 'Finished at',
            'file' => 'File',
            'message' => 'Message to signers',
            'signers' => 'Signers',
            'email' => 'Email',
            'signer_name' => 'Name (signature link, no email)',
            'action' => 'Action',
            'refusable' => 'Signers may reject',
            'sortable' => 'Sign in the order above',
        ],
        'status' => [
            'pending' => 'Pending',
            'signed' => 'Signed',
            'rejected' => 'Rejected',
        ],
    ],

    'signature' => [
        'label' => 'Signer',
        'plural_label' => 'Signers',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'action' => 'Action',
            'link' => 'Signature link',
            'viewed_at' => 'Viewed at',
            'signed_at' => 'Signed at',
            'rejected_at' => 'Rejected at',
        ],
    ],

    'signer_actions' => [
        'SIGN' => 'Sign',
        'SIGN_AS_A_WITNESS' => 'Sign as a witness',
        'APPROVE' => 'Approve',
        'RECOGNIZE' => 'Acknowledge',
    ],

    'webhook_call' => [
        'label' => 'Webhook call',
        'plural_label' => 'Webhook calls',
        'fields' => [
            'event_id' => 'Event',
            'type' => 'Type',
            'processed' => 'Processed',
            'exception' => 'Error',
            'created_at' => 'Received at',
        ],
        'filters' => [
            'failed' => 'Failed',
        ],
    ],

    'actions' => [
        'send' => 'Send document',
        'sync' => 'Sync',
        'download' => 'Signed PDF',
        'resend' => 'Resend',
        'create_link' => 'Create link',
        'reprocess' => 'Reprocess',
        'payload' => 'Payload',
    ],

    'notifications' => [
        'queued' => 'Document queued: it shows up here once Autentique has received it.',
        'synced' => 'Sync queued.',
        'resent' => 'Signature request resent.',
        'link_created' => 'Signature link created.',
        'reprocessed' => 'Webhook queued for processing.',
        'failed' => 'Autentique returned an error',
    ],
];
