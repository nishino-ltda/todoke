# Sprint 12 — Partner Pages: i18n, Layout Fixes & Premium UI

## Context

As páginas do partner foram implementadas com funcionalidade básica, mas possuem problemas críticos: título fixo "Partner Dashboard" em todas as páginas, dezenas de strings em inglês sem tradução, 12 páginas stub sem implementação real, bugs no backend (rota `metrics` inexistente, código órfão de tabela deletada), e ausência do padrão visual premium que já existe nas páginas do customer.

As páginas do customer (`Customer/Orders/Index.vue`, `Customer/Orders/Show.vue`) servem como referência de qualidade — usam glassmorphism, gradientes, border-radius 28px, animações hover, e título dinâmico no layout.

## Seeded Test Credentials (password: `password123`)

| Papel | Email |
|-------|-------|
| Admin | admin@todoke.test |
| Partner | partner@todoke.test |
| Courier | courier@todoke.test |
| Customer | customer@todoke.test |

## Tasks

### 1. Layout Fix — Título Dinâmico e i18n nos Nav Items

O `PartnerLayout.vue` tem um `<h1>` fixo "Partner Dashboard" que aparece em todas as páginas. Deve seguir o padrão do `CustomerLayout.vue` que usa `currentPageTitle` computado dinamicamente.

Arquivo: `resources/js/Layouts/PartnerLayout.vue`

- Substituir título hardcoded por computed property `currentPageTitle`
- Aplicar `$t()` nos `title` dos `navItems`
- Usar `v-app-bar` com `v-app-bar-title` em vez de `v-main` + `<h1>` (igual ao CustomerLayout)
- Adicionar `LanguageSelector` no app-bar
- Mover o ícone `mdi-store` para o app-bar

Referência visual: `resources/js/Layouts/CustomerLayout.vue`

Chaves i18n necessárias:
```
partner.nav.dashboard
partner.nav.orders
partner.nav.products
partner.nav.settings
partner.nav.profile
partner.nav.access_as_customer
partner.title (fallback quando nenhuma rota casar)
```

### 2. Internacionalização — Strings Faltantes

#### 2.1 Profile.vue

Arquivo: `resources/js/Pages/Partner/Profile.vue`

Traduzir via `$t()` todos os labels e mensagens:

| Localização | Strings |
|-------------|---------|
| Form fields | `Name`, `Email`, `Phone`, `Profile Photo`, `Choose a photo` |
| Business fields | `Business Name`, `Business Type` (e items `restaurant`/`market`/`pharmacy`), `Tax ID`, `Address` |
| Botão | `Save` |
| Roles section | `Additional Roles`, `Access as Customer`, `Want to become a courier?`, `Become a Courier` |
| Courier form | `License Number`, `Vehicle Type` (e items `motorcycle`/`car`/`bicycle`), `Submit` |
| Status | `You are already a courier.` |
| Snackbar | `Profile updated successfully`, `Failed to save profile`, `Role added successfully!`, `Failed to add role` |

Chaves i18n a adicionar em `pt-BR.json` e `en.json`:
```
partner.profile.name
partner.profile.email
partner.profile.phone
partner.profile.profile_photo
partner.profile.choose_photo
partner.profile.business_name
partner.profile.business_type
partner.profile.business_type_options.restaurant
partner.profile.business_type_options.market
partner.profile.business_type_options.pharmacy
partner.profile.tax_id
partner.profile.address
partner.profile.save
partner.profile.additional_roles
partner.profile.access_as_customer
partner.profile.become_courier
partner.profile.become_courier_title
partner.profile.license_number
partner.profile.vehicle_type
partner.profile.vehicle_type_options.motorcycle
partner.profile.vehicle_type_options.car
partner.profile.vehicle_type_options.bicycle
partner.profile.submit
partner.profile.already_courier
partner.profile.success.updated
partner.profile.error.save
partner.profile.success.role_added
partner.profile.error.role_added
```

#### 2.2 Dashboard.vue — Moeda

Arquivo: `resources/js/Pages/Partner/Dashboard.vue`

