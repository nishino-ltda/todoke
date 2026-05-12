# Sprint 14 — Courier Pages: i18n, Layout Fixes, Premium UI & Delivery Backend

## Context

As páginas do courier foram implementadas com funcionalidade básica no Dashboard, mas possuem problemas críticos: título fixo "Courier Portal" sem i18n, 7 páginas stub sem implementação, Profile com ~25 strings em inglês, 2 métodos faltando no backend (500 error), eventos de real-time que nunca são disparados, criação duplicada de DeliveryAssignment, validação de latitude incorreta, e ausência de API para listar entregas disponíveis.

O Dashboard do courier (`Dashboard.vue`) é funcional com i18n, mas não possui o padrão visual premium (glassmorphism, gradientes, animações) que já existe nas páginas do customer e partner.

## Seeded Test Credentials (password: `password123`)

| Papel | Email |
|-------|-------|
| Admin | admin@todoke.test |
| Partner | partner@todoke.test |
| Courier | courier@todoke.test |
| Customer | customer@todoke.test |

## Tasks

### 1. Layout Fix — Título Dinâmico e i18n nos Nav Items

Arquivo: `resources/js/Layouts/CourierLayout.vue`

- Substituir `v-app-bar-title` hardcoded "Courier Portal" por computed property dinâmica `currentPageTitle` (igual ao `CustomerLayout` e ao `PartnerLayout` recém-refatorado)
- Aplicar `useI18n()` e `$t()` nos `title` dos `navItems`
- Adicionar fallback traduzido para `user?.name || user?.email || t('courier.nav.user')`
- `v-app-bar` com `LanguageSelector` (já existe)

Chaves i18n a adicionar em `pt-BR.json` e `en.json`:
```
courier.nav.dashboard
courier.nav.deliveries
courier.nav.hybrid_deliveries
courier.nav.service_area
courier.nav.settings
courier.nav.profile
courier.nav.access_as_customer
courier.nav.user
courier.title
```

### 2. Internacionalização — Strings Faltantes

#### 2.1 Profile.vue

Arquivo: `resources/js/Pages/Courier/Profile.vue`

Traduzir via `$t()` todos os labels e mensagens. Atualmente não usa `useI18n()`.

| Localização | Strings |
|-------------|---------|
| Título | `<h1>Courier Profile</h1>` |
| Form fields | `Name`, `Email`, `Phone` |
| Foto | `Profile Photo`, `Choose a photo` |
| Veículo | `License Number`, `Vehicle Type` (items `motorcycle`/`car`/`bicycle`) |
| Botão | `Save` |
| Roles section | `Additional Roles`, `Access as Customer`, `Want to become a partner?`, `Become a Partner` |
| Partner form | `Business Name`, `Business Type` (items), `Tax ID`, `Address` |
| Botão role | `Submit` |
| Status | `You are already a partner.` |
| Snackbar | `Profile updated successfully`, `Failed to save profile`, `Role added successfully!`, `Failed to add role` |

Chaves i18n:
```
courier.profile.title
courier.profile.name
courier.profile.email
courier.profile.phone
courier.profile.profile_photo
courier.profile.choose_photo
courier.profile.license_number
courier.profile.vehicle_type
courier.profile.vehicle_type_options.motorcycle
courier.profile.vehicle_type_options.car
courier.profile.vehicle_type_options.bicycle
courier.profile.save
courier.profile.additional_roles
courier.profile.access_as_customer
courier.profile.become_partner
courier.profile.become_partner_title
courier.profile.business_name
courier.profile.business_type
courier.profile.business_type_options.restaurant
courier.profile.business_type_options.market
courier.profile.business_type_options.pharmacy
courier.profile.tax_id
courier.profile.address
courier.profile.submit
courier.profile.already_partner
courier.profile.success.updated
courier.profile.error.save
courier.profile.success.role_added
courier.profile.error.role_added
```

#### 2.2 ServiceArea/Index.vue

Arquivo: `resources/js/Pages/Courier/ServiceArea/Index.vue` (linha 43)

