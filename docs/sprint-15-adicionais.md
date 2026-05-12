# Sprint 15 — Addons: Criação, Associação e Exibição no Cardápio

## Context

Restaurantes precisam oferecer adicionais (bacon extra, borda recheada, etc.) que os clientes possam selecionar ao montar o pedido. A infraestrutura de dados (tabelas `addons`, `product_addon`, coluna `selected_addons` em `order_items`) já existia, mas o sistema estava incompleto: (1) o partner não conseguia navegar para a gestão de addons, (2) a API pública de produtos não retornava addons, (3) os seeders nunca associavam addons aos produtos via pivot, e (4) apenas Pizza e Burger tinham addons.

## Tasks

### 4. Gestão de Adicionais — Modal Separado na Tabela de Produtos

**Arquivos:** `Partner/Products/Index.vue`, `ProductForm.vue`, `partner.js`, `api.php`

Para melhorar a UX, a seleção de adicionais foi removida do formulário principal de criação/edição e movida para a tabela de listagem:
- **Tabela:** Nova coluna "Adicionais" exibindo um chip com a quantidade (ex: "3 Adicionais").
- **Modal:** Ao clicar no chip, abre um modal dedicado para selecionar os adicionais daquele produto.
- **Backend:** Adicionada rota `POST /api/v1/partner/products/{id}/addons` vinculada ao método `addAddons()`.
- **Bugfix:** Corrigido erro `Class Log not found` em `ProductController.php` adicionando o import da Facade.

### 6. UI/UX Premium — Floating Widgets & Mobile Optimization

**Arquivos:** `ProductDetailsModal.vue`, `AppModal.vue`, `CartIcon.vue`

Refinamento visual focado em experiência mobile e estética premium:
- **Floating Quantity Picker:** O seletor de quantidade agora flutua sobre a borda inferior da imagem do produto, centralizado em um estilo "pill".
- **Botões Full-Width:** O botão "Adicionar" no checkout agora ocupa toda a largura disponível, facilitando o uso em dispositivos móveis.
- **Interatividade nos Adicionais:** Toda a linha do adicional é clicável, não apenas o checkbox, melhorando o target de toque.
- **Scroll Inteligente:** Implementado `scrollable` em todos os modais críticos, mantendo os cabeçalhos e botões de ação fixos enquanto o conteúdo (descrições longas ou muitos adicionais) desliza.

## Arquivos Modificados

| Arquivo | Tipo |
|---------|------|
| `resources/js/Layouts/PartnerLayout.vue` | + nav item |
| `resources/lang/pt-BR.json` | + chaves adicionais + bugfix moeda |
| `resources/lang/en.json` | + chaves adicionais + bugfix moeda |
| `app/Http/Controllers/API/ProductController.php` | `index()` inclui addons + bugfix `Log` |
| `database/seeders/PartnerProductSeeder.php` | Addons por categoria + pivot |
| `routes/api.php` | + rota de atualização de addons para partner |
| `resources/js/services/partner.js` | + método `updateProductAddons` |
| `resources/js/Components/ProductForm.vue` | - removido multi-select de addons |
| `resources/js/Pages/Partner/Products/Index.vue` | + coluna e modal de gestão de adicionais |
| `resources/js/Components/Cart.vue` | fix subtotal display |
| `resources/js/Components/CartIcon.vue` | + lista de addons + fix subtotal display + scroll |
| `resources/js/Components/AppModal.vue` | + suporte a scrollable |
| `resources/js/Components/ProductDetailsModal.vue` | UI premium + floating picker + scroll |

## Fluxo Completo

```
Partner cria adicionais em /partner/addons
  → Gerencia associação na tabela /partner/products (modal dedicado por produto)
    → API pública GET /api/v1/products retorna addons por produto
      → Cliente seleciona addons e quantidade (UI Premium / Floating)
        → Carrinho exibe subtotal atualizado com adicionais (Cart.vue / CartIcon.vue)
          → Checkout envia payload mapeado para a API
            → OrderController valida compatibilidade e persiste na order_items.selected_addons
```


## Dependências

Nenhuma — toda a estrutura de dados (tabelas `addons`, `product_addon`, `order_items.selected_addons`, modelos `Addon`, `Product`, `OrderItem`) já existia de sprints anteriores.

