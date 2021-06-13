# Бандл для подключения Symfony Router к кастомным вариантам Wordpress-Symfony

## Зачем?

**INTERNAL**. Нужен для максимального разнесения функционала по пакетам. Плюс борьба с копипастой инструментов 
на проектах.

## Установка

1) `composer require proklung/wp.symfony.router.bundle`

2) Подключение бандла в `standalone_bundles.php`

## Параметры

Файл `symfony_router.yaml` в конфигурации бандлов:

- `enabled` - Использовать бандл. По умолчанию `true`.
- `controller_annotations_path` - массив с путями к контроллерам, использующим аннотации. 
- `router_cache_path` - путь к кэшу аннотаций. По умолчанию `%kernel.cache_dir%/routes`. 
- `router_config_file` - путь к файлу с конфигурацией роутов. По умолчанию  `app/routes.yaml`. Файл может быть в любом поддерживаемом Symfony формате - Yaml, PHP, XML и т.д. 
- `router_check_exists_controller` - проверять на существование классы-контроллеры. По умолчанию `false`.

## "Нативные" Wordpress обработчики AJAX (через wp-admin.php)

Переменная в контейнере `yaml.native.routes.file` - путь к Yaml файлу типа:

```yaml
examples_wp: # Action роута
  path: ''   # Игнор. Нужно, чтобы генератор коллекций роутов не падал.
  methods: [GET, POST]
  controller: 'Local\Services\Wordpress\SampleAjaxController::action'
  defaults:
    _public: true # Публичный роут или только для админки?
```

Если не задана, то пытается найти по адресу `/app/wp_routes.yaml`.