- `t('partner.actions.save', 'Save Area')` → `t('courier.service_area.save', 'Save Area')`

### 3. Páginas Stub — Implementar Conteúdo Real

#### 3.1 Settings/Index.vue

Arquivo: `resources/js/Pages/Courier/Settings/Index.vue`

Substituir stub por formulário funcional com:
- Preferências de notificação (push, email, SMS)
- Unidade de distância (km/mi)
- Idioma preferido
- Disponibilidade padrão ao logar
- Botão salvar com snackbar de feedback

#### 3.2 Deliveries/Index.vue

Arquivo: `resources/js/Pages/Courier/Deliveries/Index.vue`

Implementar lista de entregas do courier:
- Abas: "Ativas" / "Histórico"
- Cards com origem, destino, status, valor
- Cada card link para `Deliveries/Show.vue`
- Filtro por status
- Dados carregados via API (`deliveryService`)

#### 3.3 Deliveries/Show.vue

Arquivo: `resources/js/Pages/Courier/Deliveries/Show.vue`

Implementar detalhes da entrega:
- Timeline de status com datas
- Mapa (`DeliveryMap.vue`) mostrando rota origem → destino
- Informações do cliente (nome, endereço)
- Descrição do item, peso, dimensões
- Código de confirmação (se aplicável)
- Botões de ação (atualizar status, entrar em contato)
- Mensagens da entrega (reaproveitar `DeliveryMessagingController`)

#### 3.4 HybridDeliveries/Index.vue

Arquivo: `resources/js/Pages/Courier/HybridDeliveries/Index.vue`

Implementar lista de entregas híbridas:
- Tabela com colunas: ID, origem, destino, estágio atual, status
- Badge indicando estágio atual (drone/courier)
- Ações: "Ver Detalhes", "Handoff" (se estágio atual for courier)

#### 3.5 HybridDeliveries/Show.vue

Arquivo: `resources/js/Pages/Courier/HybridDeliveries/Show.vue`

Implementar detalhes da entrega híbrida:
- Mapa multi-estágio (rota completa com pontos de handoff)
- Timeline de estágios com status de cada um
- Informações do drone (se aplicável)
- Botões de ação por estágio

#### 3.6 Dashboard/Index.vue — Remover Rota Duplicada

Arquivo: `resources/js/Pages/Courier/Dashboard/Index.vue`

Este arquivo de 24 linhas é um stub. Verificar rota:
- `CourierLayout` nav aponta `/courier` → `CourierController@index` ou redirect para `courier.dashboard`
- Rota `courier.dashboard` → `Courier\DashboardController@index` renderiza `Courier/Dashboard`
- `Dashboard/Index.vue` parece inacessível ou conflitante

Remover `Dashboard/Index.vue` e ajustar rota se necessário. O Dashboard funcional é `Dashboard.vue`.

#### 3.7 Courier.vue — Remover Página Legado

Arquivo: `resources/js/Pages/Courier/Courier.vue`

Página stub de 14 linhas. Se a rota `/courier` redireciona para `/courier/dashboard`, este arquivo pode ser removido.

### 4. Design Premium — Glassmorphism + Animações

Aplicar o mesmo padrão visual usado no Partner e Customer.

#### 4.1 Dashboard.vue

Arquivo: `resources/js/Pages/Courier/Dashboard.vue`

- Status card com glassmorphism
- Available deliveries cards com `border-radius: 28px`, hover animation (`translateY(-4px)`)
- Active delivery card com gradiente e animações
- Loading states com skeleton

#### 4.2 Profile.vue

Arquivo: `resources/js/Pages/Courier/Profile.vue`

- Cards com glassmorphism
- Avatar com gradiente
- Seções com separação visual premium

#### 4.3 ServiceArea/Index.vue

Arquivo: `resources/js/Pages/Courier/ServiceArea/Index.vue`

- Map container premium
- Info cards com glassmorphism

### 5. Bug Fixes — Backend (Crítico)

