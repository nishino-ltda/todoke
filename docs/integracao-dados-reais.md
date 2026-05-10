# Rastreamento de Integração com Dados Reais

Este documento lista todas as páginas e componentes que atualmente usam dados mockados ou são placeholders, servindo como roteiro para integração com dados reais do backend.

## Legenda

| Prioridade | Descrição |
|------------|-----------|
| 🔴 Alta | Página usa dados mockados em funcionalidades principais |
| 🟡 Média | Página é placeholder/skeleton — precisa ser implementada |
| 🟢 Baixa | Componente tem fallback mockado (imagens, config) |
| ⚪ Info | Código com dados mockados em services/utils |

---

## 🔴 Mock em Funcionalidades Principais

| # | Arquivo | O que mocka | Linha | Backend Necessário |
|---|---------|-------------|-------|-------------------|
| 1 | `resources/js/Pages/Admin/Deliveries/Index.vue` | Dados do gráfico de timeline (labels + valores fixos) | 245-257 | Endpoint de métricas temporais de entregas |
| 2 | `resources/js/Pages/Admin/Dashboard.vue` | `makeData()` gera dados de gráfico via `Math.sin()/Math.random()` | 211-215 | Endpoint de métricas do dashboard admin |
| 3 | `resources/js/Pages/Partner/Dashboard.vue` | `makeData()` gera dados de gráfico via `Math.sin()/Math.random()` | 250-253 | Endpoint de métricas do dashboard partner |
| 4 | `resources/js/Pages/Partner/Products/Index.vue` | Array de categorias hardcoded `['Pizza', 'Burger', 'Dessert', 'Drinks', 'Sushi']` | 167 | Endpoint de categorias do backend |
| 5 | `resources/js/services/delivery.js` | `rejectDelivery()` retorna `Promise.resolve` local — sem endpoint real | 64-67 | Endpoint `PATCH /api/v1/deliveries/{id}/reject` (se aplicável) |

---

## 🟡 Página Placeholder / Skeleton

### Partner (Parceiro)

| # | Arquivo | Observação | Prioridade |
|---|---------|------------|------------|
| 6 | `resources/js/Pages/Partner/Products/Variations/Index.vue` | Placeholder — "listing product variations" | Média |
| 7 | `resources/js/Pages/Partner/Products/Variations/Edit.vue` | Placeholder — "editing product variations" | Média |
| 8 | `resources/js/Pages/Partner/Products/Variations/Create.vue` | Placeholder — "creating product variations" | Média |
| 9 | `resources/js/Pages/Partner/Products/Show.vue` | Skeleton — mostra apenas `productId` | Média |
| 10 | `resources/js/Pages/Partner/Orders/BatchCreate.vue` | Placeholder — "batch creating orders" | Média |
| 11 | `resources/js/Pages/Partner/Orders/Create.vue` | Placeholder — "creating partner orders" | Média |
| 12 | `resources/js/Pages/Partner/Addons/Show.vue` | Skeleton — mostra apenas `addonId` | Média |
| 13 | `resources/js/Pages/Partner/Addons/Create.vue` | Placeholder — "Form to create a new addon." | Média |
| 14 | `resources/js/Pages/Partner/Addons/Edit.vue` | Skeleton — mostra apenas `addonId` | Média |
| 15 | `resources/js/Pages/Partner/Admin.vue` | Skeleton — layout partner admin | Baixa |
| 16 | `resources/js/Pages/Partner/Partner.vue` | Skeleton — layout partner | Baixa |
| 17 | `resources/js/Pages/Partner/OrderDetails.vue` | Skeleton — detalhes do pedido | Média |

### Courier (Entregador)

| # | Arquivo | Observação | Prioridade |
|---|---------|------------|------------|
| 18 | `resources/js/Pages/Courier/Dashboard/Index.vue` | Placeholder via i18n — `courier.dashboard.index_placeholder` | Média |
| 19 | `resources/js/Pages/Courier/Deliveries/Index.vue` | Skeleton — apenas título + descrição traduzidos | Média |
| 20 | `resources/js/Pages/Courier/Deliveries/Show.vue` | Skeleton — apenas título + descrição traduzidos | Média |
| 21 | `resources/js/Pages/Courier/HybridDeliveries/Index.vue` | Skeleton — apenas título + descrição traduzidos | Média |
| 22 | `resources/js/Pages/Courier/HybridDeliveries/Show.vue` | Skeleton — apenas título + descrição traduzidos | Média |
| 23 | `resources/js/Pages/Courier/Courier.vue` | Skeleton — layout courier | Baixa |

