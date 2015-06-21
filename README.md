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
[https://getcomposer.org/download/](https://getcomposer.org/download/)

Выполняем команду `composer require novuscom/cmfbundle`
 
Или же в блок *require* файла *composer.json* в корне проекта помещаем строчку `"novuscom/cmfbundle": "dev-master"` 

Страница пакета на сайте packagist.org [https://packagist.org/packages/novuscom/cmfbundle](https://packagist.org/packages/novuscom/cmfbundle)






