# TODOKE: Rotas da API

## Visão Geral

Este documento descreve as principais rotas da API da plataforma TODOKE, organizadas por funcionalidade. Todas as rotas retornam JSON e requerem autenticação via Bearer Token, exceto onde indicado.

## Convenções

- Prefixo base: `/api/v1`
- Autenticação: `Authorization: Bearer <token>`
- Formato de datas: ISO 8601 (`YYYY-MM-DDTHH:MM:SSZ`)
- IDs: UUID v4

## 1. Autenticação e Usuário

### 1.1 Registrar Usuário
```
POST /api/v1/auth/register
```
**Body:**
```json
{
  "name": "string",
  "email": "string",
  "telefone": "string",
  "tipo": "entregador|cliente|parceiro",
  "senha": "string"
}
```
**Respostas:**
- 201 Created: Usuário criado
- 400 Bad Request: Dados inválidos
- 409 Conflict: Email já cadastrado

### 1.2 Login
```
POST /api/v1/auth/login
```
**Body:**
```json
{
  "email": "string",
  "senha": "string"
}
```
**Resposta (200 OK):**
```json
{
  "token": "string",
  "usuario": {
    "id": "string",
    "name": "string",
    "email": "string",
    "tipo": "string"
  }
}
```

### 1.3 Obter Perfil
```
GET /api/v1/users/me
```
**Resposta (200 OK):**
```json
{
  "id": "string",
  "name": "string",
  "email": "string",
  "telefone": "string",
  "tipo": "string",
  "fotoUrl": "string|null",
  "status": "string"
}
```

### 1.4 Atualizar Perfil
```
PATCH /api/v1/users/me
```
**Body (parâmetros opcionais):**
```json
{
  "name": "string",
  "telefone": "string",
  "fotoUrl": "string"
}
```

## 2. Entregas

### 2.1 Criar Entrega (Cliente)
```
POST /api/v1/deliveries
```
**Body:**
```json
{
  "origem": {
    "lat": "number",
    "lng": "number",
    "endereco": "string"
  },
  "destino": {
    "lat": "number",
    "lng": "number",
    "endereco": "string"
  },
  "descricaoItem": "string",
  "pesoEstimado": "number",
  "dimensoes": {
    "largura": "number",
    "altura": "number",
    "profundidade": "number"
  },
  "tipo": "normal|expressa|sustentavel"
}
```
**Resposta (201 Created):**
```json
{
  "id": "string",
  "valor": "number",
  "tempoEstimado": "number",
  "codigoConfirmacao": "string"
}
```

### 2.2 Listar Entregas
```
GET /api/v1/deliveries
```
**Query Params:**
- `status`: Filtrar por status
- `limit`: Limite de resultados (padrão: 10)
- `offset`: Paginação

**Resposta (200 OK):**
```json
{
  "entregas": [
    {
      "id": "string",
      "status": "string",
      "origem": "object",
      "destino": "object",
      "valor": "number",
      "createdAt": "string"
    }
  ],
  "total": "number"
}
```

### 2.3 Aceitar Entrega (Entregador)
```
PATCH /api/v1/deliveries/{id}/accept
```
**Respostas:**
- 200 OK: Entrega aceita
- 403 Forbidden: Não autorizado
- 409 Conflict: Entrega já aceita

### 2.4 Atualizar Status da Entrega
```
PATCH /api/v1/deliveries/{id}/status
```
**Body:**
```json
{
  "status": "coletado|em_transito|entregue",
  "codigoConfirmacao": "string" // opcional
}
```

## 3. Nodes e Regiões (Parceiros)

### 3.1 Listar Nodes
```
GET /api/v1/nodes
```
**Resposta (200 OK):**
```json
{
  "nodes": [
    {
      "id": "string",
      "tipo": "string",
      "identificador": "string",
      "status": "string",
      "regiao": {
        "id": "string",
        "name": "string"
      }
    }
  ]
}
```

### 3.2 Criar Node
```
POST /api/v1/nodes
```
**Body:**
```json
{
  "tipo": "entregador|drone|veiculo",
  "identificador": "string",
  "capacidade": "number",
  "regiaoId": "string"
}
```

### 3.3 Listar Regiões
```
GET /api/v1/regions
```
**Resposta (200 OK):**
```json
{
  "regioes": [
    {
      "id": "string",
      "name": "string",
      "status": "string",
      "nodesCount": "number"
    }
  ]
}
```

### 3.4 Criar/Atualizar Região
```
POST /api/v1/regions
PUT /api/v1/regions/{id}
```
**Body:**
```json
{
  "name": "string",
  "poligono": [
    { "lat": "number", "lng": "number" }
  ]
}
```

## 4. Produtos, Addons e Pedidos

