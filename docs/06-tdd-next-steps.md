# TODOKE: Próximos Passos para Test Driven Development (TDD)

## Introdução

Este documento descreve os próximos passos e recomendações para a aplicação contínua da metodologia Test Driven Development (TDD) no projeto TODOKE. Baseado na análise da documentação existente, rotas, controllers e testes, identificamos áreas chave onde o TDD pode ser mais efetivamente aplicado para garantir a robustez e a qualidade do código.

## Status Atual do TDD e Cobertura de Testes

A análise dos arquivos e a execução dos testes (`php artisan test`) revelaram que o projeto já adota o TDD em certa medida, especialmente na implementação de novas funcionalidades como o fluxo de entrega híbrida e precificação comunitária.

**Resultados dos Testes:**
- **64 testes passaram**
- **2 testes estão incompletos**, relacionados ao rastreamento de entrega offline e cenários offline em `tests/Feature/DeliveryTest.php` e `tests/Feature/HybridDeliveryEdgeCasesTest.php`.

Apesar dos testes passarem, a execução indicou que **nenhum driver de cobertura de código está disponível**, o que impede a geração de um relatório detalhado de cobertura. Portanto, a avaliação da cobertura de testes é baseada na análise manual dos arquivos de teste, rotas e controllers.

**Avaliação da Cobertura:**
- **Autenticação e Usuário:** Cobertura razoável para fluxos básicos e gerenciamento de admin.
- **Precificação Comunitária:** Cobertura para votação e cálculo, mas faltam testes unitários para os serviços (`VotingCalculationService`, `VotingRoundService`, `FareUpdateService`).
- **Entregas:** Cobertura para criação, aceitação, status e mensagens. Necessita de mais testes para casos de borda e, crucialmente, para cenários offline (conforme indicado pelos testes incompletos).
- **Menu e Pedidos:** Cobertura decente para listagem, criação e atualização de produtos, e criação de pedidos.
- **Nodes e Regiões:** Cobertura suficiente para criação e associação.
- **Partner Analytics:** Cobertura adequada para as métricas atuais.
- **Segurança:** Cobertura inicial para acesso não autorizado e forjamento de campos. Requer expansão para cobrir mais endpoints e tipos de vulnerabilidades.
- **Performance:** Testes básicos existem, mas precisam ser expandidos conforme indicado em `memory-bank/progress.md`.
- **Usabilidade:** Cobertura razoável para mensagens de erro, validação, documentação e paginação.

No geral, a cobertura é decente para as funcionalidades implementadas, mas há oportunidades claras para melhoria, especialmente em testes unitários para serviços, tratamento abrangente de casos de borda para entregas (principalmente cenários offline), e expansão dos testes de segurança e performance.

## Áreas Chave para Aplicação de TDD e Testes Necessários

Com base nos casos de uso, modelos, testes existentes e na análise de cobertura, as seguintes áreas são prioritárias para a aplicação de TDD e requerem o desenvolvimento de testes específicos:

1.  **Precificação Comunitária (Casos de Uso 2.1 - 2.4):**
    *   **Testes Necessários:**
        *   Testes unitários abrangentes para `VotingCalculationService`, cobrindo diferentes cenários de votação, empates e desempates.
        *   Testes unitários para `VotingRoundService`, verificando a criação, fechamento e gerenciamento de rodadas de votação.
        *   Testes unitários para `FareUpdateService`, garantindo que os preços das regiões sejam atualizados corretamente com base nos resultados da votação.
        *   Testes de feature para a integração com APIs externas (ANP, Google Maps) para cálculo de preço por custo real (se aplicável).
        *   Testes de feature para o dashboard coletivo, verificando a correta agregação e exibição de dados de custos e demanda.

2.  **Casos de Borda em Entrega Híbrida (Caso de Uso 1.9):**
    *   **Testes Necessários:**
        *   Expandir testes para cenários de cancelamento de etapas, garantindo que o sistema lide corretamente com interrupções no fluxo em diferentes pontos.
        *   Desenvolver testes para falhas de drone, simulando situações onde o drone não consegue completar a entrega e verificando os mecanismos de recuperação ou fallback (conforme indicado no teste incompleto).
        *   Testar a transição de status entre as etapas, assegurando que a ordem das etapas seja respeitada e que as notificações corretas sejam enviadas em todos os cenários possíveis.

