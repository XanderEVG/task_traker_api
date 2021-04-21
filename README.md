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


## Команды
curl -X POST -H "Content-Type: application/json" http://localhost:8000/api/login_check -d '{"username":"admin","password":"1"}'
curl -X GET -H "Content-Type: application/json" -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MTkwMDIzNDYsImV4cCI6MTYxOTAzMTE0Niwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluIn0.nCgyggozf8OrnHPqRiElIv4rapct65EybC7bVCag2arkHQgHJ8sPgDTf1dK7d1FT1c_L4eIlYBxtIuCEeGUO593Cxz_iHrPeqMgO5D84u0c-VvGRJibj6KO-ZJEzjnQ-AS5BNmcnSc8hzxOjIhcwvhYnfafP-xIZUjTF2Vyi8IPZvdu_ELwm4YGP9fzKOTM6P3WrkXcFnL8gHpAoelG9Hkz3G7vN8vHHTVjTBmW3wUpS4Af6m9R2hoDAY8SERpY_c10bkatBl4Ug3x7ig26z6VOY24XZa6OKeDqXvVfpS4Ydzo9ygZMou-H59OuryRknFFl2o6jkeQUfeakf_W41UFnv2AamypUskhBp0wPqglCUb2m_F0EOzbWRDGE-e46IOxIHyrvKpiUnCYUy6KPPBbFUAMwiU1BpqMmbaDs3yRAzGZcTXHZS4m8VtpdnbRou-7ZoK7S_OZegBDQsnKai9vP5YfxhpancLVsCPzFbRKmL5P6t3tdl0wRzvMx2TO1W5ZZe0DnSQLxtcv1TGXn8L3fxokrNFxw_oOrVECXTmJ4sfmxhVfyr4BMJfKd0UfJA3V4zb-fCGyQoczw5xmEAllOazJH4gR7qPM0WuQpoOEzZPPcEC_WRPqhNx9pI5x7YLTC9n5jvUOGdxqyivCWUEx1C1mckOolEZIiI9FtCSLE" http://localhost:8000/api/users