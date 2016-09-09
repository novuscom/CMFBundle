# Novuscom CMF #

CMF на Symfony2

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3b16c0b8-6055-4b38-a543-edfcc3ae1f65/big.png)](https://insight.sensiolabs.com/projects/3b16c0b8-6055-4b38-a543-edfcc3ae1f65)

## Установка ##


### Устанавливаем Composer ###

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Подробнее про уставноку composer [https://getcomposer.org/download/](https://getcomposer.org/download/)

### Устанавливаем Symfony ###

Выполняем следующие команды в корне сайта (на примере Ubuntu)

`composer create-project symfony/framework-standard-edition site.local`

(`php composer.phar create-project symfony/framework-standard-edition site.local`)

Более подробная и актуальная информация об установке Symfony2 - [http://symfony.com/download](http://symfony.com/download)

### Устанавливаем Novuscom.CMF ###

Страница пакета на сайте packagist.org [https://packagist.org/packages/novuscom/cmfbundle](https://packagist.org/packages/novuscom/cmfbundle)

1. В блоке *require* файла composer.json добавляем следующие пакеты:
	```json
	"novuscom/cmfbundle": "0.0.7.8",
	"novuscom/cmfuserbundle": "0.0.6.8",
	"gedmo/doctrine-extensions": "^2.4",
	"friendsofsymfony/user-bundle": "dev-master",
	"knplabs/knp-paginator-bundle": "^2.5",
	"helios-ag/fm-elfinder-bundle": "^6.0",
	"stfalcon/tinymce-bundle": "^0.4.0",
	"symfony/assetic-bundle": "^2.7",
	"knplabs/knp-menu": "^2.1",
	"knplabs/knp-menu-bundle": "^2.1",
	"apy/breadcrumbtrail-bundle": "dev-master",
	"liip/imagine-bundle": "^1.4",
	"openlss/lib-array2xml": "^0.0.10",
	"guzzlehttp/guzzle": "^6.2"
	```
2. Выполняем команду `composer update` (`php composer.phar update`)

    Composer поставит зависимости. 

3. Добавляем в app/AppKernel.php строчки
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

6. Содержимое файла *app/config/config.yml*
 ```
 imports:
    - { resource: "@NovuscomCMFBundle/Resources/config/config.yml" }
    - { resource: "parameters.yml" }
 ```

7. Содержимое файла *app/config/routing.yml*
 ```
  NovuscomCMFBundle:
      resource: "@NovuscomCMFBundle/Resources/config/routing.yml"
 ```

 
8. Выполняем команды `php app/console doctrine:schema:update --dump-sql` и затем `php app/console doctrine:schema:update --force`

9. 
	`php app/console cache:clear --env=prod --no-debug`
	
	`composer dump-autoload --optimize`

	`php app/console cache:clear --env=prod --no-debug`

	`php app/console cache:clear`

10. *example.com/admin*
