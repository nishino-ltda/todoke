# TODOKE - Plataforma de Delivery Colaborativo

## Visão Geral
O TODOKE é uma plataforma inovadora de gerenciamento de entregas projetada para entregadores e operações com drones. Nosso sistema combina tecnologia de ponta com uma abordagem centrada no usuário, integrando entregadores tradicionais com tecnologia de drones para criar um ecossistema flexível e eficiente.

**Modelo de Negócio:** Tarifa mensal fixa acessível, sem taxas ocultas ou comissões percentuais.

## Principais Recursos
- **Integração pioneira com drones** para áreas de difícil acesso
- **Algoritmos de otimização de rotas** que reduzem tempo e consumo
- **Sistema de reputação transparente** incluindo:
  - Reputação do cliente para segurança de parceiros/entregadores
  - Mecanismos de avaliação e feedback
  - Detecção de atividades suspeitas
- **Notificações em tempo real** via Laravel Reverb para atualizações instantâneas de pedidos e entregas
- **Funcionalidades offline** para áreas com conectividade limitada
- **Precificação comunitária** com participação democrática dos entregadores
- **Ecossistema completo:** API + App + Painel Administrativo

## Arquitetura do Sistema

### Modelos de Dados Principais
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
```

### Principais Entidades
- **User**: Todos os tipos de usuários (entregadores, clientes, administradores)
- **Delivery**: Solicitações de entrega com status, origem/destino, etc.
- **Node**: Recursos de entrega (entregadores, drones, veículos)
- **Region**: Áreas geográficas de operação
- **Product**: Itens do cardápio de restaurantes
- **Order**: Pedidos de produtos com status

## API REST
Todas as rotas requerem autenticação via Bearer Token (exceto registro/login).

### Endpoints Principais
- **Autenticação**: `/api/v1/auth/register`, `/api/v1/auth/login`
- **Entregas**: 
  - `POST /api/v1/deliveries` - Criar entrega
  - `PATCH /api/v1/deliveries/{id}/accept` - Aceitar entrega
- **Nodes/Regiões**: 
  - `GET /api/v1/nodes` - Listar nodes
  - `POST /api/v1/regions` - Criar região
- **Pedidos**: 
  - `POST /api/v1/orders` - Criar pedido
  - `PATCH /api/v1/orders/{id}/status` - Atualizar status

**Exemplo Completo** de criação e acompanhamento de entrega disponível na documentação.

## Precificação Comunitária
Sistema democrático onde entregadores colaboram na definição de preços:

1. **Votação por Faixa de Preço**: Mensal, com rankeamento de opções
2. **Fórum Comunitário**: Espaço para troca de informações via áudio
3. **Preço por Custo Real**: Baseado em dados reportados pelos entregadores
4. **Dashboard Coletivo**: Visualização transparente de custos e demanda

## Notificações e Real-time
A plataforma utiliza **Laravel Reverb** para comunicação via WebSockets em tempo real:
- **Status do Pedido**: Atualizações instantâneas para clientes e parceiros.
- **Disponibilidade de Entrega**: Notificações imediatas para entregadores próximos.
- **Suporte**: Chat e respostas de tickets em tempo real.
- **Painel Admin**: Monitoramento live de atividades do sistema.

## Testes
A suíte de testes inclui:
- **Unitários & Feature (PHP)**: Testes de modelos e API usando PHPUnit.
- **E2E (Frontend)**: Testes de fluxo completo usando Cypress (12 arquivos para todos os papéis).
- **Unitários (JS)**: 243+ testes de componentes e stores usando Vitest.

**Execução:**
```bash
./vendor/bin/phpunit         # Testes de Backend
npm run test                # Testes Unitários de Frontend (Vitest)
npm run test:e2e:local      # Testes E2E (Cypress)
```

## Como Contribuir
1. O projeto é open-source sob licença MIT
2. Documentação abrangente disponível
3. Roadmap público para acompanhamento
4. Bug bounty program para vulnerabilidades

## Impacto Social
- Melhora condições de trabalho dos entregadores
- Reduz pegada de carbono das operações
- Democratiza acesso a serviços de entrega
- Fomenta empreendedorismo local
- Aumenta segurança para parceiros e entregadores através do sistema de reputação


## Status do Projeto — MVP Completo ✅

| Área | Status |
|------|--------|
| Autenticação multi-role | ✅ Completo |
| Home Page (hero, features, CTA) | ✅ Completo |
| Cardápio / Carrinho / Checkout | ✅ Completo |
| Painel do Entregador (delivery map, aceitar/rejeitar) | ✅ Completo |
| Painel Admin (gráficos, gestão, monitoramento) | ✅ Completo |
| Painel Parceiro (métricas, pedidos, produtos, etiqueta) | ✅ Completo |
| Sistema de Suporte (tickets, FAQ) | ✅ Completo |
| Notificações Real-time (Laravel Reverb) | ✅ Completo |
| Internacionalização (pt-BR + en) | ✅ Completo |
| Testes (Unitários + E2E + Backend) | ✅ 243+ testes passando |

### Próximos Passos (Sprint 10)
- Otimização de performance (lazy loading, bundle)
- Auditoria de acessibilidade (ARIA, keyboard nav)
- Compatibilidade cross-browser
- Scans de segurança
