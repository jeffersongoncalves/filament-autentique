<?php

return [
    'document' => [
        'label' => 'Documento',
        'plural_label' => 'Documentos',
        'fields' => [
            'name' => 'Nome',
            'status' => 'Status',
            'signatures' => 'Assinaturas',
            'sandbox' => 'Sandbox',
            'autentique_id' => 'ID na Autentique',
            'documentable' => 'Vinculado a',
            'created_at' => 'Criado em',
            'finished_at' => 'Concluído em',
            'file' => 'Arquivo',
            'message' => 'Mensagem aos signatários',
            'signers' => 'Signatários',
            'email' => 'E-mail',
            'signer_name' => 'Nome (link de assinatura, sem e-mail)',
            'action' => 'Ação',
            'refusable' => 'Signatários podem recusar',
            'sortable' => 'Assinar na ordem acima',
        ],
        'status' => [
            'pending' => 'Pendente',
            'signed' => 'Assinado',
            'rejected' => 'Recusado',
        ],
    ],

    'signature' => [
        'label' => 'Signatário',
        'plural_label' => 'Signatários',
        'fields' => [
            'name' => 'Nome',
            'email' => 'E-mail',
            'action' => 'Ação',
            'link' => 'Link de assinatura',
            'viewed_at' => 'Visualizado em',
            'signed_at' => 'Assinado em',
            'rejected_at' => 'Recusado em',
        ],
    ],

    'signer_actions' => [
        'SIGN' => 'Assinar',
        'SIGN_AS_A_WITNESS' => 'Assinar como testemunha',
        'APPROVE' => 'Aprovar',
        'RECOGNIZE' => 'Reconhecer',
    ],

    'webhook_call' => [
        'label' => 'Chamada de webhook',
        'plural_label' => 'Chamadas de webhook',
        'fields' => [
            'event_id' => 'Evento',
            'type' => 'Tipo',
            'processed' => 'Processado',
            'exception' => 'Erro',
            'created_at' => 'Recebido em',
        ],
        'filters' => [
            'failed' => 'Com erro',
        ],
    ],

    'actions' => [
        'send' => 'Enviar documento',
        'sync' => 'Sincronizar',
        'download' => 'PDF assinado',
        'resend' => 'Reenviar',
        'create_link' => 'Gerar link',
        'reprocess' => 'Reprocessar',
        'payload' => 'Payload',
    ],

    'notifications' => [
        'queued' => 'Documento na fila: ele aparece aqui assim que a Autentique o receber.',
        'synced' => 'Sincronização na fila.',
        'resent' => 'Solicitação de assinatura reenviada.',
        'link_created' => 'Link de assinatura gerado.',
        'reprocessed' => 'Webhook na fila para processamento.',
        'failed' => 'A Autentique retornou um erro',
    ],
];