#### 5.1 Criar métodos `create()` e `handoff()` no HybridDeliveryController

Arquivo: `app/Http/Controllers/Courier/HybridDeliveryController.php`

Atualmente as rotas existem mas os métodos não — 500 error.

```php
public function create()
{
    return Inertia::render('Courier/HybridDeliveries/Create');
}

public function handoff($id)
{
    return Inertia::render('Courier/HybridDeliveries/Handoff', ['deliveryId' => $id]);
}
```

Criar também os arquivos Vue correspondentes:
- `resources/js/Pages/Courier/HybridDeliveries/Create.vue`
- `resources/js/Pages/Courier/HybridDeliveries/Handoff.vue`

#### 5.2 Disparar evento `NewDeliveryAvailable`

Arquivo: `app/Http/Controllers/API/DeliveryManagementController.php`

No método `store()`, após criação bem-sucedida da entrega, disparar:
```php
event(new NewDeliveryAvailable($delivery));
```

Arquivo: `app/Events/NewDeliveryAvailable.php`

Verificar se o evento transmite dados corretos no canal `courier.available`.

#### 5.3 Disparar evento `DeliveryStatusChanged`

Arquivo: `app/Services/DeliveryStatusService.php`

Nos métodos `updateStatus()` e `updateStageStatus()`, disparar:
```php
event(new DeliveryStatusChanged($delivery));
```

Arquivo: `app/Events/DeliveryStatusChanged.php`

Verificar canal `deliveries.{deliveryId}` e dados transmitidos.

#### 5.4 Corrigir criação duplicada de DeliveryAssignment

Arquivos:
- `app/Models/Delivery.php` (booted — created event)
- `app/Http/Controllers/API/DeliveryManagementController.php` (store — linhas 83-88)
- `app/Services/DeliveryStatusService.php` (acceptDelivery)

Escolher UM local para criar assignments. Recomendação: manter no model event (`Delivery::booted()`) e remover dos outros dois locais.

#### 5.5 Corrigir `is_hybrid` nunca setado

Arquivo: `app/Http/Controllers/API/DeliveryHelpersTrait.php`

No método `prepareDeliveryData()`, adicionar:
```php
if (!empty($data['stages'])) {
    $data['is_hybrid'] = true;
}
```

#### 5.6 Corrigir validação de latitude

Arquivo: `app/Http/Controllers/API/DeliveryHelpersTrait.php` (linha 38)

`destination.lat` validado como `between:-180,180` — corrigir para `between:-90,90`.

#### 5.7 Corrigir autorização no `show()` do DeliveryManagementController

Arquivo: `app/Http/Controllers/API/DeliveryManagementController.php` (linha 111)

Adicionar `courier_id` na autorização:
```php
$user->id != $delivery->courier_id
```

#### 5.8 Corrigir label `logistics_partner` na resposta do show()

Arquivo: `app/Http/Controllers/API/DeliveryManagementController.php` (linha 118)

Se o campo retornado for o courier, usar `'courier'` como chave. Se for logistics partner, retornar o dado correto.

#### 5.9 Criar API endpoint para listar entregas disponíveis

Arquivo: `routes/api.php`

Adicionar rota:
```php
Route::get('/deliveries/available', [DeliveryManagementController::class, 'available']);
```

Arquivo: `app/Http/Controllers/API/DeliveryManagementController.php`

Criar método:
```php
public function available(Request $request)
{
    $deliveries = Delivery::whereNull('courier_id')
        ->whereIn('status', ['pending'])
        ->with(['customer:id,name'])
        ->paginate(20);

    return response()->json($deliveries);
}
```

#### 5.10 Remover código morto

Remover:
- `app/Http/Controllers/DeliveryController.php` (TODO stubs completo, sem rotas)
- `app/Http/Controllers/API/DeliveryController.php` (classe vazia)

#### 5.11 Corrigir `rejectDelivery` no frontend

Arquivo: `resources/js/services/delivery.js` (linhas 64-68)

