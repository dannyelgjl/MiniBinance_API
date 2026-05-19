# Mini Binance API

API Laravel para o desafio técnico de trading: autenticação com Sanctum, carteira, preço fake de BTC, compra/venda e histórico de transações.

## Rodando com Docker

```bash
cp .env.example .env
docker compose up --build
```

Em outro terminal, rode as migrations e seeds se o container ainda não tiver feito:

```bash
docker compose exec app php artisan migrate --seed
```

A API sobe em `http://localhost:8000/api`.

Usuário seed para testes:

```text
Nome: Teste
Email: teste@example.com
Senha: passwordteste123
```

## Documentação Swagger

Com a aplicação rodando:

- Swagger UI: `http://localhost:8000/api/docs`
- OpenAPI JSON: `http://localhost:8000/api/openapi.json`

## Rodando sem Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Endpoints

Rotas públicas:

- `GET /api/docs`
- `GET /api/openapi.json`
- `POST /api/register`
- `POST /api/login`
- `GET /api/market/btc`

Rotas protegidas com `Authorization: Bearer <token>`:

- `GET /api/me`
- `POST /api/logout`
- `GET /api/wallet`
- `POST /api/trade/buy`
- `POST /api/trade/sell`
- `GET /api/transactions`

### Registro

```http
POST /api/register
Content-Type: application/json

{
  "name": "Daniel",
  "email": "daniel@example.com",
  "password": "password123"
}
```

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "daniel@example.com",
  "password": "password123"
}
```

### Compra

O endpoint aceita `amount_brl`, `brl_amount` ou `amount`.

```http
POST /api/trade/buy
Authorization: Bearer <token>
Content-Type: application/json

{
  "amount_brl": 1000
}
```

### Venda

O endpoint aceita `amount_btc`, `btc_amount` ou `amount`.

```http
POST /api/trade/sell
Authorization: Bearer <token>
Content-Type: application/json

{
  "amount_btc": 0.001
}
```

## Decisões técnicas

- Dinheiro é armazenado em centavos (`brl_balance_cents`) e BTC em satoshis (`btc_balance_satoshis`) para evitar erro de ponto flutuante.
- Compra e venda usam transaction SQL e `lockForUpdate()` para controle de concorrência.
- O preço fake do BTC fica em cache por `TRADING_MARKET_TTL` segundos, usando Redis no Docker e array nos testes.
- Cada usuário criado recebe automaticamente uma carteira inicial com R$ 10.000,00 e 0 BTC.

## Testes

```bash
docker compose exec app php artisan test
```
