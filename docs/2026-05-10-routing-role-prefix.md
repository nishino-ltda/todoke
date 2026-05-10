# Ajustes de Rotas por Role

## Problema

Rotas como `/settings` estavam sendo referenciadas nos layouts sem o prefixo de role
correspondente. O `AuthenticatedLayout.vue` (usado pelo papel `customer`) apontava
para `/settings`, `/dashboard` e `/profile` — rotas que sequer existiam, resultando
em 404.

Além disso, o `CourierLayout.vue` listava itens de navegação (`History`, `Earnings`)
que apontavam para rotas nunca definidas (`/courier/history`, `/courier/earnings`).

## O que foi feito

### 1. AuthenticatedLayout.vue (customer)

Os `navItems` foram corrigidos para usar o prefixo `/customer/`:

```diff
- { text: 'Dashboard', to: '/dashboard' },
- { text: 'Profile', to: '/profile' },
- { text: 'Settings', to: '/settings' }
+ { text: 'Dashboard', to: '/customer/dashboard' },
+ { text: 'Profile', to: '/customer/profile' },
+ { text: 'Settings', to: '/customer/settings' }
```

### 2. CourierLayout.vue

Itens de navegação que apontavam para rotas inexistentes foram removidos:

- `History` → `/courier/history` (não definida, removida)
- `Earnings` → `/courier/earnings` (não definida, removida)

Os itens restantes são `Dashboard` e `Service Area`, ambos com rotas existentes.

### 3. routes/web.php — Nova rota /customer/settings

Adicionada a rota de configurações para o papel `customer`:

```php
Route::get('/settings', [CustomerSettingsController::class, 'index'])
    ->name('settings.index');
```

Dentro do grupo:
```php
Route::prefix('customer')->name('customer.')
    ->middleware('ensure.type:customer')->group(function () { ... });
```

### 4. Customer/SettingsController.php (novo)

```php
namespace App\Http\Controllers\Customer;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Settings/Index');
    }
}
```

Segue o mesmo padrão de `Partner/SettingsController` e `Courier/SettingsController`.

### 5. Pages/Customer/Settings/Index.vue (novo)

Página inicial de configurações do cliente, usando `AuthenticatedLayout`.

## Estrutura Final de Rotas por Role

| Papel    | Dashboard              | Profile              | Settings              |
|----------|------------------------|----------------------|-----------------------|
| Customer | `/customer/dashboard`  | `/customer/profile`  | `/customer/settings`  |
| Partner  | `/partner/dashboard`   | —                    | `/partner/settings`   |
| Courier  | `/courier/dashboard`   | —                    | `/courier/settings`   |
| Admin    | `/admin/dashboard`     | —                    | `/admin/settings`     |
| Support  | `/support`             | —                    | —                     |

Todas as rotas agora seguem o padrão `/{role}/{recurso}`, cada uma protegida pelo
middleware `ensure.type:{role}` correspondente.
