# Sprint 16 — Geocoding, Mapas e Gestão de Endereços

## Resultado Final

Todos os 5 objetivos da sprint foram implementados. O `MapController` agora consulta serviços reais (Nominatim → OSM, fallback DeepSeek), o modelo `Address` com CRUD completo foi criado, o checkout ganhou gestão de endereços salvos e pinpoint no mapa, parceiros agora têm coordenadas no banco, e a origem dos pedidos inclui lat/lng.

---

## ✅ Concluído

### 1. Backend: Geocoding Real

#### 1.1 Serviço de Geocoding
- **Arquivo**: `app/Services/GeocodingService.php`
- **`geocode(string $address): array`** — Nominatim como provedor primário; se retornar vazio, cai no DeepSeek
  - Nominatim: `GET https://nominatim.openstreetmap.org/search?q={address}&format=json&limit=5`
  - DeepSeek fallback: `POST https://api.deepseek.com/v1/chat/completions` com prompt para extrair `{address, lat, lng}`
- **`reverseGeocode(float $lat, float $lng): array`** — Nominatim reverse
- **`getDistance(array $origin, array $dest): array`** — OSRM (Open Source Routing Machine) com fallback para Haversine
  - OSRM: `GET https://router.project-osrm.org/route/v1/driving/{lng},{lat};{lng},{lat}?overview=false`
- Rate limit e User-Agent respeitados conforme termos do Nominatim

#### 1.2 MapController Atualizado
- **Arquivo**: `app/Http/Controllers/API/MapController.php`
- Injeta `GeocodingService` via constructor
- `geocode()`, `reverseGeocode()`, `distance()` delegam ao service real
- Estrutura de resposta mantida: `{data: {lat, lng, address}}` / `{data: {distance_km, duration_seconds}}`

#### 1.3 Configuração
- **Arquivo**: `config/services.php`
  ```php
  'deepseek' => ['api_key' => env('DEEPSEEK_API_KEY')],
  'nominatim' => ['user_agent' => env('NOMINIM_USER_AGENT', 'TodokeApp/1.0')],
  ```

#### 1.4 Testes — GeocodingService
- **Arquivo**: `tests/Unit/Services/GeocodingServiceTest.php` — 6 testes:
  - Nominatim geocode com sucesso
  - Fallback DeepSeek quando Nominatim retorna vazio
  - Retorno raw quando ambos falham
  - Reverse geocode
  - Haversine distance
  - OSRM distance quando disponível

#### 1.5 Testes — MapController
- **Arquivo**: `tests/Feature/MapControllerTest.php` — 4 testes:
  - Geocode endpoint com service mockado
  - Validação de campo obrigatório
  - Reverse geocode
  - Distance

---

### 2. Backend: Modelo de Endereços

#### 2.1 Migration
- **Arquivo**: `database/migrations/2026_05_13_000715_create_addresses_table.php`
- Campos: `id`, `user_id` (FK → users, cascade delete), `label`, `address`, `complement`, `neighborhood`, `city`, `state`(2), `zip_code`, `lat`(10,7), `lng`(10,7), `is_default`(bool), timestamps

#### 2.2 Model Address
- **Arquivo**: `app/Models/Address.php`
- `HasFactory`, `$fillable` com todos os campos, `$casts` para `lat`/`lng`/`is_default`
- `belongsTo(User::class)`
- `booted()`: ao salvar com `is_default=true`, remove flag de outros endereços do mesmo usuário

#### 2.3 User Model
- **Arquivo**: `app/Models/User.php`
- `hasMany(Address::class)` adicionado
- `latitude`, `longitude`, `address_complement` adicionados ao `$fillable`
- Casts: `latitude => 'decimal:7'`, `longitude => 'decimal:7'`

#### 2.4 AddressController
- **Arquivo**: `app/Http/Controllers/API/AddressController.php`
- 6 métodos: `index`, `store`, `show`, `update`, `destroy`, `setDefault`
- Sempre escopado ao usuário autenticado (`$request->user()->addresses()`)
- Validação: `label`, `address`, `city`, `state` required; `lat`/`lng` opcionais