- Linha 90: `${{ item.total?.toFixed(2) || '0.00' }}` → usar `formatPrice()` ou `$t('common.currency_format', { value: item.total })`
- Linha 307: `` `$${stats[m.key].toFixed(2)}` `` → usar formatação i18n

#### 2.3 OrderDetails.vue — Moeda e Print Labels

Arquivo: `resources/js/Pages/Partner/OrderDetails.vue`

- Linha 59: `${{ item.price }}` → formatar com i18n
- Linha 67: `(+${{ addon.price }})` → formatar com i18n
- Linha 124: Print template com `ORDER:`, `CUSTOMER:`, `ADDRESS:`, `PHONE:`, `TOTAL:` → traduzir

Chaves i18n:
```
partner.orders.print_label.order
partner.orders.print_label.customer
partner.orders.print_label.address
partner.orders.print_label.phone
partner.orders.print_label.total
```

#### 2.4 Products/Index.vue — Validação e Categorias

Arquivo: `resources/js/Pages/Partner/Products/Index.vue`

- Linha 88: `'Price must be positive'` → `$t('partner.products.validation.price_positive')`
- Linha 209: Array `['Pizza', 'Burger', 'Dessert', 'Drinks', 'Sushi']` → traduzir via `$t()` com chaves ou lookup dinâmico

Chaves i18n:
```
partner.products.validation.price_positive
partner.products.categories.pizza
partner.products.categories.burger
partner.products.categories.dessert
partner.products.categories.drinks
partner.products.categories.sushi
```

#### 2.5 Addons/Index.vue — Validação

Arquivo: `resources/js/Pages/Partner/Addons/Index.vue`

- Linha 68: `'Price must be non-negative'` → `$t('partner.addons.validation.price_non_negative')`

#### 2.6 Corrigir Chaves i18n Copiadas

| Arquivo | Linha | Chave Atual | Chave Correta |
|---------|-------|-------------|---------------|
| `Regions/Index.vue` | 67 | `partner.products.confirm_delete` | `partner.regions.confirm_delete` |
| `Addons/Index.vue` | 84 | `partner.products.confirm_delete` | `partner.addons.confirm_delete` |
| `Regions/Create.vue` | 105, 109 | `admin.regions.error.no_polygon` | `partner.regions.error.no_polygon` |
| `Regions/Edit.vue` | 105, 109 | `admin.regions.error.no_polygon` | `partner.regions.error.no_polygon` |

Adicionar chaves `partner.regions.confirm_delete`, `partner.addons.confirm_delete`, `partner.regions.error.no_polygon` nos arquivos de tradução.

### 3. Páginas Stub — Implementar Conteúdo Real

Substituir os arquivos abaixo que atualmente contém apenas `<h1>` placeholder + `<p>` por implementações funcionais seguindo os padrões das páginas customer premium.

#### 3.1 Settings/Index.vue

**Rota**: `/partner/settings`
**Controller**: `PartnerSettingsController`
**Arquivo**: `resources/js/Pages/Partner/Settings/Index.vue`

Implementar formulário com:
- Horário de funcionamento (dias da semana + abertura/fechamento)
- Tempo médio de preparo (minutos)
- Taxa de entrega (delivery fee)
- Status do parceiro (aberto/fechado)
- Métodos de pagamento aceitos
- Botão salvar com snackbar de feedback

Dados devem ser carregados via `partnerService.getDashboardStats()` ou novo endpoint. Se o backend não tiver endpoint de settings, criar seção estática que persiste localmente (preparando para backend futuro).

#### 3.2 Products/Show.vue

**Rota**: `/partner/products/{id}`
**Arquivo**: `resources/js/Pages/Partner/Products/Show.vue`

Implementar detalhes do produto com:
- Card com imagem, nome, descrição, preço, categoria, status
- Lista de addons vinculados
- Gráfico ou badge de pedidos que contêm este produto
- Botões "Editar" e "Voltar"

#### 3.3 Products/Edit.vue

**Rota**: `/partner/products/{id}/edit`
**Arquivo**: `resources/js/Pages/Partner/Products/Edit.vue`

Reaproveitar o formulário do modal em `Products/Index.vue` como página standalone:
- Carregar dados do produto via `partnerService.getMenu()` ou filtrar do ID passado como prop
- Mesmos campos do modal: nome, descrição, preço, categoria, imagem, addons
- `partnerService.updateProduct(id, data)` no submit

