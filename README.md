# Point Store

ポイントで個人間売買するショップのREST API

## Requirements
- Docker

## 使用技術
- Laravel
- Sanctum
- Docker
- Nginx

## Execution 
```bash
git clone git@github.com:background-color/store-api-sample.git
cd store-api-sample
docker compose up -d
```

## APIs
Document : http://localhost:8080/docs/index.html

### AUTH
```bash
// Register
curl --request POST \
    "http://localhost:8080/api/register" \
    --header "Content-Type: application/json" \
    --data "{
    \"name\": \"山田花子\",
    \"email\": \"user1@example.com\",
    \"password\": \"password\"
}"

// Login
curl --request POST \
    "http://localhost:8080/api/login" \
    --header "Content-Type: application/json" \
    --data "{
    \"email\": \"user1@example.com\",
    \"password\": \"password\"
}"

// Login response
{
    "message": "Success",
    "data": {
        "access_token": "xxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer",
    }
}
```


## Tests
```bash
docker compose exec app /bin/bash -c "cd /app/store && php artisan test"
```



