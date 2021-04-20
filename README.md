## Запуск
 - БД
   - Запускаем контейнеры (с PostgreSQL и ElasticSearch) `docker-compose up`
   - Прописываем в env-файле в параметры подключения к БД (По идее нужно брать шаблон из env.example и вставлять свои данные, но в рамках тестового задания я не стал добавлять env  в гитигнор, что бы проще было запустить)
 - Устанавливаем пакеты `composer install`
 - Настойка аутентификации
    - Генерируем сертификаты для шифрования (можно указать пароль 123456, что бы не лезть в .env)
      ```
      mkdir -p config/jwt 
      openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
      openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
      ```
    - Прописываем в env-файле в параметр *JWT_PASSPHRASE* пароль к сертификату
  - Подготовка БД
    - Накатываем миграции `bin/console doctrine:migrations:migrate`
    - Генерируем тестовые данные `bin/console doctrine:fixtures:load`
    - Создаем индекс ElasticSearch `php bin/console fos:elastica:populate`
  - Запускаем веб-сервер back-end `symfony server:start`
  
