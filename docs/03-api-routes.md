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

## 4. Pedidos e Produtos (Restaurantes)

### 4.1 Listar Produtos
```
GET /api/v1/products
```
**Resposta (200 OK):**
```json
{
  "produtos": [
    {
      "id": "string",
      "name": "string",
      "descricao": "string",
      "preco": "number",
      "categoria": "string",
      "status": "string"
    }
  ]
}
```

### 4.2 Criar Pedido
```
POST /api/v1/orders
```
**Body:**
```json
{
  "restauranteId": "string",
  "itens": [
    {
      "produtoId": "string",
      "quantidade": "number"
    }
  ],
  "entrega": {
    "destino": {
      "lat": "number",
      "lng": "number",
      "endereco": "string"
    }
  }
}
```

### 4.3 Atualizar Status do Pedido
```
PATCH /api/v1/orders/{id}/status
```
**Body:**
```json
{
  "status": "aceito|em_preparo|aguardando_entregador"
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

## Exemplo Completo: Criar e Acompanhar Entrega

**1. Cliente cria entrega:**
```http
POST /api/v1/deliveries
Authorization: Bearer <cliente_token>
```
```json
{
  "origem": {
    "lat": -23.5505,
    "lng": -46.6333,
    "endereco": "Av. Paulista, 1000"
  },
  "destino": {
    "lat": -23.5614,
    "lng": -46.6559,
    "endereco": "Rua Augusta, 500"
  },
  "descricaoItem": "Documentos importantes",
  "pesoEstimado": 0.5,
  "dimensoes": {
    "largura": 25,
    "altura": 5,
    "profundidade": 15
  },
  "tipo": "expressa"
}
```

**2. Entregador aceita entrega:**
```http
PATCH /api/v1/deliveries/660e8400-e29b-41d4-a716-446655441111/accept
Authorization: Bearer <entregador_token>
```

**3. Cliente acompanha status:**
```http
GET /api/v1/deliveries/660e8400-e29b-41d4-a716-446655441111
Authorization: Bearer <cliente_token>
```
```json
{
  "id": "660e8400-e29b-41d4-a716-446655441111",
  "status": "em_transito",
  "entregador": {
    "name": "Aurora Silva",
    "fotoUrl": "https://...",
    "telefone": "+5511999999999"
  },
  "posicaoAtual": {
    "lat": -23.5555,
    "lng": -46.6444,
    "timestamp": "2025-04-22T12:45:00Z"
  }
}
