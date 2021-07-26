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
- `router_cache_path` - путь к кэшу аннотаций (вида `%kernel.cache.dir%/routes`). По умолчанию `null`. 
   Если задан, то роуты будут кэшироваться.  
- `router_config_file` - путь к файлу с конфигурацией роутов. По умолчанию  `app/routes.yaml`. Файл может быть в любом поддерживаемом Symfony формате - Yaml, PHP, XML и т.д. 
- `router_check_exists_controller` - проверять на существование классы-контроллеры. По умолчанию `false`.
- `resource` - путь к конфигурационному файлу роутов. По умолчанию: `%kernel.project_dir%/app/routes.yaml`.
- `native_resource` - путь к конфигурации "нативных" обработчиков роутов. По умолчанию: `%kernel.project_dir%/app/wp_routes.yaml`.

## "Нативные" Wordpress обработчики AJAX (через wp-admin.php)

Yaml файл типа (обычный файл с описаниями роутов):

```yaml
examples_wp: # Action роута
  path: ''   # Игнор. Нужно, чтобы генератор коллекций роутов не падал.
  methods: [GET, POST]
  controller: 'Local\Services\Wordpress\SampleAjaxController::action'
  defaults:
    _public: true # Публичный роут или только для админки?
```

Для реализации контроллера на нативный обработчик Ajax предлагается использовать класс `Prokl\WpSymfonyRouterBundle\Services\NativeAjax\AbstractWPAjaxController`.
Он представляет собой наследника от `AbstractController` с добавлением конструктора, принимающего одну зависимость - 
объект класса `Request`. Приходится так действовать, потому что в ajax обработчик Wordpress никак нелльзя передать
параметры снаружи (за исключением супер-глобалов типа `$_POST` и `$_GET`). 

Дополнительно в Request контроллера пробрасываются всякие параметры из свойств роута. Например `methods`.

Это делает возможным, например, отсекать неправильные типа запроса в `action` обработчика.

```php
class SampleAjaxController extends AbstractWPAjaxController
{
    public function action()
    {
        $this->checkTypeRequest('Invalid type request');

        $response = new Response(
            'OK',
            Response::HTTP_OK
        );

        $response->headers->set('Content-Type', 'application/html; charset=utf-8');
        $response->send();
        
        wp_die();
    }
}
```

Или писать каку-нибудь логику для операций с `defaults`, `requirements` или `options` из параметров роута.

## Использование роутера без контейнера и вне фреймворка

`functions.php`:

```php
use Prokl\WpSymfonyRouterBundle\Services\Agnostic\RoutesConfigurator;
use Prokl\WpSymfonyRouterBundle\Services\Agnostic\Router;
use Prokl\WpSymfonyRouterBundle\Services\Agnostic\WpInitializerRouter;

$agnosticRouter = new RoutesConfigurator(
    $_SERVER['DOCUMENT_ROOT'] . '/local/configs/standalone_routes.yaml',
    $_SERVER['DOCUMENT_ROOT'] . '/wp-content/cache/routes', // Кэш; если null - без кэширования.
    $_ENV['APP_DEBUG'] // Режим отладки или нет
);

$agnosticRouterInstance = new Router(
    $agnosticRouter->getRouter(),
    new WpInitializerRouter()
);
```

Все. Подтянутся роуты из `/local/configs/standalone_routes.yaml`. Автоматически подцепятся события.

Допускается наличие нескольких таких "агностических" роутеров в один момент.

## Прочее

1) Экземпляр `Symfony\Component\Routing\Router` можно получить снаружи так:

```php
$router = \Prokl\WpSymfonyRouterBundle\Services\Agnostic\RoutesConfigurator::getInstance();
```