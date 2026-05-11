# Tarefa: Reestruturação da Experiência do Cliente (Customer UI)

## Contexto
Após os ajustes realizados na Home Page, decidimos adotar uma abordagem mais direta e focada em produtos e parceiros locais. O objetivo agora é levar essa mesma filosofia para o Dashboard do Cliente e para as telas de Menu.

## Diretrizes de Design e UX
- **Foco no Produto**: O cliente deve ver opções reais de comida e produtos assim que entrar na plataforma.
- **Geolocalização como Prioridade**: Assim como na Home, a descoberta de parceiros deve ser baseada na localização do usuário.
- **Resistência e Open Source**: Manter a identidade visual premium mas com a atitude de plataforma comunitária e justa.

## Objetivos da Próxima Tarefa

### 1. Dashboard do Cliente (`Customer/Dashboard.vue`)
- **Remover o Vazio**: Atualmente o dashboard está simples demais. Ele deve ser transformado em uma central de descoberta.
- **Seção de Proximidade**: Integrar o componente de mapa e geolocalização para mostrar o que está "perto de você".
- **Categorias Rápidas**: Adicionar chips ou ícones para categorias (Pizza, Burger, Saudável, etc).
- **Últimos Pedidos**: Se houver histórico, mostrar de forma compacta e elegante.

### 2. Tela de Menu/Exploração (`Customer/Menu.vue`)
- **Feed de Produtos**: Em vez de apenas listar itens, criar um feed visualmente atraente.
- **Filtros Dinâmicos**: Implementar filtros por distância, preço e tempo estimado.
- **Integração com Mapa**: Permitir que o usuário alterne entre visualização de lista e visualização de mapa para encontrar restaurantes.

### 3. Identidade Visual
- Utilizar os cards premium desenvolvidos para a Home.
- Manter o uso de sombras sutis, tipografia em negrito e ícones MDI.
- Garantir que o texto reforce a ideia de "Economia Local" e "Plataforma Justa".

## Referência Técnica
- Reutilizar a lógica de geolocalização do componente `HomeNearbyPartners.vue`.
- Utilizar os endpoints de `/api/v1/products` e `/api/v1/partners` para alimentar as telas.
- Garantir total suporte a I18n (pt-BR e en).