### Customer (Cliente)

| # | Arquivo | Observação | Prioridade |
|---|---------|------------|------------|
| 24 | `resources/js/Pages/Customer/Orders/Index.vue` | Placeholder — "List of your orders." | Média |
| 25 | `resources/js/Pages/Customer/Orders/Show.vue` | Skeleton — mostra apenas `orderId` | Média |
| 26 | `resources/js/Pages/Customer/Dashboard.vue` | Skeleton — "Welcome to the customer dashboard." | Média |
| 27 | `resources/js/Pages/Customer/Orders.vue` | Skeleton | Média |
| 28 | `resources/js/Pages/Customer/OrderDetail.vue` | Skeleton | Média |

### Admin

| # | Arquivo | Observação | Prioridade |
|---|---------|------------|------------|
| 29 | `resources/js/Pages/Admin/Regions.vue` | Skeleton — versão antiga/alternativa | Média |
| 30 | `resources/js/Pages/Admin/Deliveries.vue` | Skeleton — versão antiga/alternativa | Média |
| 31 | `resources/js/Pages/Admin/Users.vue` | Skeleton — versão antiga/alternativa | Média |
| 32 | `resources/js/Pages/Admin/Settings.vue` | Skeleton — versão antiga/alternativa | Média |

### Geral

| # | Arquivo | Observação | Prioridade |
|---|---------|------------|------------|
| 33 | `resources/js/Pages/App.vue` | Skeleton — "Main application component." | Baixa |
| 34 | `resources/js/Pages/Welcome.vue` | Skeleton — "This is the welcome page." | Baixa |
| 35 | `resources/js/Pages/Dashboard.vue` | Skeleton | Baixa |
| 36 | `resources/js/Pages/Public/Home.vue` | Skeleton — "Public landing page content." | Baixa |

---

## 🟢 Fallback de Imagens Placeholder

| # | Arquivo | Fallback | Linha |
|---|---------|----------|-------|
| 37 | `resources/js/Components/ProductCard.vue` | `product.image \|\| '/images/placeholder.png'` | 7 |
| 38 | `resources/js/Components/ProductDetailsModal.vue` | `product.image \|\| '/images/placeholder-food.jpg'` | 16 |
| 39 | `resources/js/Pages/Partner/Products/Index.vue` | `item.image \|\| '/images/placeholder-food.jpg'` | 24 |

> Nota: fallbacks de imagem são aceitáveis para MVP, mas devem ser substituídos por imagens reais via upload.

---

## ⚪ Services com Dados Mockados

| # | Arquivo | Função | Comportamento Mock |
|---|---------|--------|--------------------|
| 40 | `resources/js/services/delivery.js` | `rejectDelivery()` | Retorna `Promise.resolve` local — `{ data: { id, rejected: true } }` |

---

## 📋 Resumo por Role

| Role | 🔴 Mock Funcional | 🟡 Placeholder | 🟢 Fallback Imagem | Total |
|------|-------------------|----------------|-------------------|-------|
| Admin | 2 | 4 | 0 | 6 |
| Partner | 1 | 11 | 1 | 13 |
| Courier | 0 | 6 | 0 | 6 |
| Customer | 0 | 5 | 0 | 5 |
| Geral | 0 | 4 | 0 | 4 |
| Compartilhado | 1 (service) | 0 | 2 | 3 |
| **Total** | **4** | **30** | **3** | **37** |

---

## Como Usar Este Documento

1. **Implementar endpoints no backend** conforme listado em "Backend Necessário"
2. **Substituir dados mockados** nas páginas 🔴 chamando os endpoints reais
3. **Implementar páginas 🟡** com CRUD completo + chamadas à API
4. **Remover placeholders** de imagem quando o upload estiver funcional
5. **Atualizar este arquivo** alterando o status para ✅ conforme cada item for integrado
