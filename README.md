# Mini Binance API

Backend em Laravel para uma plataforma simples de trading de BTC. A API cobre autenticacao com Laravel Sanctum, carteira, preco fake de mercado, compra/venda de BTC, historico de transacoes, seed de dados, testes automatizados, Redis e Docker.

## Stack

- PHP 8.3
- Laravel 12
- Laravel Sanctum
- MySQL 8.4
- Redis 7
- PHPUnit
- Docker Compose
- Swagger UI com especificacao OpenAPI

## URLs

Com Docker, a API sobe em:

```text
http://localhost:8000/api
```

Documentacao navegavel:

```text
http://localhost:8000/api/docs
```

Especificacao OpenAPI:

```text
http://localhost:8000/api/openapi.json
```

Para o app mobile, use a URL conforme o ambiente:

```text
iOS Simulator:      http://localhost:8000/api
Android Emulator:  http://10.0.2.2:8000/api
Celular fisico:    http://SEU_IP_LOCAL:8000/api
```

Exemplo de IP local ja usado nesta maquina:

```text
http://192.168.0.2:8000/api
```

## Rodando Com Docker

Requisito: Docker Desktop aberto.

```bash
cp .env.example .env
docker compose up --build
```

O container `app` executa automaticamente:

```bash
composer install --no-interaction
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=0.0.0.0 --port=8000
```

Para rodar em segundo plano:

```bash
docker compose up --build -d
```

Para ver os containers:

```bash
docker compose ps
```

Para ver logs:

```bash
docker compose logs -f app
```

Para parar:

```bash
docker compose down
```

Para apagar tambem o volume do MySQL e recriar o banco do zero:

```bash
docker compose down -v
docker compose up --build
```

## Rodando Sem Docker

Requisitos locais: PHP 8.2+, Composer, MySQL e Redis.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Por padrao, o Laravel sobe em:

```text
http://127.0.0.1:8000
```

## Banco De Dados

Banco principal:

```text
MySQL 8.4
Database: mini_binance
User: mini_binance
Password: secret
```

Configuracao no Docker:

```yaml
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=mini_binance
DB_USERNAME=mini_binance
DB_PASSWORD=secret
```

Nos testes, o projeto usa SQLite em memoria para nao apagar os dados do MySQL do app.

## Usuario Seed

Ao rodar as seeds, o banco e populado com um usuario pronto para login:

```text
Nome: Teste
Email: teste@example.com
Senha: passwordteste123
```

Esse usuario tambem recebe dados para as rotas protegidas retornarem informacao:

```text
Saldo BRL: R$ 9.260,00
Saldo BTC: 0.00300000
Historico: 1 compra e 1 venda
```

Para rodar novamente:

```bash
docker compose exec app php artisan db:seed --force
```

## Autenticacao

A API usa Laravel Sanctum com token Bearer.

Fluxo esperado:

1. Fazer `POST /api/login` ou `POST /api/register`.
2. Guardar o campo `access_token`.
3. Enviar o token nas rotas protegidas:

```http
Authorization: Bearer <access_token>
Accept: application/json
```

## Rotas

### Publicas

| Metodo | Rota | Descricao |
| --- | --- | --- |
| `GET` | `/api/docs` | Swagger UI |
| `GET` | `/api/openapi.json` | Spec OpenAPI |
| `POST` | `/api/register` | Registro de usuario |
| `POST` | `/api/login` | Login com email e senha |
| `GET` | `/api/market/btc` | Preco fake atual do BTC |

### Protegidas

Todas exigem `Authorization: Bearer <token>`.

| Metodo | Rota | Descricao |
| --- | --- | --- |
| `GET` | `/api/me` | Dados do usuario autenticado |
| `POST` | `/api/logout` | Revoga o token atual |
| `GET` | `/api/wallet` | Saldos BRL e BTC |
| `POST` | `/api/trade/buy` | Compra BTC usando saldo BRL |
| `POST` | `/api/trade/sell` | Vende BTC e credita BRL |
| `GET` | `/api/transactions` | Historico paginado de transacoes |

