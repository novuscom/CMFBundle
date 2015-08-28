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

1. Выполняем команду `composer require novuscom/cmfbundle`
 
    Или же в блок *require* файла *composer.json* в корне проекта помещаем строчку `"novuscom/cmfbundle": "dev-master"`
    и выполняем команду `composer update`
 
    Composer поставит зависимости. 

2. Переходим по адресу *example.com/config.php* (example.com - адрес вашего сайта) - проверяем все ли рекомендации symfomy выполнены.
    Выполняем если нет.

3. Переходим по ссылке **Configure your Symfony Application online**, указываем доступы к базе данных и сохраняем

4. Добавляем в app/AppKernel.php строчку `new Novuscom\CMFBundle\NovuscomCMFBundle(),`

5. Выполняем команды `php app/console doctrine:schema:update --dump-sql` и затем `php app/console doctrine:schema:update --force`









