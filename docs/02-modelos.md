# TODOKE: Modelos de Dados

**Nota sobre convenções:** Todos os nomes de campos e relacionamentos usam convenções em inglês para manter consistência com as melhores práticas de desenvolvimento.

## Visão Geral

Este documento descreve os principais modelos de dados da plataforma TODOKE, identificados a partir dos casos de uso. Os modelos são representados como classes/entidades com seus atributos e relacionamentos.

## 1. Usuário (User)

**Descrição:** Representa todos os tipos de usuários do sistema (entregadores, clientes, administradores, parceiros).

**Atributos:**
- id: string (UUID)
- name: string
- email: string (único)
- telefone: string
- tipo: enum ['entregador', 'cliente', 'admin', 'parceiro']
- fotoUrl: string (opcional)
- status: enum ['ativo', 'inativo', 'pendente']
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Um User pode ter múltiplas Entregas (como cliente ou entregador)
- Um User do tipo 'parceiro' pode ter múltiplos Nodes associados

**Exemplo:**
```typescript
{
  id: "550e8400-e29b-41d4-a716-446655440000",
  name: "Aurora Silva",
  email: "aurora@email.com",
  telefone: "+5511999999999",
  tipo: "entregador",
  fotoUrl: "https://...",
  status: "ativo",
  createdAt: "2025-04-22T10:00:00Z",
  updatedAt: "2025-04-22T10:00:00Z"
}
```

## 2. Entrega (Delivery)

**Descrição:** Representa uma solicitação de entrega, desde a criação até a conclusão.

**Atributos:**
- id: string (UUID)
- clienteId: string (ref: User)
- entregadorId: string (ref: User, opcional)
- origem: GeoPoint (lat, lng) + endereço textual
- destino: GeoPoint (lat, lng) + endereço textual
- status: enum ['pendente', 'coletado', 'em_transito', 'entregue', 'cancelado']
- tipo: enum ['normal', 'expressa', 'sustentavel', 'hibrida']
- descricaoItem: string
- pesoEstimado: number (kg)
- dimensoes: { largura: number, altura: number, profundidade: number } (cm)
- valor: number
- tempoEstimado: number (minutos)
- codigoConfirmacao: string (opcional)
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Pertence a um Cliente (User)
- Pode ter um Entregador (User) associado
- Pode ter múltiplas Avaliações (Rating)
- Pode estar associada a um Node (para entregas híbridas)

**Exemplo:**
```typescript
{
  id: "660e8400-e29b-41d4-a716-446655441111",
  clienteId: "440e8400-e29b-41d4-a716-446655440001",
  entregadorId: "550e8400-e29b-41d4-a716-446655440000",
  origem: { lat: -23.5505, lng: -46.6333, endereco: "Av. Paulista, 1000" },
  destino: { lat: -23.5614, lng: -46.6559, endereco: "Rua Augusta, 500" },
  status: "em_transito",
  tipo: "normal",
  descricaoItem: "Comida japonesa",
  pesoEstimado: 1.5,
  dimensoes: { largura: 20, altura: 10, profundidade: 15 },
  valor: 15.90,
  tempoEstimado: 25,
  codigoConfirmacao: "A1B2C3",
  createdAt: "2025-04-22T12:00:00Z",
  updatedAt: "2025-04-22T12:30:00Z"
}
```

## 3. Node (Node)

**Descrição:** Representa um recurso de entrega (entregador, drone, veículo) associado a um parceiro.

**Atributos:**
- id: string (UUID)
- parceiroId: string (ref: User)
- tipo: enum ['entregador', 'drone', 'veiculo']
- identificador: string (placa, número de série)
- capacidade: number (kg)
- status: enum ['disponivel', 'ocupado', 'manutencao', 'offline']
- regiaoId: string (ref: Regiao)
- posicaoAtual: GeoPoint (lat, lng, opcional)
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Pertence a um Parceiro (User)
- Está associado a uma Região
- Pode estar associado a múltiplas Entregas

**Exemplo:**
```typescript
{
  id: "770e8400-e29b-41d4-a716-446655442222",
  parceiroId: "330e8400-e29b-41d4-a716-446655440002",
  tipo: "drone",
  identificador: "DR-001",
  capacidade: 5,
  status: "disponivel",
  regiaoId: "880e8400-e29b-41d4-a716-446655443333",
  posicaoAtual: { lat: -23.5505, lng: -46.6333 },
  createdAt: "2025-04-22T09:00:00Z",
  updatedAt: "2025-04-22T09:00:00Z"
}
```

## 4. Região (Region)

**Descrição:** Representa uma área geográfica de operação para um parceiro.