## Exemplos De Requisicao

### Registro

```http
POST /api/register
Content-Type: application/json
Accept: application/json

{
  "name": "Daniel",
  "email": "daniel@example.com",
  "password": "password123"
}
```

Resposta:

```json
{
  "access_token": "1|token...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Daniel",
    "email": "daniel@example.com",
    "created_at": "2026-05-20T00:00:00.000000Z"
  },
  "wallet": {
    "brl_balance": 10000,
    "brl_balance_formatted": "10000.00",
    "brl_balance_cents": 1000000,
    "btc_balance": 0,
    "btc_balance_formatted": "0.00000000",
    "btc_balance_satoshis": 0
  }
}
```

### Login

```http
POST /api/login
Content-Type: application/json
Accept: application/json

{
  "email": "teste@example.com",
  "password": "passwordteste123"
}
```

### Usuario Autenticado

```http
GET /api/me
Authorization: Bearer <token>
Accept: application/json
```

### Carteira

```http
GET /api/wallet
Authorization: Bearer <token>
Accept: application/json
```

Resposta:

```json
{
  "brl_balance": 9260,
  "brl_balance_formatted": "9260.00",
  "brl_balance_cents": 926000,
  "btc_balance": 0.003,
  "btc_balance_formatted": "0.00300000",
  "btc_balance_satoshis": 300000
}
```

### Mercado BTC

```http
GET /api/market/btc
Accept: application/json
```

Resposta:

```json
{
  "symbol": "BTCBRL",
  "asset": "BTC",
  "currency": "BRL",
  "price": 250000,
  "price_change": 1.25,
  "price_formatted": "250000.00",
  "price_cents": 25000000,
  "price_history": [
    245000,
    246100,
    247250,
    248900,
    250000
  ],
  "price_history_cents": [
    24500000,
    24610000,
    24725000,
    24890000,
    25000000
  ],
  "cached_for_seconds": 10
}
```

### Compra De BTC

O endpoint aceita `amount_brl`, `brl_amount` ou `amount`.

```http
POST /api/trade/buy
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json

{
  "amount_brl": 1000
}
```

Regras:

- Valida saldo em BRL.
- Usa o preco atual do mercado.
- Debita BRL da carteira.
- Credita BTC em satoshis.
- Cria uma transacao do tipo `buy`.

### Venda De BTC

O endpoint aceita `amount_btc`, `btc_amount` ou `amount`.

```http
POST /api/trade/sell
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json

{
  "amount_btc": 0.001
}
```

Regras:

- Valida saldo em BTC.
- Usa o preco atual do mercado.
- Debita BTC da carteira.
- Credita BRL em centavos.
- Cria uma transacao do tipo `sell`.

### Historico

```http
GET /api/transactions?per_page=20
Authorization: Bearer <token>
Accept: application/json
```

Retorna uma lista paginada com:

- `type`: `buy` ou `sell`
- `brl_amount`
- `btc_amount`
- `btc_price`
- `created_at`

### Logout

```http
POST /api/logout
Authorization: Bearer <token>
Accept: application/json
```

## Arquitetura

O projeto segue uma organizacao por camadas simples, adequada ao escopo do desafio:

```text
app/
  Enums/
    TransactionType.php
  Http/
    Controllers/Api/
      AuthController.php
      MarketController.php
      TradeController.php
      TransactionController.php
      WalletController.php
      ApiDocumentationController.php
      OpenApiController.php
    Requests/
      Auth/
      Trade/
    Resources/
      UserResource.php
      WalletResource.php
      TransactionResource.php
  Models/
    User.php
    Wallet.php
    Transaction.php
  Services/
    AssetFormatter.php
    BtcMarketService.php
    TradeService.php
database/
  migrations/
  seeders/
routes/
  api.php
tests/
  Feature/
  Unit/
```

### Controllers

Responsaveis por receber a requisicao HTTP, chamar services quando necessario e retornar resources JSON.

### Form Requests

Centralizam validacoes de entrada:

- `RegisterRequest`
- `LoginRequest`
- `BuyTradeRequest`
- `SellTradeRequest`