Atualmente é um no-op local. Se não há endpoint de rejeição no backend, criar:
```php
// routes/api.php
Route::patch('/deliveries/{id}/reject', [DeliveryStatusController::class, 'reject']);
```

Ou documentar claramente que reject é apenas local e remover o mock enganoso.

### 6. Testes

#### 6.1 Implementar testes Cypress stub em dashboard.cy.js

Arquivo: `cypress/e2e/courier/dashboard.cy.js`

Substituir 4 `cy.fail()` por testes reais:
- availability toggle
- delivery request display
- accept/reject deliveries
- delivery status updates

#### 6.2 Implementar testes PHP marcados como incomplete

Arquivos:
- `tests/Feature/Delivery/DeliveryTrackingTest.php` — `testOfflineDeliveryTracking`
- `tests/Feature/Delivery/HybridDeliveryEdgeCasesTest.php` — `test_offline_scenario_handling`

Implementar ou remover os `markTestIncomplete`.

#### 6.3 Adicionar testes Vitest para novas páginas

Onde páginas stub forem implementadas (task 3), adicionar testes:
- `CourierDeliveries.spec.js` — Deliveries/Index.vue e Deliveries/Show.vue
- `CourierSettings.spec.js` — Settings/Index.vue
- `CourierHybridDeliveries.spec.js` — HybridDeliveries pages

#### 6.4 Atualizar testes Vitest existentes

Arquivo: `resources/js/Pages/__tests__/CourierDashboard.spec.js`

Garantir assertions de i18n refletem as novas chaves e labels traduzidas.

#### 6.5 Testar eventos de real-time

Adicionar testes PHPUnit para verificar que:
- `NewDeliveryAvailable` é disparado no `store()` do `DeliveryManagementController`
- `DeliveryStatusChanged` é disparado no `updateStatus()` do `DeliveryStatusService`

## Constraints

- `Dashboard.vue` (432 linhas) é o dashboard funcional — não substituir, apenas aprimorar
- Manter i18n: todo novo texto deve ter chave em `pt-BR.json` e `en.json`
- Usar `data-cy` para todos os seletores em novos componentes
- Páginas stub que forem substituídas devem manter a mesma rota
- Não modificar `database/seeders/` ou `database/factories/`
- Não modificar testes E2E existentes que estejam passando (courier-flow.cy.js, courier-dashboard.cy.js, courier_flow.cy.js)
- Seguir padrão de `deliveryService` (delivery.js) para chamadas API
- Remover código morto, não apenas comentar

## Success Criteria

- `CourierLayout.vue` exibe título dinâmico baseado na rota atual (não mais "Courier Portal" fixo)
- Nav items no layout estão traduzidos via `$t()`
- `Profile.vue` — nenhuma string em inglês hardcoded
- `ServiceArea/Index.vue` — chave corrigida de `partner.actions.save` para `courier.service_area.save`
- 7 páginas stub substituídas por implementações funcionais (Settings, Deliveries, HybridDeliveries)
- Rotas duplicadas/legado removidas ou redirecionadas
- Design premium aplicado no Dashboard, Profile e ServiceArea
- `HybridDeliveryController.create()` e `handoff()` existem (fim do 500 error)
- `NewDeliveryAvailable` e `DeliveryStatusChanged` são efetivamente disparados
- Criação de `DeliveryAssignment` ocorre em UM local apenas
- `is_hybrid` é setado corretamente quando `stages` está presente
- Validação de latitude corrigida para `between:-90,90`
- Courier consegue ver próprias entregas via API `show()`
- Endpoint `GET /api/v1/deliveries/available` existe e retorna entregas disponíveis
- Código morto removido (`DeliveryController.php` e `API/DeliveryController.php`)
- `rejectDelivery` tem endpoint real ou mock documentado
- `cypress/e2e/courier/dashboard.cy.js` — 0 `cy.fail()` restantes
- `npm run test` passa (Vitest + PHPUnit)
- `npm run test:e2e:local` passa (Cypress)