**Atributos:**
- id: string (UUID)
- parceiroId: string (ref: User)
- name: string
- poligono: GeoPolygon (array de GeoPoints)
- status: enum ['ativo', 'inativo', 'pendente']
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Pertence a um Parceiro (User)
- Pode ter múltiplos Nodes associados

**Exemplo:**
```typescript
{
  id: "880e8400-e29b-41d4-a716-446655443333",
  parceiroId: "330e8400-e29b-41d4-a716-446655440002",
  name: "Zona Sul - SP",
  poligono: [
    { lat: -23.5505, lng: -46.6333 },
    { lat: -23.5614, lng: -46.6559 },
    { lat: -23.5700, lng: -46.6400 }
  ],
  status: "ativo",
  createdAt: "2025-04-21T08:00:00Z",
  updatedAt: "2025-04-21T08:00:00Z"
}
```

## 5. Avaliação (Rating)

**Descrição:** Representa uma avaliação feita por um cliente sobre uma entrega.

**Atributos:**
- id: string (UUID)
- entregaId: string (ref: Delivery)
- avaliadorId: string (ref: User)
- avaliadoId: string (ref: User)
- nota: number (1-5)
- comentario: string (opcional)
- createdAt: DateTime

**Relacionamentos:**
- Pertence a uma Entrega
- Tem um Avaliador (User) e um Avaliado (User)

**Exemplo:**
```typescript
{
  id: "990e8400-e29b-41d4-a716-446655444444",
  entregaId: "660e8400-e29b-41d4-a716-446655441111",
  avaliadorId: "440e8400-e29b-41d4-a716-446655440001",
  avaliadoId: "550e8400-e29b-41d4-a716-446655440000",
  nota: 5,
  comentario: "Ótimo serviço, muito pontual!",
  createdAt: "2025-04-22T13:00:00Z"
}
```

## 6. Produto (Product)

**Descrição:** Representa um item do cardápio de um restaurante parceiro (BistroTech).

**Atributos:**
- id: string (UUID)
- restauranteId: string (ref: User)
- name: string
- descricao: string
- preco: number
- categoria: string
- imagemUrl: string (opcional)
- status: enum ['disponivel', 'indisponivel']
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Pertence a um Restaurante (User)
- Pode estar em múltiplos Pedidos

**Exemplo:**
```typescript
{
  id: "110e8400-e29b-41d4-a716-446655445555",
  restauranteId: "220e8400-e29b-41d4-a716-446655440003",
  name: "Sushi Combinado",
  descricao: "20 peças variadas de sushi",
  preco: 59.90,
  categoria: "Japonês",
  imagemUrl: "https://...",
  status: "disponivel",
  createdAt: "2025-04-20T10:00:00Z",
  updatedAt: "2025-04-20T10:00:00Z"
}
```

## 7. Pedido (Order)

**Descrição:** Representa um pedido de produtos feito por um cliente.

**Atributos:**
- id: string (UUID)
- clienteId: string (ref: User)
- restauranteId: string (ref: User)
- status: enum ['pending', 'accepted', 'preparing', 'awaiting_delivery', 'delivery_in_progress', 'delivered', 'canceled']
- itens: Array<{ product_id: string, quantity: number, unitPrice: number }>
- valorTotal: number
- entregaId: string (ref: Delivery, opcional)
- createdAt: DateTime
- updatedAt: DateTime

**Relacionamentos:**
- Pertence a um Cliente (User) e um Restaurante (User)
- Tem múltiplos Produtos associados
- Pode ter uma Entrega associada

**Exemplo:**
```typescript
{
  id: "120e8400-e29b-41d4-a716-446655446666",
  clienteId: "440e8400-e29b-41d4-a716-446655440001",
  restauranteId: "220e8400-e29b-41d4-a716-446655440003",
  status: "preparing",
  itens: [
    { product_id: "110e8400-e29b-41d4-a716-446655445555", quantity: 1, unitPrice: 59.90 }
  ],
  valorTotal: 59.90,
  entregaId: "660e8400-e29b-41d4-a716-446655441111",
  createdAt: "2025-04-22T12:15:00Z",
  updatedAt: "2025-04-22T12:30:00Z"
}
```

## Diagrama de Relacionamentos

```mermaid
erDiagram
    USER ||--o{ DELIVERY : "cliente/entregador"
    USER ||--o{ NODE : "parceiro"
    USER ||--o{ REGION : "parceiro"
    USER ||--o{ PRODUCT : "restaurante"
    DELIVERY ||--o{ RATING : "avaliações"
    DELIVERY ||--|{ NODE : "node associado"
    DELIVERY ||--|{ ORDER : "entrega"
    REGION ||--o{ NODE : "nodes na região"
    ORDER ||--o{ PRODUCT : "itens"
    ORDER ||--|{ USER : "cliente/restaurante"
