# TODOKE: Próximos Passos para Test Driven Development (TDD)

## Introdução

Este documento descreve os próximos passos e recomendações para a aplicação contínua da metodologia Test Driven Development (TDD) no projeto TODOKE. Baseado na análise da documentação existente, rotas, controllers e testes, identificamos áreas chave onde o TDD pode ser mais efetivamente aplicado para garantir a robustez e a qualidade do código.

## Status Atual do TDD

A análise dos arquivos revelou que o projeto já adota o TDD em certa medida, especialmente na implementação de novas funcionalidades como o fluxo de entrega híbrida. Existem testes de feature e unitários cobrindo diversas áreas, incluindo autenticação, entregas, produtos e segurança. No entanto, há oportunidades para expandir a cobertura e refinar a aplicação do TDD em fluxos mais complexos e casos de borda.

## Áreas Chave para Aplicação de TDD

Com base nos casos de uso, modelos e testes existentes, as seguintes áreas são prioritárias para a aplicação de TDD:

1.  **Precificação Comunitária (Casos de Uso 2.1 - 2.4):**
    *   Implementar testes para o sistema de votação por faixa de preço, cobrindo diferentes cenários de votação e cálculo de resultados.
    *   Desenvolver testes para a integração com APIs externas (ANP, Google Maps) para cálculo de preço por custo real.
    *   Criar testes para o dashboard coletivo, verificando a correta agregação e exibição de dados de custos e demanda.

2.  **Casos de Borda em Entrega Híbrida (Caso de Uso 1.9):**
    *   Escrever testes para cenários de cancelamento de etapas, garantindo que o sistema lide corretamente com interrupções no fluxo.
    *   Desenvolver testes para falhas de drone, simulando situações onde o drone não consegue completar a entrega e verificando os mecanismos de recuperação ou fallback.
    *   Testar a transição de status entre as etapas, assegurando que a ordem das etapas seja respeitada e que as notificações corretas sejam enviadas.

3.  **Cenários Offline (Caso de Uso 1.7):**
    *   Expandir os testes existentes para o modo offline, cobrindo a sincronização de diferentes tipos de dados (status, posição, mensagens) e a resolução de conflitos.

4.  **Gerenciamento de Nodes e Regiões (Casos de Uso 1.6, 1.8):**
    *   Implementar testes para a criação e atualização de regiões, incluindo validações de polígonos GeoJSON.
    *   Desenvolver testes para o cadastro e gerenciamento de nodes (entregadores, drones), verificando o fluxo de aprovação pelo administrador.

5.  **Segurança e Permissões:**
    *   Continuar a expandir os testes de segurança, focando em permissões de acesso a endpoints e validação de payloads, especialmente em rotas sensíveis como atualização de status de entrega e gerenciamento de usuários/nodes.

## Recomendações para Melhoria de Testes e Documentação

*   **Documentar Padrões de Teste:** Criar um documento específico (por exemplo, `docs/07-padroes-de-teste.md`) detalhando as convenções para nomes de classes e métodos de teste, estrutura de testes (Arrange-Act-Assert), uso de factories e mocks, e diretrizes para testes de feature vs. unitários.
*   **Refatorar Testes Frágeis:** Revisar testes existentes que possam ser frágeis (por exemplo, dependendo de IDs fixos ou dados específicos que podem mudar) e refatorá-los para usar factories e dados dinâmicos.
*   **Aumentar Cobertura de Testes Unitários:** Focar na escrita de testes unitários para a lógica de negócio complexa, especialmente nos Services (como `DeliveryStatusService`) e Models, para garantir que as unidades de código funcionem corretamente isoladamente.
*   **Alinhar Testes com Casos de Uso:** Garantir que cada teste de feature esteja claramente mapeado a um ou mais casos de uso, conforme documentado em `docs/01-casos-de-uso.md`. Adicionar referências aos casos de uso nos comentários dos testes.

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
