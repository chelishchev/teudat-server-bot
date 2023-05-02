```shell
./composer.phar install --optimize-autoloader --no-dev
cp .env.example .env
./artisan key:generate
#руками прописать в .env локальную БД для проекта
#прописать в .env токен бота
#APP_DEBUG=
#APP_ENV=production

./artisan storage:link
./artisan migrate
./artisan config:cache
./artisan route:cache
./artisan telegram:set-webhook
```