### 4.1 Listar Produtos
```
GET /api/v1/products
```
**Query Params:**
- `category`: Filtrar por categoria

**Resposta (200 OK):**
```json
{
  "products": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": "number",
      "category": "string",
      "status": "string",
      "partner": "string"
    }
  ]
}
```

### 4.2 Obter Addons de um Produto
```
GET /api/v1/products/{product}/addons
```
**Resposta (200 OK):**
```json
{
  "addons": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": "number",
      "status": "string"
    }
  ]
}
```

### 4.3 Listar Addons
```
GET /api/v1/addons
```
**Query Params:**
- `partner_id`: Filtrar por parceiro

**Resposta (200 OK):**
```json
{
  "addons": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": "number",
      "status": "string",
      "partner": "string"
    }
  ]
}
```

### 4.4 Criar Addon (Parceiro)
```
POST /api/v1/addons
```
**Body:**
```json
{
  "name": "string",
  "description": "string",
  "price": "number"
}
```
**Resposta (201 Created):**
```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "price": "number",
  "status": "string",
  "partner_id": "string"
}
```

### 4.5 Atualizar Addon (Parceiro)
```
PUT /api/v1/addons/{addon}
```
**Body:**
```json
{
  "name": "string",
  "description": "string",
  "price": "number",
  "status": "available|unavailable"
}
```

### 4.6 Associar Addons a um Produto (Parceiro)
```
POST /api/v1/products/{product}/addons
```
**Body:**
```json
{
  "addon_ids": ["string", "string"]
}
```
**Resposta (200 OK):**
```json
{
  "message": "Addons updated successfully"
}
```

### 4.7 Criar Pedido
```
POST /api/v1/orders
```
**Body:**
```json
{
  "partner_id": "string",
  "items": [
    {
      "product_id": "string",
      "quantity": "number",
      "addons": [
        {
          "addon_id": "string",
          "quantity": "number"
        }
      ]
    }
  ],
  "delivery": {
    "destination": {
      "lat": "number",
      "lng": "number",
      "address": "string"
    }
  }
}
```
**Resposta (201 Created):**
```json
{
  "id": "string",
  "status": "string",
  "total_value": "number"
}
```

### 4.8 Atualizar Status do Pedido
```
PATCH /api/v1/orders/{id}/status
```
**Body:**
```json
{
  "status": "accepted|preparing|awaiting_delivery"
}
```

## 5. Administração

### 5.1 Listar Usuários (Admin)
```
GET /api/v1/admin/users
```
**Query Params:**
- `tipo`: Filtrar por tipo de usuário
- `status`: Filtrar por status

### 5.2 Gerenciar Status de Usuário
```
PATCH /api/v1/admin/users/{id}/status
```
**Body:**
```json
{
  "status": "ativo|inativo"
}
```

### 5.3 Estatísticas da Plataforma
```
GET /api/v1/admin/stats
```
**Resposta (200 OK):**
```json
{
  "usuariosAtivos": "number",
  "entregasHoje": "number",
  "entregasStatus": {
    "pendente": "number",
    "em_andamento": "number",
    "concluida": "number"
  }
}
```

## Webhooks

### Entrega Atualizada
```
POST /webhooks/delivery-updated
```
**Headers:**
- `X-Todoke-Signature`: assinatura HMAC

**Body:**
```json
{
  "event": "delivery.updated",
  "data": {
    "id": "string",
    "status": "string",
    "entregador": {
      "id": "string",
      "name": "string"
    }
  }
}
```

## Exemplo Completo: Criar Pedido com Addons

**1. Cliente visualiza produtos:**
```http
GET /api/v1/products?category=Lanches
```

**2. Cliente visualiza addons disponíveis para um produto:**
```http
GET /api/v1/products/123/addons
```
```json
{
  "addons": [
    {
      "id": "456",
      "name": "Queijo Extra",
      "description": "Queijo cheddar premium",
      "price": "5.00",
      "status": "available"
    },
    {
      "id": "789",
      "name": "Bacon",
      "description": "Bacon crocante",
      "price": "3.50",
      "status": "available"
    }
  ]
}
```

**3. Cliente cria pedido com addons:**
```http
POST /api/v1/orders
Authorization: Bearer <cliente_token>
```
```json
{
  "partner_id": "101",
  "items": [
    {
      "product_id": "123",
      "quantity": 1,
      "addons": [
        {
          "addon_id": "456",
          "quantity": 1
        },
        {
          "addon_id": "789",
          "quantity": 2
        }
      ]
    }
  ],
  "delivery": {
    "destination": {
      "lat": -23.5614,
      "lng": -46.6559,
      "address": "Rua Augusta, 500"
    }
  }
}
```
```json
{
  "id": "660e8400-e29b-41d4-a716-446655441111",
  "status": "pending",
  "total_value": "22.00"
}
