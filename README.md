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
   
	"novuscom/cmfbundle": "0.0.6.*",
	"novuscom/cmfuserbundle": "0.0.6.*",
	"gedmo/doctrine-extensions": "^2.4",
	"friendsofsymfony/user-bundle": "dev-master",
	"knplabs/knp-paginator-bundle": "^2.5",
	"helios-ag/fm-elfinder-bundle": "^6.0",
	"stfalcon/tinymce-bundle": "^0.4.0",
	"symfony/assetic-bundle": "^2.7",
	"knplabs/knp-menu": "^2.1",
	"knplabs/knp-menu-bundle": "^2.1",
	"apy/breadcrumbtrail-bundle": "dev-master",
	"liip/imagine-bundle": "^1.4"
        
   ```
2. Выполняем команду `composer update`
    Composer поставит зависимости. 

3. Переходим по адресу *example.com/config.php* (example.com - адрес вашего сайта) - проверяем все ли рекомендации symfomy выполнены.
    Выполняем если нет.

4. Переходим по ссылке **Configure your Symfony Application online**, указываем доступы к базе данных и сохраняем

5. Добавляем в app/AppKernel.php строчки
 ```php
 new Novuscom\CMFBundle\NovuscomCMFBundle(),
 new Novuscom\CMFUserBundle\NovuscomCMFUserBundle(),
 new FOS\UserBundle\FOSUserBundle(),
 new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
 new FM\ElfinderBundle\FMElfinderBundle(),
 new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
 new Knp\Bundle\MenuBundle\KnpMenuBundle(),
 new APY\BreadcrumbTrailBundle\APYBreadcrumbTrailBundle(),
 new Liip\ImagineBundle\LiipImagineBundle(),
 ```

6. Содержимое файла *app/config/routing.yml*
 ```
  NovuscomCMFBundle:
      resource: "@NovuscomCMFBundle/Resources/config/routing.yml"
 ```
7. Содержимое файла *app/config/config.yml*
 ```
 imports:
    - { resource: "@NovuscomCMFBundle/Resources/config/config.yml" }
    - { resource: parameters.yml }
 ```
 
8. Выполняем команды `php app/console doctrine:schema:update --dump-sql` и затем `php app/console doctrine:schema:update --force`

9. `php app/console cache:clear --env=prod --no-debug`

 `composer dump-autoload --optimize`

 `php app/console cache:clear --env=prod --no-debug`

 `php app/console cache:clear`

10. *example.com/admin*
