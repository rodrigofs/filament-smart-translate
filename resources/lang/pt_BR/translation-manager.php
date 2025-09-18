<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Página de Gerenciamento de Traduções
    |--------------------------------------------------------------------------
    |
    | Strings de tradução para a interface da página de Gerenciamento de Traduções
    |
    */

    // Títulos da página e navegação
    'page_title' => 'Gerenciar Traduções',
    'navigation_label' => 'Traduções',

    // Colunas da tabela
    'columns' => [
        'key' => 'Chave da Tradução',
        'value' => 'Valor da Tradução',
        'locale' => 'Idioma',
        'category' => 'Categoria',
        'length' => 'Comprimento',
    ],

    // Ações
    'actions' => [
        'new_translation' => 'Nova Tradução',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'delete_selected' => 'Excluir Selecionados',
        'change_language' => 'Alterar Idioma',
        'refresh' => 'Atualizar',
        'export' => 'Exportar',
        'create_backup' => 'Criar Backup',
        'statistics' => 'Estatísticas',
    ],

    // Formulários
    'forms' => [
        'language' => 'Idioma',
        'select_language' => 'Selecionar Idioma',
        'key' => 'Chave',
        'key_placeholder' => 'ex: resources.user',
        'translation' => 'Tradução',
    ],

    // Modais
    'modals' => [
        'delete_translation' => [
            'heading' => 'Excluir Tradução',
            'description' => 'Tem certeza de que deseja excluir esta tradução? Esta ação não pode ser desfeita.',
        ],
        'delete_selected_translations' => [
            'heading' => 'Excluir Traduções Selecionadas',
            'description' => 'Tem certeza de que deseja excluir as traduções selecionadas? Esta ação não pode ser desfeita.',
        ],
        'statistics' => [
            'heading' => 'Estatísticas das Traduções',
            'total_translations' => 'Total de Traduções',
            'empty_translations' => 'Traduções Vazias',
            'long_translations' => 'Traduções Longas (>100 caracteres)',
            'average_length' => 'Comprimento Médio',
        ],
    ],

    // Estado vazio
    'empty_state' => [
        'heading' => 'Nenhuma tradução encontrada',
        'description' => 'Comece adicionando uma nova tradução usando o botão acima.',
    ],

    // Notificações
    'notifications' => [
        'translation_added' => [
            'title' => 'Tradução adicionada',
            'body' => 'A tradução \':key\' foi adicionada com sucesso para o idioma \':locale\'.',
        ],
        'error_adding_translation' => [
            'title' => 'Erro ao adicionar tradução',
            'body' => ':message',
        ],
        'translation_updated' => [
            'title' => 'Tradução atualizada',
            'body' => 'A tradução \':key\' foi atualizada com sucesso.',
        ],
        'error_updating_translation' => [
            'title' => 'Erro ao atualizar tradução',
            'body' => ':message',
        ],
        'translation_deleted' => [
            'title' => 'Tradução excluída',
            'body' => 'A tradução \':key\' foi excluída com sucesso do idioma \':locale\'.',
        ],
        'error_deleting_translation' => [
            'title' => 'Erro ao excluir tradução',
            'body' => ':message',
        ],
        'translations_deleted' => [
            'title' => 'Traduções excluídas',
            'body' => ':count traduções excluídas com sucesso.',
        ],
        'some_translations_not_deleted' => [
            'title' => 'Algumas traduções não foram excluídas',
            'body' => 'Falha ao excluir :count traduções.',
        ],
        'error_deleting_translations' => [
            'title' => 'Erro ao excluir traduções',
            'body' => ':message',
        ],
        'language_changed' => [
            'title' => 'Idioma alterado',
            'body' => 'Exibindo traduções para: :locale',
        ],
        'translations_refreshed' => [
            'title' => 'Traduções atualizadas',
        ],
        'backup_created' => [
            'title' => 'Backup criado',
            'body' => 'Backup salvo em: :path',
        ],
        'error_creating_backup' => [
            'title' => 'Erro ao criar backup',
            'body' => ':message',
        ],
    ],

    // Mensagens de validação
    'validation' => [
        'key_already_exists' => 'Esta chave já existe para este idioma.',
    ],

    // Cores das categorias (usadas nos badges)
    'categories' => [
        'auth' => 'Autenticação',
        'validation' => 'Validação',
        'error' => 'Erro',
        'ui' => 'Interface do Usuário',
        'resources' => 'Recursos',
        'navigations' => 'Navegação',
        'actions' => 'Ações',
        'default' => 'Geral',
    ],
];
