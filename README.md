Sylius Klarna Payments Plugin
============================================

This plugin is designed to provide Klarna Payments to Sylius

By design, Klarna Payments are authorized. Capture or cancellation are available from admin.
Refund of captured payments is also possible.

## Klarna Payments

See https://docs.klarna.com/klarna-payments/  
https://docs.klarna.com/api/payments/

Create a test Klarna Merchant Account : https://docs.klarna.com/resources/test-environment/before-you-test/

## Plugin Installation


### Install using Composer :

```shell
composer require family-web-diffusion/sylius-klarna-payments-plugin
```

> Note: If the flex recipe has not been applied then follow the next step.

Enable this plugin :

```php
<?php

# config/bundles.php

return [
    // ...
    FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\FamilyWebDiffusionSyliusKlarnaPaymentsPlugin::class => ['all' => true],  
    // ...
];
```

### Configuration
- Create the file `config/packages/family_web_diffusion_sylius_klarna_payments.yaml` and add the following content
```yaml
imports:
  - { resource: '@FamilyWebDiffusionSyliusKlarnaPaymentsPlugin/config/config.yml' }
```

Override plugin options in `config/packages/family_web_diffusion_sylius_klarna_payments.yaml`, if needed:
```yaml
family_web_diffusion_sylius_klarna_payments:
  client_retry_limit: 3
  display_birthday: true
```
client_retry_limit (int) default 3, should be between 1 and 6 -> the number of times the client try to connect to Klarna API in case of connection error  
display_birthday (true/false) default true -> if true, the birthday of the sylius registered customer is sent to Klarna along with its email, enabling auto-connection for existing account  
If you prefer that Klarna asks user's birthday for each payment (or are concerned that sylius user birthday is false), set 'display_birthday' to false

- Enable SymfonyHTTPClient
  In `config/packages/framework.yaml`, add (for exemple):
```    
framework:
[...]
    http_client:
        default_options:
            max_duration: 10
```

### Modifiy Order

Implements FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface and
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderTrait on Order

See for exemple Tests\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\Order, tests/Application/src/Resources/config/config.yaml
and tests/Application/src/Resources/config/doctrine/Order.orm.yml

### Copy or adapt templates

Copy tests/Application/templates/bundles/SyliusShopBundle/Order/show.html.twig 
or adapt it to your needs


## Quickstart Usage

### Start Docker

Execute `docker compose up -d`  

### Starting test Application

#### First Installation
```bash
docker exec --user www-data:www-data -w /app sylius-klarna-payments-plugin_app_1 composer install
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 yarn install
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 yarn build
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console assets:install public
```

### Klarna Account
When your Klarna test account is created, connect to it to generate a pair of Klarna API credentials

Replace in tests/Application/.env.local
```
KLARNA_API_TEST_USERNAME=test
KLARNA_API_TEST_PASSWORD=test
```
by your Klarna test credential

Note: this is for test purpose only
for production, Klarna credential are set on Sylius admin configuration Payment Methods panel

#### After starting/restarting docker
(database is dropped at down)
```bash
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console doctrine:database:create --if-not-exists -e dev
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console doctrine:schema:create -n  -e dev
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console sylius:fixtures:load -n -e dev
docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console sylius:fixtures:load familywebdiffusion_klarna_payments -n -e dev
```

Open browser at http://localhost/ and switch to channel "DE Web Store"

## Utilities

### Connect to docker app container:
As root:
```bash
docker exec -it sylius-klarna-payments-plugin_app_1 sh
```

As web:
```bash
docker exec --user www-data:www-data -it sylius-klarna-payments-plugin_app_1 sh
```

### Running plugin tests

  - PHPUnit

    ```bash
    bin/phpunit
    ```

  - PHPSpec

    ```bash
    bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console doctrine:database:create --if-not-exists -e test
    docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console doctrine:schema:create -n  -e test
    docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console sylius:fixtures:load -n -e test
    docker exec --user www-data:www-data -w /app/tests/Application sylius-klarna-payments-plugin_app_1 bin/console sylius:fixtures:load familywebdiffusion_klarna_payments -n -e test
    docker exec --user www-data:www-data -w /app sylius-klarna-payments-plugin_app_1 sh -c 'export APP_ENV=test && bin/behat --strict'
    ```

  - Static Analysis
  
    - Psalm
    
      ```bash
      bin/psalm
      ```
      
    - PHPStan
    
      ```bash
      bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

  - Coding Standard
  
    ```bash
    bin/ecs check
    ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && bin/console sylius:fixtures:load familywebdiffusion_klarna_payments -n -e test)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```
    
- Using `dev` environment:

    ```bash
    (cd tests/Application && bin/console sylius:fixtures:load -n -e dev)
    (cd tests/Application && bin/console sylius:fixtures:load familywebdiffusion_klarna_payments -n -e dev)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