#### 3.4 Products/Create.vue

**Rota**: `/partner/products/create`
**Arquivo**: `resources/js/Pages/Partner/Products/Create.vue`

Versão standalone do formulário de criação:
- Mesmo formulário do modal em `Products/Index.vue`
- `partnerService.createProduct(data)` no submit
- Redirecionar para `/partner/products` após sucesso

#### 3.5 Products/Variations/Index.vue, Create.vue, Edit.vue

**Rotas**: 
- `/partner/products/{product}/variations`
- `/partner/products/{product}/variations/create`
- `/partner/products/{product}/variations/edit`

**Arquivos**: `resources/js/Pages/Partner/Products/Variations/*.vue`

Implementar CRUD de variações de produto:
- Index: tabela com nome, preço adicional, status, ações
- Create: formulário (nome, preço adicional, status)
- Edit: formulário preenchido com dados da variação
- Usar `productId`/`variationId` das props do Inertia

#### 3.6 Addons/Create.vue, Edit.vue, Show.vue

**Arquivos**: `resources/js/Pages/Partner/Addons/{Create,Edit,Show}.vue`

- Create: Formulário standalone de criação de addon (nome, preço, status, produtos vinculados)
- Edit: Formulário preenchido, `partnerService.updateAddon(id, data)`
- Show: Card com detalhes do addon, produtos que o utilizam

#### 3.7 Regions/Show.vue

**Arquivo**: `resources/js/Pages/Partner/Regions/Show.vue`

- Exibir detalhes da região com mapa (`MapRegionEditor` em modo leitura)
- Nome, coordenadas/polígono, status (ativo/inativo)
- Botões Editar e Voltar

#### 3.8 Orders/Create.vue

**Arquivo**: `resources/js/Pages/Partner/Orders/Create.vue`

- Formulário de criação manual de pedido
- Selecionar cliente (dropdown ou busca)
- Selecionar produtos do cardápio do parceiro
- Calcular total automaticamente
- `partnerService` ou API direta para criar pedido

#### 3.9 Orders/BatchCreate.vue

**Arquivo**: `resources/js/Pages/Partner/Orders/BatchCreate.vue`

- Upload de CSV ou formulário de múltiplos pedidos
- Preview dos pedidos antes de confirmar
- Criação em lote via API

### 4. Design Premium — Glassmorphism + Animações

Aplicar o padrão visual usado em `Customer/Orders/Index.vue` e `Customer/Orders/Show.vue` nas páginas do partner.

Padrão a aplicar:
- `border-radius: 28px` em cards principais
- `background: rgba(255, 255, 255, 0.8)` + `backdrop-filter: blur(12px)` (glassmorphism)
- `border: 1px solid rgba(var(--v-border-color), 0.1)`
- `transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)`
- `transform: translateY(-4px)` no hover
- Gradientes em avatares e progress bars: `linear-gradient(135deg, var(--v-theme-primary) 0%, #6366f1 100%)`
- `premium-tabs` com `:deep(.v-tab--selected)` estilizado

#### 4.1 Dashboard.vue

- Cards de métricas com glassmorphism
- Gráficos com fundo premium
- Tabela de pedidos recentes com linhas estilizadas

#### 4.2 Orders/Index.vue (o standalone, não o de baixo de Orders/)

- Aplicar glassmorphism nos cards de pedidos
- Badges de status com cores vivas

#### 4.3 Products/Index.vue

- Cards de produtos com hover animation
- Modal de criação/edição com fundo premium

#### 4.4 Profile.vue

- Seções do perfil com cards glassmorphism
- Avatar com gradiente

#### 4.5 Regions/Index.vue

- Cards de região com design premium
- Badge ativo/inativo estilizado

### 5. Bug Fixes — Backend

#### 5.1 Criar método `metrics()` no API PartnerController

Arquivo: `app/Http/Controllers/API/PartnerController.php`

Rota registrada: `GET /api/v1/partner/metrics` — atualmente retorna 500 porque o método não existe.

