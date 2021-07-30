# Assets Collector

![7.4](https://github.com/Enjoyzz/assets-collector/workflows/7.4/badge.svg?branch=master)
![8.0](https://github.com/Enjoyzz/assets-collector/workflows/8.0/badge.svg)
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
//Set strategy, default STRATEGY_MANY_FILES
$environment->setStrategy(Assets::STRATEGY_ONE_FILE); //Assets::STRATEGY_MANY_FILES
//Cache time for files in strategy STRATEGY_ONE_FILE
$environment->setCacheTime(0); //cache time in seconds
//Adds the output version, for example //example.php/style.css?v=123 
$environment->setVersion(123);
//You can change the parameter for the version
$environment->setParamVersion('?ver=');

/** 
 * YYou can add a logger that implements \Psr\Log\LoggerInterface, for example, Monolog
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

*Если третьим параметрам передать namespace, то при выводе так же нужно его писать. Своего рода группировка*

```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    'style/style.css', //относительный путь, относительно текущей рабочей директории
    __DIR__ . '/style/style.css', //полный путь
    '//example.com/style.css', //сокращенная URL ссылка
    'https://example.com/style.css', //URL ссылка
    ['style.css', \Enjoys\AssetsCollector\Asset::MINIFY => false], //попускает минификацию конкретного файла
]);
```

**Дополнительные параметры**

Можно передать ссылку на ресурс в качестве массива, где 1-й элемент массива — сам путь, а последующие элементы массива —
это параметры

```php
use Enjoys\AssetsCollector\Asset;

/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add('css', [
    [
         __DIR__.'/style.css',
         // По-умолчанию все ресурсы минифицируются, если указать явно false, этот ресурс пропустит минификацию
         Asset::MINIFY => false,
         
         // Если нужно создать дополнительные симлинки, то можно указать их в этом параметре, в качества массива,
         // где ключ - сама ссылка, а значение - исходный файл или директория (цель)
         // Это бывает необходимо если в ресурсе есть относительные ссылки, и чтобы был к ним доступ нужно прописать
         // явно все символические ссылки
         Asset::CREATE_SYMLINK => [
            __DIR__.'/symlink' => __DIR__.'/../../../target',
            //...
        ],          
    ],
    //...
]);
```

*При сборке assets все собирается по порядку как пришли данные в коллекцию, так было раньше, и так стало сейчас по
умолчанию. Но можно выбрать способ вставки assets в коллекцию, изменив последний параметр
в `\Enjoys\AssetsCollector\Assets::add` `push` или `unshift`.*

*Это удобно использовать например в шаблонах twig, где например в самом главном шаблоне подключаются общие стили, а
потом в дочерних шаблонах подключаются конкретные стили, так вот при push, общие стили будут подключены ниже чем
конкретные, при unshift - подкюлочены будут сначала стили общие , потом конкретные, хотя twig все равно их будет
обрабатывать в обратном порядке - от дочерних шаблонов до общих*

- `push` - дефолтное значение вставляет данные в конец
- `unshift` - вставляет данные в начало коллекции.

```php
/** @var \Enjoys\AssetsCollector\Assets $assets */
$assets->add($type = 'css|js', [], $namespace, $method = 'push|unshift');
```

**Вывод**

```php
/** @var \Enjoys\AssetsCollector\Assets $assets 
 */
$assets->get('css'); //get Css with default namespace
$assets->get('js', 'admin_namespace'); //Get Js with namespace `admin_namespace`
```

При *$environment->setStrategy(Assets::STRATEGY_ONE_FILE);* происходит чтение всех файлов и запись в один файл. Вернет
html строку для подключения стилей или скриптов

```html

<link type='text/css' rel='stylesheet' href='/assets/3c2ea3240f78656c2e4ad2b7f64a5bc2.css?_ver=1610822303'/>
```

При *$environment->setStrategy(Assets::STRATEGY_MANY_FILES);* вернет стили или скрипты по отдельности, примерно так,
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

**Вывод в шаблоне**

```twig
{{ eCSS() }}
{{ eJS('namespace') }}
```

<a id="options_cssminify"></a>

## Настройки CSS Minify

По умолчанию в качестве минификатора используется **NullMinify::class**, то есть ничего не сжимается. Для установки
минификатора, в Environment используется метод **Environment::setMinifyCSS(MinifyInterface $minifyImpl)**


Базовая реализация CSS Minify реализована с помощью библиотеки **tubalmartin\CssMin**
Подробное описание параметров: https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port#api

Легко можно реализовать минификацию с помощью других библиотек, реализовав интерфейс
*\Enjoys\AssetsCollector\Content\Minify\MinifyInterface::class*

```php
use Enjoys\AssetsCollector\Content\Minify\Adapters\CssMinify;

/** @var \Enjoys\AssetsCollector\Environment $environment */
//необязательно передавать все параметры, можно только выборочно 
$environment->setMinifyCSS(
    new CssMinify([
        'keepSourceMapComment' => false, //bool
        'removeImportantComments' => true, //bool
        'setLineBreakPosition' => 1000, //int
        'setMaxExecutionTime' => 60, //int
        'setMemoryLimit' => '128M',
        'setPcreBacktrackLimit' => 1000000, //int
        'setPcreRecursionLimit' => 500000, //int
    ])
);
```

<a id="options_jsminify"></a>

## Настройки JS Minify

По умолчанию в качестве минификатора используется **NullMinify::class**, то есть ничего не сжимается. Для установки
минификатора, в Environment используется метод **Environment::setMinifyJS(MinifyInterface $minifyImpl)**

Базовая реализация CSS Minify реализована с помощью библиотеки **JShrink**
Подробнее про [JShrink](https://github.com/tedious/JShrink)

Легко можно реализовать минификацию с помощью других библиотек, реализовав интерфейс 
*\Enjoys\AssetsCollector\Content\Minify\MinifyInterface::class*

```php

use Enjoys\AssetsCollector\Content\Minify\Adapters\JsMinify;

/** @var \Enjoys\AssetsCollector\Environment $environment */
//не обязательно передавать все параметры, можно только выборочно 
$environment->setJsMinifyOptions(
    new JsMinify([
        'flaggedComments' => false
    ])
);
```