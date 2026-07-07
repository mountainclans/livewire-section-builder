<?php

return [
    /*
     * Headless-режим: секции отдаются наружу через API (BuilderSection::toApiArray),
     * ключ 'frontend' в реестре секций опционален. По умолчанию выключен —
     * поведение Livewire-first не меняется.
     */
    'headless' => false,

    /*
     * Валидация реестра секций при загрузке приложения (opt-in).
     * Проверяет обязательные ключи секций ('frontend' обязателен только
     * вне headless-режима) и ссылки шаблонов на существующие секции.
     */
    'validate_registry' => false,

    /*
     * Сериализатор медиа для toApiArray()/serializeMediaCollection():
     * класс-инвокабл, реализующий Contracts\SerializesMedia.
     * null — дефолтный Support\DefaultMediaSerializer ({url, alt}).
     */
    'media_serializer' => null,

    /*
     * Модели медиа приложения, используемые редакторами картинок
     * (WithRepeaterImages). Переопределить, если в проекте свои классы.
     */
    'media_model' => 'App\Models\Media',
    'temp_media_model' => 'App\Models\TempMedia',

    'sections' => [
        //            [
        //                'key' => 'top_banner',
        //                'title' => 'Top banner',
        //                'model' => EloquentSectionModelClass::class,
        //                'editor' => LivewireEditorComponent::class,
        //                'frontend' => LivewireViewComponent::class, // Livewire-режим
        //                'schema' => OpenApiSchemaClass::class,      // headless-режим
        //            ],
        //            ...
    ],
    'templates' => [
        //        'main_page' => [
        //            'top_banner',
        //            'advantages',
        //        ]
    ],
];