3.  **Cenários Offline (Caso de Uso 1.7):**
    *   **Testes Necessários:**
        *   Completar os testes existentes para o modo offline (`tests/Feature/DeliveryTest.php` e `tests/Feature/HybridDeliveryEdgeCasesTest.php`), cobrindo a sincronização de diferentes tipos de dados (status, posição, mensagens) e a resolução de conflitos ao reconectar. Isso provavelmente exigirá o uso de mocks para simular condições offline e testar a lógica de sincronização.

4.  **Gerenciamento de Nodes e Regiões (Casos de Uso 1.6, 1.8):**
    *   **Testes Necessários:**
        *   Testes de feature para a criação e atualização de regiões, incluindo validações de polígonos GeoJSON e casos de borda (por exemplo, polígonos inválidos).
        *   Testes de feature para o cadastro e gerenciamento de nodes (entregadores, drones), verificando o fluxo de aprovação pelo administrador e as transições de status dos nodes.

5.  **Segurança e Permissões:**
    *   **Testes Necessários:**
        *   Expandir os testes de segurança para cobrir todos os endpoints sensíveis, verificando se apenas usuários autorizados com as permissões corretas podem acessá-los.
        *   Adicionar testes para prevenir injeção de código ou outros tipos de vulnerabilidades comuns em APIs.
        *   Testar a validação de payloads em todos os endpoints para garantir que campos não permitidos sejam rejeitados.

6.  **Performance:**
    *   **Testes Necessários:**
        *   Expandir os testes de performance para incluir cenários com maior volume de dados e requisições concorrentes em endpoints críticos (listagem de entregas, criação de pedidos, atualizações de status).

## Recomendações para Melhoria de Testes e Documentação

*   **Documentar Padrões de Teste:** Criar um documento específico (por exemplo, `docs/07-padroes-de-teste.md`) detalhando as convenções para nomes de classes e métodos de teste, estrutura de testes (Arrange-Act-Assert), uso de factories e mocks, e diretrizes para testes de feature vs. unitários.
*   **Refatorar Testes Frágeis:** Revisar testes existentes que possam ser frágeis (por exemplo, dependendo de IDs fixos ou dados específicos que podem mudar) e refatorá-los para usar factories e dados dinâmicos.
*   **Aumentar Cobertura de Testes Unitários:** Focar na escrita de testes unitários para a lógica de negócio complexa, especialmente nos Services (como `DeliveryStatusService`, `VotingCalculationService`, `VotingRoundService`, `FareUpdateService`) e Models, para garantir que as unidades de código funcionem corretamente isoladamente.
*   **Alinhar Testes com Casos de Uso:** Garantir que cada teste de feature esteja claramente mapeado a um ou mais casos de uso, conforme documentado em `docs/01-casos-de-uso.md`. Adicionar referências aos casos de uso nos comentários dos testes.
*   **Configurar Driver de Cobertura:** Investigar e configurar um driver de cobertura de código (como Xdebug) para gerar relatórios de cobertura precisos e automatizados.

## Diretrizes para Novos Testes (TDD)

Ao implementar novas funcionalidades ou corrigir bugs, seguir o ciclo TDD:

1.  **Escrever um Teste Falho:** Começar escrevendo um teste que descreva o comportamento desejado e que falhe porque a funcionalidade ainda não existe ou não funciona corretamente.
2.  **Implementar o Código Mínimo:** Escrever apenas o código necessário para fazer o teste passar. O foco é na funcionalidade, não na elegância do código neste ponto.
3.  **Refatorar:** Melhorar a estrutura do código, remover duplicações, otimizar e garantir que ele siga os padrões do projeto, sem alterar o comportamento (garantido pelos testes que agora passam).
4.  **Repetir:** Continuar o ciclo para a próxima pequena porção de funcionalidade.

Utilizar factories para criar dados de teste de forma consistente e evitar dependências de dados fixos no banco de dados. Usar mocks quando necessário para isolar a unidade de código sendo testada e simular dependências externas.

## Integração com o Fluxo de Desenvolvimento

O TDD deve ser uma parte integrante do fluxo de desenvolvimento:

*   **Antes de Codificar:** Sempre começar escrevendo testes antes de implementar novas funcionalidades.
*   **Durante a Correção de Bugs:** Escrever um teste que reproduza o bug antes de corrigi-lo. A correção estará completa quando o novo teste passar.
*   **Revisão de Código:** Incluir a revisão dos testes como parte essencial do processo de code review.

## Conclusão

A adoção consistente do TDD nas áreas identificadas, juntamente com a melhoria da documentação e dos testes existentes, fortalecerá significativamente a qualidade e a manutenibilidade do projeto TODOKE. Este plano serve como um guia para os próximos esforços de desenvolvimento orientados a testes.