#### 2.5 Rotas
- **Arquivo**: `routes/api.php`
- Grupo autenticado (`auth:sanctum`), prefixo `/api/v1/customer/addresses`:
  - `GET /`, `POST /`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`, `PATCH /{id}/default`

#### 2.6 Testes — AddressController
- **Arquivo**: `tests/Feature/AddressControllerTest.php` — 7 testes:
  - Listar, criar, validar campos obrigatórios, atualizar, deletar, set default, acesso não autenticado
- **Arquivo**: `database/factories/AddressFactory.php` — factory para testes

---

### 3. Frontend: Gestão de Endereços

#### 3.1 AddressManagerModal
- **Arquivo**: `resources/js/Components/AddressManagerModal.vue`
- Modal Vuetify (`v-dialog`, 600px) com dois modos:
  - **Lista**: endereços salvos com ícones, botões editar/excluir, "Definir como padrão"
  - **Formulário**: label (select: Casa/Trabalho/Outro), address, complement, neighborhood, city, state, zip_code, is_default switch
- Integração com API (`/api/v1/customer/addresses`)
- Emite `address-selected` ao criar novo endereço

#### 3.2 AddressInput Atualizado
- **Arquivo**: `resources/js/Components/AddressInput.vue`
- Ao montar com `geocode=true`, busca endereços salvos via API
- Renderiza chips de endereços salvos abaixo do combobox
- Botão "Gerenciar Endereços" que abre `AddressManagerModal`
- `selectSavedAddress(addr)` emite `{address, lat, lng, address_id}`
- Sugestões do combobox combinam endereços salvos + resultados do geocoding

#### 3.3 Testes — AddressManagerModal
- **Arquivo**: `resources/js/Components/__tests__/AddressManagerModal.spec.js`

---

### 4. Frontend: Map Pinpoint

#### 4.1 MapPinpointModal
- **Arquivo**: `resources/js/Components/MapPinpointModal.vue`
- Modal fullscreen com mapa Leaflet (OpenStreetMap tiles)
- Props: `lat`, `lng` (coordenadas iniciais)
- Marcador **dragável** — ao soltar, atualiza lat/lng exibidos e faz reverse geocode
- Exibe endereço selecionado, latitude e longitude em campos readonly
- Botões: "Confirmar Localização" (emite `{lat, lng, address}`) e "Cancelar"
- Limpa o mapa ao fechar o modal

#### 4.2 Checkout com Pinpoint
- **Arquivo**: `resources/js/Pages/Customer/Checkout.vue`
- Botão "Ajustar no Mapa" exibido após endereço preenchido
- Abre `MapPinpointModal` com coordenadas atuais do formulário
- Ao confirmar, atualiza `formData.lat/lng/address`

#### 4.3 Testes — MapPinpointModal
- **Arquivo**: `resources/js/Components/__tests__/MapPinpointModal.spec.js`

---

### 5. Backend: Coordenadas de Parceiros

#### 5.1 Migration
- **Arquivo**: `database/migrations/2026_05_13_000714_add_lat_lng_to_users_table.php`
- Colunas adicionadas a `users`: `latitude`(10,7), `longitude`(10,7), `address_complement`

#### 5.2 User Model
- **Arquivo**: `app/Models/User.php`
- `latitude`, `longitude`, `address_complement` no `$fillable`
- Casts: `decimal:7`

#### 5.3 PartnerController
- **Arquivo**: `app/Http/Controllers/API/PartnerController.php`
- `show()` agora retorna `latitude`, `longitude`, `address` no objeto `partner`
- Novo método `nearby(Request $request)` — endpoint público `GET /api/v1/partners/nearby?lat=&lng=&radius=`
  - Filtra parceiros ativos com coordenadas dentro do raio (Haversine)
  - Retorna `{data: [{... partner com distance_km}]}`
- Rota adicionada em `routes/api.php`

#### 5.4 OrderController Corrigido
- **Arquivo**: `app/Http/Controllers/API/OrderController.php` (linha 161-171)
- `origin` agora inclui `lat` e `lng` do parceiro:
  ```php
  'origin' => [
      'address' => $partner->address ?? 'Restaurante',
      'lat' => $partner->latitude,
      'lng' => $partner->longitude,
  ],
  ```

#### 5.5 Testes — DeliveryOrigin
- **Arquivo**: `tests/Feature/Delivery/DeliveryOriginTest.php` — 2 testes:
  - Origin contém lat/lng do parceiro
  - Origin com coordenadas nulas (graceful handling)

---

### 6. Correções Adicionais

#### 6.1 CustomerDiscoveryMap — dados reais
- **Arquivo**: `resources/js/Components/CustomerDiscoveryMap.vue`
- Removeu coordenadas mockadas (random offset)
- Agora usa endpoint `GET /api/v1/partners/nearby?lat=&lng=&radius=10`

#### 6.2 DeliveryMap — pendente
- A rota real via OSRM (Leaflet Routing Machine) não foi implementada nesta sprint
- O componente continua usando polyline simples entre origem e destino
- O endpoint de distância (`/api/v1/map/distance`) já usa OSRM com fallback Haversine

#### 6.3 i18n
- **Arquivos**: `resources/lang/en.json`, `resources/lang/pt-BR.json`
- 23 novas chaves adicionadas para `components.address.*`, `components.map_pinpoint.*`, `checkout.adjust_on_map`

---

## Arquivos Criados

| Arquivo | Linhas | Responsabilidade |
|---|---|---|
| `app/Services/GeocodingService.php` | 141 | Serviço de geocoding (Nominatim + DeepSeek + OSRM/Haversine) |
| `app/Models/Address.php` | 51 | Modelo de endereço do cliente |
| `app/Http/Controllers/API/AddressController.php` | 87 | CRUD de endereços |
| `database/migrations/2026_05_13_000715_create_addresses_table.php` | 37 | Tabela de endereços |
| `database/migrations/2026_05_13_000714_add_lat_lng_to_users_table.php` | 28 | Colunas lat/lng em users |
| `database/factories/AddressFactory.php` | 22 | Factory para testes |
| `resources/js/Components/AddressManagerModal.vue` | 159 | Modal de gerenciamento de endereços |
| `resources/js/Components/MapPinpointModal.vue` | 132 | Modal de pinpoint no mapa |
| `tests/Unit/Services/GeocodingServiceTest.php` | 112 | Testes do GeocodingService |
| `tests/Feature/MapControllerTest.php` | 104 | Testes do MapController |
| `tests/Feature/AddressControllerTest.php` | 119 | Testes do AddressController |
| `tests/Feature/Delivery/DeliveryOriginTest.php` | 111 | Testa origin lat/lng em pedidos |
| `resources/js/Components/__tests__/AddressManagerModal.spec.js` | 68 | Teste do AddressManagerModal |
| `resources/js/Components/__tests__/MapPinpointModal.spec.js` | 54 | Teste do MapPinpointModal |

## Arquivos Modificados

| Arquivo | O que mudou |
|---|---|
| `config/services.php` | Config deepseek + nominatim |
| `app/Models/User.php` | `hasMany(Address)`, `latitude`, `longitude`, `address_complement` |
| `app/Http/Controllers/API/MapController.php` | Injeta GeocodingService, substitui mocks |
| `app/Http/Controllers/API/OrderController.php` | Origin com lat/lng do parceiro |
| `app/Http/Controllers/API/PartnerController.php` | Retorna `latitude`/`longitude` no show(); novo método `nearby()` |
| `routes/api.php` | Rotas de endereço + `GET /partners/nearby` |
| `resources/js/Components/AddressInput.vue` | Endereços salvos, botão "Gerenciar" |
| `resources/js/Pages/Customer/Checkout.vue` | Botão "Ajustar no mapa" + MapPinpointModal |
| `resources/js/Components/CustomerDiscoveryMap.vue` | Chamada real ao endpoint `/partners/nearby` |
| `resources/lang/en.json` | 23 novas chaves i18n |
| `resources/lang/pt-BR.json` | 23 novas chaves i18n |
| `resources/js/Components/__tests__/AddressInput.spec.js` | Mock do AddressManagerModal + api |

---

## Testes

- **19 novos testes backend** (PHPUnit) — todos passando
- **11 testes frontend relevantes** (Vitest) — todos passando
- Testes pré-existentes que falham (147 backend por PDOException no UsabilityTest, 57 frontend por chart.js/Inertia router) são anteriores a esta sprint e não relacionados

---

## Fluxo Final do Checkout

1. Cliente acessa `/customer/checkout` com itens no carrinho
2. `AddressInput` carrega endereços salvos do cliente via `GET /api/v1/customer/addresses`
3. Opções disponíveis:
   - **Clicar em chip de endereço salvo** → preenche lat/lng/address automaticamente
   - **"Usar localização atual"** → `navigator.geolocation` + reverse geocode
   - **Digitar novo endereço** → geocoding com Nominatim (fallback DeepSeek)
   - **"Gerenciar Endereços"** → abre AddressManagerModal para CRUD completo
4. Botão **"Ajustar no mapa"** → abre `MapPinpointModal` com Leaflet:
   - Marcador arrastável para ajuste fino
   - Reverse geocode ao confirmar
5. Ao submeter, payload inclui `delivery.destination {lat, lng, address}`
6. Backend cria Delivery com `origin` contendo lat/lng do parceiro (`OrderController::store`)

---

## 🚀 Refinamentos de UX e Seletor Unificado (Pós-Sprint)

Após o feedback inicial, o sistema de endereços foi unificado e simplificado para uma experiência mais fluida.

### 1. Seletor de Endereço Unificado
- **Componente**: `resources/js/Components/AddressSelector.vue`
- **Funcionalidades**:
  - **Dropdown Multimodal**: Substitui o antigo input por um seletor que agrupa endereços salvos e ações (Digitar, Usar Coordenadas).
  - **Geolocalização Inteligente**: Ao escolher "Usar coordenadas", o sistema tenta obter o GPS do navegador antes de abrir o mapa.
  - **Overlay de Bloqueio**: Durante a geolocalização, um overlay global impede interações acidentais e fornece feedback visual.
  - **Extração Completa**: O serviço de Geocoding agora extrai Cidade, Estado (UF) e Bairro para cumprir os requisitos do banco de dados.
  - **Ajuste Fino**: Opção de "Ajustar no mapa" disponível tanto para endereços digitados quanto para coordenadas.
  - **Salvar ao Checkout**: Opção direta para salvar um novo endereço com rótulo customizado (ex: "Casa do Papaco") sem sair do fluxo de compra.

### 2. Gestão Dedicada de Endereços
- **Página**: `resources/js/Pages/Customer/Addresses/Index.vue`
- **Controller**: `app/Http/Controllers/Customer/AddressController.php`
- **Funcionalidades**:
  - Listagem em cards modernos com ícones dinâmicos (Casa, Trabalho, Local).
  - CRUD completo usando o mesmo componente `AddressSelector`.
  - Definição de endereço padrão com um clique.

### 3. Melhorias Técnicas
- **Debounce**: Aumentado para 800ms para evitar chamadas excessivas às APIs de geocoding.
- **Timeout GPS**: Aumentado para 10 segundos para maior confiabilidade em dispositivos móveis.
- **Mapeamento de Dados**: Correção no processamento de respostas aninhadas da API (`data.data`).
- **Interatividade**: Adicionado suporte a clique direto no mapa para mover o marcador, além do arraste.

---

## Arquivos Adicionais Criados/Modificados

| Arquivo | Status | Responsabilidade |
|---|---|---|
| `app/Http/Controllers/Customer/AddressController.php` | [NEW] | Controller para a página Inertia de endereços |
| `resources/js/Components/AddressSelector.vue` | [NEW] | Seletor unificado de endereços (substitui AddressInput) |
| `resources/js/Pages/Customer/Addresses/Index.vue` | [NEW] | Página de gestão de endereços do cliente |
| `resources/js/Components/MapPinpointModal.vue` | [MOD] | Adicionado clique no mapa e invalidateSize |
| `app/Services/GeocodingService.php` | [MOD] | Extração de Cidade, Estado e Bairro |
| `resources/js/Components/CheckoutForm.vue` | [MOD] | Integração com AddressSelector e salvamento automático |

---

## Testes Realizados
- Fluxo de Checkout com endereço salvo.
- Fluxo de Checkout digitando novo endereço e salvando.
- Fluxo de Checkout usando GPS + Ajuste no mapa.
- CRUD completo na página /customer/addresses.
- Validação de Cidade/Estado obrigatórios corrigida.

---

## Pendências / Melhorias Futuras

- [ ] **DeliveryMap com rota real** — integrar Leaflet Routing Machine ou OSRM para desenhar rota entre origem e destino
- [ ] **Cache de geocoding** — evitar chamadas repetidas à API Nominatim/DeepSeek (array cache ou Redis)
- [ ] **Spatial index** — migrar colunas `lat`/`lng` para MySQL POINT type + SPATIAL index quando houver consultas frequentes de proximidade
- [ ] **Cadastro de parceiro** — formulário para definir `latitude`/`longitude` no cadastro do partner
- [ ] **HomeNearbyPartners** — implementar filtro por proximidade usando o endpoint `/partners/nearby`
