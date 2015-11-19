# Novuscom CMF #

CMF на Symfony2

## Установка ##

### Устанавливаем Symfony ###

Выполняем следующие команды в корне сайта (на примере Ubuntu)

1. `sudo curl -LsS http://symfony.com/installer -o /usr/local/bin/symfony`
2. `sudo chmod a+x /usr/local/bin/symfony`
3. `symfony new my_project_name`

Более подробная и актуальная информация об установке Symfony2 - [http://symfony.com/download](http://symfony.com/download)

### Устанавливаем Composer ###

Необходимо установить **Composer** для работы с пакетами, если еще не установлен.
`curl -sS https://getcomposer.org/installer | php`

[https://getcomposer.org/download/](https://getcomposer.org/download/)

### Устанавливаем Novuscom.CMF ###

Страница пакета на сайте packagist.org [https://packagist.org/packages/novuscom/cmfbundle](https://packagist.org/packages/novuscom/cmfbundle)

1. В блоке *require* файла composer.json добавляем следующие пакеты:
   ```json

    "gedmo/doctrine-extensions": "dev-master",
    "friendsofsymfony/user-bundle": "~2.0@dev",
    "knplabs/knp-menu": "2.0.*@dev",
    "knplabs/knp-menu-bundle": "2.0.*@dev",
    "whiteoctober/breadcrumbs-bundle": "dev-master",
    "apy/breadcrumbtrail-bundle": "dev-master",
    "mopa/bootstrap-bundle": "v3.0.0-beta2",
    "twbs/bootstrap": "v3.0.0",
    "knplabs/knp-paginator-bundle": "^2.4",
    "liip/imagine-bundle": "1.2.3",
    "misd/guzzle-bundle": "~1.0",
    "snc/redis-bundle": "1.1.x-dev",
    "predis/predis": "0.8.x-dev",
    "stfalcon/tinymce-bundle": "dev-master",
    "helios-ag/fm-elfinder-bundle": "~5",
    "novuscom/cmfbundle": "0.0.6.*",
    "novuscom/cmfuserbundle": "0.0.6.3",
    "openlss/lib-array2xml": "^0.0.10",
    "ifsnop/mysqldump-php": "2.*"

   ```
2. Выполняем команду `composer update`
    Composer поставит зависимости. 

3. Переходим по адресу *example.com/config.php* (example.com - адрес вашего сайта) - проверяем все ли рекомендации symfomy выполнены.
    Выполняем если нет.

4. Переходим по ссылке **Configure your Symfony Application online**, указываем доступы к базе данных и сохраняем

5. Добавляем в app/AppKernel.php строчки
 ```php
    new Knp\Bundle\MenuBundle\KnpMenuBundle(),
    new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
    new WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle(),
    new APY\BreadcrumbTrailBundle\APYBreadcrumbTrailBundle(),
    new Liip\ImagineBundle\LiipImagineBundle(),
    new Misd\GuzzleBundle\MisdGuzzleBundle(),
    new Novuscom\CMFBundle\NovuscomCMFBundle(),
    new Novuscom\CMFUserBundle\NovuscomCMFUserBundle(),
    new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
    new FM\ElfinderBundle\FMElfinderBundle(),
    new FOS\UserBundle\FOSUserBundle(),
 ```

6. Содержимое файла *app/config/routing.yml*
 ```
  NovuscomCMFBundle:
      resource: "@NovuscomCMFBundle/Resources/config/routing.yml"
 ```
7. Выполняем команды `php app/console doctrine:schema:update --dump-sql` и затем `php app/console doctrine:schema:update --force`

8.
 `php app/console cache:clear --env=prod --no-debug`

 `composer dump-autoload --optimize`

 `php app/console cache:clear --env=prod --no-debug`

 `php app/console cache:clear`

9.
*example.com/admin*