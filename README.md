# Assets Collector


Инициализация

*Чтобы можно было использовать единый инстанс запустите его в в DI контейнере*

```php

$config = new \Enjoys\AssetsCollector\Environment(
    __DIR__.'/assets', //полный путь для сборки и кеширования, пример, /var/www/project/web/assets
    '/assets', //относительный baseUrl до директории сборки, пример, /something/assets (http://localhost/something/assets/...)
    '/var/www/project' //директория проекта (не веб), rootPath если хотите
);

$config->setBuild(true);
$config->setCacheTime(86400);
//other setting
$config->setVersion(time());
$config->setParamVersion('?v='); //default '?_ver='
$config->setCssPath('css'); // css is default value
$config->setCssBuildFile('main.css'); //main.css - default value
$config->setJsPath('js'); // js is default value
$config->setJsBuildFile('script.js'); //script.js -default value

```

```php
/** @var \Enjoys\AssetsCollector\Environment $config */
$filesystemAdapter = new \League\Flysystem\Local\LocalFilesystemAdapter($config->getProjectDir());
```

```php
/** 
 * @var \League\Flysystem\FilesystemAdapter $filesystemAdapter
 * @var \Enjoys\AssetsCollector\Environment $config
 */
$assets = new \Enjoys\AssetsCollector\Assets($config, $filesystemAdapter = null);

```

Добавление в коллекцию 

*Если третим параметрам передать namespace, то при выводе так же нужно его писать. Своего рода группировка*
```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    'style/style.css', //относительный путь
    __DIR__ . '/style/style.css', //полный путь
    '//example.com/style.css', //сокращенная URL ссылка
    'https://example.com/style.css', //URL ссылка
    ['style.css', \Enjoys\AssetsCollector\Asset::PARAM_MINIFY => false], //попускает минификацию конкретного файла
]);
```

Вывод 
```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->getCss();
$assets->getJs('admin_namespace');
```

При Build = true происходит чтение всех файлов и запись в один файл. Вернет html строку для подлючения стилей или скриптов
```html
<link type='text/css' rel='stylesheet' href='/assets/main.css?_ver=1610822303' />
```
Если отключено, вернет примерно такое, удобно при разработке
```html
<link type='text/css' rel='stylesheet' href='/assets/bootstrap.min.css?_ver=1610822303' />
<link type='text/css' rel='stylesheet' href='https://example.com/style.css?_ver=1610822303' />
```

####ДЛЯ JS ВСЕ АНАЛОГИЧНО, ЗА ИСКЛЮЧЕНИЕМ HTML В ВЫВОДЕ

## Twig

Добавление в коллекцию. Показано для css, но для js то же самое

```twig
 {{  asset('css', [{0: 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css', 'minify': false}]) }}
 {{  asset('css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css') }}
 {{  asset('css', ['path/style1.css', 'style2.css']) }}
```

Вывод

```twig
{{ eCSS()|raw }}
{{ eJS()|raw }}
```