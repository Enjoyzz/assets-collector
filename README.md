# Assets Collector

![8.1](https://github.com/Enjoyzz/assets-collector/workflows/8.1/badge.svg)
![8.2](https://github.com/Enjoyzz/assets-collector/workflows/8.2/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Enjoyzz/assets-collector/?branch=master)




## Установка

```
composer require enjoys/assets-collector
```

## Использование

**Настройка окружения**

```php
use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;

//project directory
$projectDir = __DIR__;
//compile path relative project directory
$assetsDir = $projectDir .'/assets'; 
//or relative project directory
//$assetsDir = 'assets';

$environment = new Environment($assetsDir, $projectDir); 
//Base URL to compile path for Web
$environment->setBaseUrl("/assets-collector/example/assets"); 
//Set strategy, default \Enjoys\AssetsCollector\Strategy\ManyFilesStrategy
$environment->setStrategy(\Enjoys\AssetsCollector\Strategy\OneFileStrategy::class); //\Enjoys\AssetsCollector\Strategy\ManyFilesStrategy::class
//Cache time for files in strategy STRATEGY_ONE_FILE
$environment->setCacheTime(0); //cache time in seconds
//Adds the output version, for example //example.php/style.css?v=123 
$environment->setVersion(123); //int|float|string
//You can change the parameter for the version
$environment->setParamVersion('version');

/** 
 * You can add a logger that implements \Psr\Log\LoggerInterface, for example, Monolog
 * @var \Psr\Log\LoggerInterface $logger 
 */
$environment->setLogger($logger);

```

[Настройки CSS Minify](#options_cssminify)

[Настройки JS Minify](#options_jsminify)

**Инициализация класса**

```php
/** @var \Enjoys\AssetsCollector\Environment $environment */
$assets = new \Enjoys\AssetsCollector\Assets($environment);
```

**Добавление в коллекцию**

*Если третьим параметрам передать $group, то при выводе так же нужно его писать. Своего рода группировка*

```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    'style/style.css', //относительный путь, относительно текущей рабочей директории
    __DIR__ . '/style/style.css', //полный путь
    '//example.com/style.css', //сокращенная URL ссылка
    'https://example.com/style.css', //URL ссылка
    'url:/assets/css/style.css', // URL ссылка
    'local:/assets/css/style.css', // URL ссылка (local: и url: идентичны)
    ['style.css', \Enjoys\AssetsCollector\AssetOption::MINIFY => false], //попускает минификацию конкретного файла
    ['goods/style.css', \Enjoys\AssetsCollector\AssetOption::REPLACE_RELATIVE_URLS => false], //не заменяет относительные ссылки - оставляет так как есть
]);
```

**Дополнительные параметры**

Можно передать ссылку на ресурс в качестве массива, где 1-й элемент массива — сам путь, а последующие элементы массива —
это параметры

```php
use Enjoys\AssetsCollector\AssetOption;

/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    [
         __DIR__.'/style.css',
         // По-умолчанию все ресурсы минифицируются, если указать явно false, этот ресурс пропустит минификацию
         AssetOption::MINIFY => false,
         
         // Если нужно создать дополнительные симлинки, то можно указать их в этом параметре, в качества массива,
         // где ключ - сама ссылка, а значение - исходный файл или директория (цель)
         // Это бывает необходимо если в ресурсе есть относительные ссылки, и чтобы был к ним доступ нужно прописать
         // явно все символические ссылки
         AssetOption::SYMLINKS => [
            __DIR__.'/symlink' => __DIR__.'/../../../target',
            //...
        ],  
        
        // При STRATEGY_MANY_FILES будут добавлены html-аттрибуты,
        // примерно это будет выглядеть так
        // <script attribute-key='attribute-value' attribute-without-value attribute-without-value src='...'>
        AssetOption::ATTRIBUTES => [
            'attribute-key' => 'attribute-value',
            'attribute-without-value' => null,
            'attribute-without-value-another-method',
            //...
        ],
        
        // При STRATEGY_ONE_FILE если будет установлена эта опция в true, то именно этот asset в сборку не попадет,
        // а выведется отдельно
        AssetOption::NOT_COLLECT => true,   
         
        // При false - не заменяет относительные ссылки - оставляет так как есть.
        // По-умолчанию true, все относительные ссылки заменяются на абсолютные
        AssetOption::REPLACE_RELATIVE_URLS => false       
    ],
    //...
]);
```

*При сборке assets все собирается по порядку как пришли данные в коллекцию, так было раньше, и так стало сейчас по
умолчанию. Но можно выбрать способ вставки assets в коллекцию, изменив последний параметр
в `\Enjoys\AssetsCollector\Assets::add` `push` или `unshift`.*

*Это удобно использовать например в шаблонах twig, где например в самом главном шаблоне подключаются общие стили, а
потом в дочерних шаблонах подключаются конкретные стили, так вот при push, общие стили будут подключены ниже чем
конкретные, при unshift - подключены будут сначала стили общие, потом конкретные, хотя twig все равно их будет
обрабатывать в обратном порядке - от дочерних шаблонов до общих*

- `push` - дефолтное значение вставляет данные в конец
- `unshift` - вставляет данные в начало коллекции.

```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add($type = 'css|js', [], $group, $method = 'push|unshift');
```

**Вывод**

```php
/** @var \Enjoys\AssetsCollector\Assets $assets 
 */
$assets->get('css'); //get Css with default namespace
$assets->get('js', 'admin_namespace'); //Get Js with namespace `admin_namespace`
```

При *$environment->setStrategy(\Enjoys\AssetsCollector\Strategy\OneFileStrategy::class);* происходит чтение всех файлов и запись в один файл. Вернет
html строку для подключения стилей или скриптов

```html

<link type='text/css' rel='stylesheet' href='/assets/3c2ea3240f78656c2e4ad2b7f64a5bc2.css?_ver=1610822303'/>
```

При *$environment->setStrategy(\Enjoys\AssetsCollector\Strategy\ManyFilesStrategy::class);* вернет стили или скрипты по отдельности, примерно так,
удобно при разработке

```html

<link type='text/css' rel='stylesheet' href='/assets/bootstrap.min.css?_ver=1610822303'/>
<link type='text/css' rel='stylesheet' href='https://example.com/style.css?_ver=1610822303'/>
```

***ДЛЯ JS ВСЕ АНАЛОГИЧНО, ЗА ИСКЛЮЧЕНИЕМ HTML В ВЫВОДЕ***

## Twig Extension

**Подключение расширения**

```php
/** 
 * @var \Twig\Environment $twig 
 * @var \Enjoys\AssetsCollector\Assets $assets
 */
$twig->addExtension(new \Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension($assets));
```

**Добавление в коллекцию в шаблоне.**

Ниже показаны примеры, как можно подключить ресурсы в шаблоне.

*Стоит обратить внимание, что не полные пути будут относительно текущей рабочей директории, или относительно директории
проекта*

```twig
 {{  asset('css', [{0: 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css', 'minify': false}]) }}
 {{  asset('css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.css') }}
 {{  asset('js', ['path/script.js', 'script.js'], 'namespace') }}
```

**С версии 2.2.1 поддерживаются пути twig**

Необходимо при инициализации расширения передать загрузчик, который реализует `\Twig\Loader\LoaderInterface`,
например `\Twig\Loader\FilesystemLoader`

```php
/** 
 * @var \Enjoys\AssetsCollector\Extensions\Twig\AssetsExtension $extension 
 * @var \Enjoys\AssetsCollector\Assets $assets
 * @var \Twig\Loader\LoaderInterface $loader
 */
$extension = new AssetsExtension($assets, $loader));
```

**Вывод в шаблоне**

```twig
{{ eCSS() }}
{{ eJS('namespace') }}
```

<a id="options_cssminify"></a>

## Настройки Minify

По умолчанию в качестве минификатора CSS и JS ничего не используется, то есть ничего не сжимается. Для настройки
минификатора, в Environment используется метод **Environment::setMinifier(AssetsType $type, Minify|\Closure|null $minifier)**

Проще всего передать в класс анонимную функцию (\Closure(string): string), но также можно передать объект класса, реализовавший интерфейс
*\Enjoys\AssetsCollector\Minify::class* для сложных случаев.

```php
/** @var \Enjoys\AssetsCollector\Environment $environment */
use Enjoys\AssetsCollector\AssetType;

// css
$environment->setMinifier(AssetType::CSS, function (string $content): string {
    return (new CSSMin())->run($content);
});

$environment->setMinifier(AssetType::CSS, new class implements \Enjoys\AssetsCollector\Minifier {
    public function minify(string $content): string {
        return (new CSSMin())->run($content);
    }
});

```
Список third-party минификаторов:
- CSS
  - **YUI-CSS-compressor-PHP-port** [tubalmartin/cssmin](https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port)
- JS
  - **JShrink** [tedivm/jshrink](https://github.com/tedious/JShrink)
