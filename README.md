# Assets Collector

Инициализация

*Чтобы можно было использовать единый инстанс запустите его в DI контейнере*

```php
/**
* @param string first argument - compile path relative project directory
* @param string second argument - project directory
 */
$environment = new \Enjoys\AssetsCollector\Environment('assets', __DIR__); 
$environment->setBaseUrl("/assets-collector/example/assets"); //Base URL to compile path for Web
$environment->setStrategy(\Enjoys\AssetsCollector\Assets::STRATEGY_ONE_FILE); //Assets::STRATEGY_MANY_FILES
$environment->setCacheTime(0); //cache time in seconds
$environment->setCssBuildFile('dir/file.css'); //or $environment->setJsBuildFile(); allowed use dir in the path

/** @var \Psr\Log\LoggerInterface $logger */
$environment->setLogger($logger);

```

```php
/** 
 * @var \Enjoys\AssetsCollector\Environment $environment
 */
$assets = new \Enjoys\AssetsCollector\Assets($environment);

```

Добавление в коллекцию

*Если третьим параметрам передать namespace, то при выводе так же нужно его писать. Своего рода группировка*

```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    'style/style.css', //относительный путь, относительно текущей рабочей директории
    __DIR__ . '/style/style.css', //полный путь
    '//example.com/style.css', //сокращенная URL ссылка
    'https://example.com/style.css', //URL ссылка
    ['style.css', \Enjoys\AssetsCollector\Asset::PARAM_MINIFY => false], //попускает минификацию конкретного файла
]);
```

Вывод

```php
/** @var \Enjoys\AssetsCollector\Assets $assets 
 */
$assets->get('css'); //get Css with default namespace
$assets->get('js', 'admin_namespace'); //Get Js with namespace `admin_namespace`
```

При $environment->setStrategy(\Enjoys\AssetsCollector\Assets::STRATEGY_ONE_FILE); происходит чтение всех файлов и запись
в один файл. Вернет html строку для подключения стилей или скриптов

```html

<link type='text/css' rel='stylesheet' href='/assets/main.css?_ver=1610822303'/>
```

При $environment->setStrategy(\Enjoys\AssetsCollector\Assets::STRATEGY_MANY_FILES); вернет стили или скрипты по
отдельности, примерно так, удобно при разработке

```html

<link type='text/css' rel='stylesheet' href='/assets/bootstrap.min.css?_ver=1610822303'/>
<link type='text/css' rel='stylesheet' href='https://example.com/style.css?_ver=1610822303'/>
```

#### ДЛЯ JS ВСЕ АНАЛОГИЧНО, ЗА ИСКЛЮЧЕНИЕМ HTML В ВЫВОДЕ

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