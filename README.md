# Point Store

ポイントで個人間売買する店舗のREST API サンプルです。

## 機能一覧
- ユーザー登録（10000ポイント付与）
- ログイン
- 商品登録 *
- 商品編集 *
- 商品削除 *
- 商品一覧表示
- 商品購入 *
- 売買履歴表示 *

\* = 認証必要

## 使用技術
- PHP(Laravel)
- MySQL
- Nginx
- Sanctum
- Docker

## Setup
```bash
git clone git@github.com:background-color/store-api-sample.git
cd store-api-sample
cp src/store/.env.example src/store/.env

docker compose up -d

docker compose exec app composer install
docker compose exec app php artisan migrate --force
```
BaseURL: http://localhost:8080/

## APIs
Document : http://localhost:8080/docs/

### AUTH
```bash
# Register
curl --request POST \
    "http://localhost:8080/api/register" \
    --header "Content-Type: application/json" \
    --data "{
    \"name\": \"山田花子\",
    \"email\": \"user1@example.com\",
    \"password\": \"password\"
}"

# Login
curl --request POST \
    "http://localhost:8080/api/login" \
    --header "Content-Type: application/json" \
    --data "{
    \"email\": \"user1@example.com\",
    \"password\": \"password\"
}"

# Login response
{
    "message": "Success",
    "data": {
        "access_token": "{YOUR_AUTH_KEY}",
        "token_type": "Bearer",
    }
}

# Request sample
curl --request GET \
    --get "http://localhost:8080/api/orders?per_page=10" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"
```

### Item
```bash
# Item list
GET http://localhost:8080/api/items

# Item show
GET http://localhost:8080/api/items/{ID}

# Item create
POST http://localhost:8080/api/items

# Item update
PUT http://localhost:8080/api/items/{ID}

# Item delete
DELETE http://localhost:8080/api/items/{ID}
```

### Order
```bash
# Order
POST http://localhost:8080/api/orders

# Order History
GET http://localhost:8080/api/orders

```



## Tests
```bash
docker compose exec app php artisan test
```




## Close
```bash
docker compose down -v
```