### Resources

Padronizam as respostas JSON:

- `UserResource`
- `WalletResource`
- `TransactionResource`

### Models

Entidades principais:

- `User`: usuario autenticavel com Sanctum.
- `Wallet`: saldo BRL e BTC do usuario.
- `Transaction`: historico de compras e vendas.

### Services

Concentram regra de negocio:

- `AssetFormatter`: conversao segura entre BRL/centavos e BTC/satoshis.
- `BtcMarketService`: gera e cacheia o preco fake do BTC.
- `TradeService`: executa compra e venda com transacao SQL e lock de carteira.

## Modelagem

### `users`

Tabela padrao de usuarios do Laravel.

### `wallets`

Campos principais:

- `user_id`
- `brl_balance_cents`
- `btc_balance_satoshis`

Cada usuario possui uma carteira unica.

### `transactions`

Campos principais:

- `user_id`
- `type`
- `brl_amount_cents`
- `btc_amount_satoshis`
- `btc_price_cents`
- `created_at`

## Decisoes Tecnicas

- BRL e armazenado em centavos (`brl_balance_cents`) para evitar erro de ponto flutuante.
- BTC e armazenado em satoshis (`btc_balance_satoshis`) pelo mesmo motivo.
- Compra e venda rodam dentro de `DB::transaction`.
- A carteira e consultada com `lockForUpdate()` durante trade para controle de concorrencia.
- O preco fake do BTC fica entre R$ 200.000,00 e R$ 300.000,00.
- O preco do BTC e cacheado em Redis por `TRADING_MARKET_TTL` segundos.
- O seed e idempotente: pode rodar mais de uma vez sem duplicar o usuario.
- Testes rodam com SQLite em memoria, isolados do MySQL de desenvolvimento.

## Cache Redis

O Redis e usado para cachear o preco fake do BTC.

Variavel:

```env
TRADING_MARKET_TTL=10
```

Arquivo principal:

```text
app/Services/BtcMarketService.php
```

Comportamento:

- Primeira chamada a `/api/market/btc` gera um preco aleatorio.
- A resposta inclui `price_change`, `price_history` e `price_history_cents` para alimentar cards/graficos do app.
- Chamadas seguintes dentro do TTL retornam o mesmo pacote de mercado em cache.
- Depois do TTL, um novo pacote de mercado e gerado.

## Controle De Concorrencia

O fluxo de trade usa:

```php
DB::transaction(...)
Wallet::query()->lockForUpdate()
```

Isso evita que duas compras ou vendas simultaneas usem o mesmo saldo antes da atualizacao da carteira.

## Testes

Rodar testes com Docker:

```bash
docker compose exec app php artisan test
```

Rodar testes sem Docker:

```bash
php artisan test
```

Coberturas principais:

- Registro e login
- Endpoint `/me`
- Carteira inicial
- Mercado BTC
- Compra com saldo
- Venda com saldo BTC
- Validacao de saldo insuficiente
- Historico de transacoes
- Seed do usuario de teste
- Swagger/OpenAPI
- Conversao de BRL/centavos e BTC/satoshis

## Troubleshooting

### Login retorna credenciais invalidas

Rode novamente o seed:

```bash
docker compose exec app php artisan db:seed --force
```

Depois tente:

```json
{
  "email": "teste@example.com",
  "password": "passwordteste123"
}
```

### Android Emulator nao acessa `localhost`

Use:

```text
http://10.0.2.2:8000/api
```

### Celular fisico nao acessa a API

Use o IP local da maquina na mesma rede Wi-Fi:

```bash
ipconfig getifaddr en0
```

Depois configure no app:

```text
http://SEU_IP_LOCAL:8000/api
```

### Docker nao sobe

Confirme que o Docker Desktop esta aberto:

```bash
docker info
```

Se precisar recriar tudo:

```bash
docker compose down -v
docker compose up --build
```

## Comandos Uteis

```bash
docker compose up --build -d
docker compose ps
docker compose logs -f app
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan test
docker compose down
```