Implementar:
```php
public function metrics(Request $request)
{
    $partnerId = $request->user()->id;

    $totalDeliveries = Delivery::where('logistics_partner_id', $partnerId)->count();
    $averageTime = Delivery::where('logistics_partner_id', $partnerId)
        ->whereNotNull('completed_at')
        ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, completed_at)'));
    $averageRating = Delivery::where('logistics_partner_id', $partnerId)
        ->whereHas('rating')
        ->avg('ratings.score');

    return response()->json([
        'total_deliveries' => $totalDeliveries,
        'average_time' => round($averageTime ?? 0),
        'average_rating' => round($averageRating ?? 0, 1),
    ]);
}
```

#### 5.2 Remover código órfão de `$node`

Arquivo: `app/Http/Controllers/API/PartnerController.php` (linhas 86-116)

Remover todo o código após o fechamento do método `show()` que referencia a variável `$node` — é resquício da tabela `nodes` que foi deletada na migration `2026_05_10_045540_remove_nodes_table.php`.

#### 5.3 Verificar coluna `slug` na tabela `users`

Tanto `MenuController` quanto `API/PartnerController` usam `User::where('slug', $slug)`. Verificar se:
- Existe migration que adiciona `slug` à tabela `users`
- Se não existir, criar migration adicionando `slug` (unique, nullable) e popular para os partners seeded

#### 5.4 Products/Index.vue — Corrigir import ausente

Arquivo: `resources/js/Pages/Partner/Products/Index.vue` (linha 294)

`api.post()` é chamado mas `api` não é importado. Substituir por `partnerService.createProduct()` ou adicionar `import api from '@/services/api'`.

### 6. Testes

#### 6.1 Atualizar testes Vitest existentes

Arquivos:
- `resources/js/Pages/__tests__/PartnerDashboard.spec.js`
- `resources/js/Pages/__tests__/PartnerProducts.spec.js`
- `resources/js/Pages/__tests__/PartnerLogistics.spec.js`

Garantir que assertions de i18n refletem as novas chaves e labels traduzidas.

#### 6.2 Adicionar testes Vitest para novas páginas

Onde páginas stub forem implementadas (task 3), adicionar testes unitários:
- Settings/Index.vue — renderiza campos, salva configurações
- Products/Show.vue — exibe detalhes do produto
- Products/Create.vue — formulário de criação funciona
- Addons/Create.vue, Edit.vue — formulários funcionam

#### 6.3 Adicionar testes Cypress E2E para fluxos novos

Expandir testes E2E existentes em `cypress/e2e/partner/`:
- Navegação entre todas as páginas do partner via sidebar
- Criação de produto via página standalone (`/partner/products/create`)
- Atualização de configurações (`/partner/settings`)
- Visualização de detalhes de região (`/partner/regions/{id}`)

## Constraints

- Seguir o padrão visual estabelecido em `Customer/Orders/Index.vue` e `Customer/Orders/Show.vue` como referência de qualidade
- Manter i18n: todo novo texto deve ter chave em `pt-BR.json` e `en.json`
- Usar `data-cy` para todos os seletores em novos componentes
- Páginas stub que forem substituídas devem manter a mesma rota para não quebrar links existentes
- Não modificar `database/seeders/` ou `database/factories/`
- Não modificar testes E2E existentes que estejam passando
- Seguir padrão de `partnerService` para chamadas API, evitando `api` direto

## Success Criteria

- `PartnerLayout.vue` exibe título dinâmico baseado na rota atual (não mais "Partner Dashboard" fixo)
- Nav items no layout estão traduzidos via `$t()`
- Nenhuma string em inglês hardcoded nas páginas: Profile, Dashboard, Products, Addons, OrderDetails
- Prints de etiqueta em `OrderDetails.vue` estão em pt-BR
- Chaves i18n copiadas incorretamente foram corrigidas
- Rota `GET /api/v1/partner/metrics` retorna dados (não 500)
- Código órfão `$node` removido do `API/PartnerController.php`
- Páginas stub (12) substituídas por implementações funcionais
- Design premium (glassmorphism, border-radius 28px, animações hover) aplicado nas páginas principais do partner
- `npm run test` passa (Vitest)
- `npm run test:e2e:local` passa (Cypress)
