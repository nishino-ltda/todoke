# Todoke Design Standards

Este documento define os padrões visuais e de UX para a plataforma Todoke, garantindo consistência em todos os módulos (Customer, Partner, Courier, Admin).

## 1. Layout e Espaçamento

### Container Principal
- **Max-width**: Todas as páginas principais devem ter um `max-width` de `1200px`.
- **Padding**: O `CustomerLayout` (e outros layouts base) já fornece um `v-main` com `v-container pa-8`. Evite adicionar `pa-8` extra nas páginas para não duplicar o espaçamento lateral.

```vue
<!-- Padrão de Página -->
<div class="page-container">
  <!-- Conteúdo -->
</div>

<style scoped>
.page-container {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
```

## 2. Tipografia

### Títulos de Página
- **Tag**: `h1`
- **Classes Vuetify**: `text-h3 font-weight-black mb-2`
- **Cor**: Padrão do tema ou `primary` se for um destaque específico.

### Subtítulos
- **Classes Vuetify**: `text-subtitle-1 text-medium-emphasis mb-8`

## 3. Componentes Premium

### Cards
- **Border Radius**: `24px` ou `28px` para um visual moderno e suave.
- **Elevation**: Use `elevation="0"` com bordas sutis ou `elevation="2"` para cards flutuantes.
- **Glassmorphism**: 
  ```css
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(var(--v-border-color), 0.1);
  ```

### Botões
- **Rounded**: Sempre use `rounded="pill"` para botões de ação principal.
- **Text Transform**: Use `class="text-none"` para manter a legibilidade natural (evitar ALL CAPS em excesso).

## 4. Cores e Estados

### Paleta Principal

| Token    | Cor       | Uso                                                    |
| -------- | --------- | ------------------------------------------------------ |
| primary  | `#FF3F33` | Ações principais (CTAs, botões, destaques de marca).   |
| secondary| `#075B5E` | Elementos secundários, contraste com primary.           |
| surface  | `#FFFFFF` | Fundo de cards, diálogos e superfícies (cards brancos). |
| background| `#FFE6E1` | Fundo da página (tom peach acolhedor).                 |
| success  | `#9FC87E` | Pedidos concluídos, pronto para entrega.               |

### Status Colors

- `success` (`#9FC87E`): Pedidos concluídos, pronto para entrega.
- `warning`: Pedidos pendentes ou aguardando ação.
- `info`: Pedidos em preparação.
- `error`: Pedidos cancelados ou problemas.

### CSS Custom Properties

As cores da marca também estão disponíveis como variáveis CSS no `:root`:

```css
--brand-primary: #FF3F33;
--brand-secondary: #075B5E;
--brand-surface: #FFFFFF;
--brand-background: #FFE6E1;
--brand-success: #9FC87E;
```

## 5. Micro-animações

- Cards devem ter transições suaves no `hover`:
  ```css
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  ```
- Use `v-hover` ou estados CSS para elevar cards (`transform: translateY(-8px)`).
