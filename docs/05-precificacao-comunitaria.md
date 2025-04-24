# TODOKE: Precificação Comunitária - Casos de Uso

## Introdução
Este documento descreve os casos de uso relacionados à precificação comunitária na plataforma TODOKE, onde entregadores colaboram democraticamente na definição de preços de delivery através de um sistema de votação, com transparência de dados e ferramentas adaptáveis.

## Atores Adicionais

* **Sistema de Precificação:** Componente especializado do sistema TODOKE
* **APIs Externas:** Serviços como ANP (preço combustível), Google Maps (trânsito)

## Casos de Uso

### [❌] 2.1 Votação por Faixa de Preço

* **Atores:** Entregadores, Sistema de Precificação
* **Descrição:** Votações periódicas para definir faixas mínima, média e máxima por quilômetro
* **Fluxo Principal:**
    1. Sistema agenda votação mensal e notifica todos os entregadores via app
    2. Entregadores acessam seção de votação no app TODOKE
    3. Sistema apresenta opções de faixas de preço baseadas em dados históricos
    4. Entregadores rankeiam as opções usando Método de Borda
    5. Sistema calcula resultado e define as novas faixas
    6. Novos preços são aplicados automaticamente na plataforma
* **Fluxo Alternativo:**
    * Se participação for baixa, sistema pode estender período de votação
    * Se houver empate, sistema pode usar critério de desempate pré-definido

### [❌] 2.2 Fórum Comunitário de Áudios

* **Atores:** Entregadores
* **Descrição:** Espaço informal para troca de informações via mensagens de áudio, similar a grupos de WhatsApp  
* **Fluxo Principal:**
    1. Sistema disponibiliza fórum permanente no app TODOKE
    2. Entregadores podem gravar e enviar áudios com:
       - Relatos sobre condições locais (trânsito, demanda)
       - Preços de combustível em postos específicos
       - Dicas e experiências sobre rotas
    3. Sistema organiza os áudios por região e tópico
    4. Áudios mais relevantes são destacados com base em engajamento
    5. Conteúdo serve como input para o sistema de votação formal
* **Observações:**
    - O fórum é apenas para discussão informal
    - A decisão final sobre preços será feita através do sistema de votação formal
    - Moderação mínima para manter o foco em informações relevantes

### [❌] 2.3 Preço por Custo Real

* **Atores:** Entregadores, Sistema de Precificação
* **Descrição:** Cálculo de preço baseado em custos operacionais reportados pelos próprios entregadores
* **Fluxo Principal:**
    1. Entregador adiciona datapoints no app (ex: preço do combustível, coordenadas)
    2. Sistema valida e armazena os dados com carimbo de tempo e localização
    3. Calcula médias regionais de custos operacionais
    4. Aplica margem de lucro mínima definida por votação
    5. Sugere preço base para região
    6. Disponibiliza cálculo detalhado no dashboard
* **Fluxo Alternativo:**
    * Se poucos dados forem reportados, sistema usa APIs externas como fallback
    * Entregador pode anexar comprovantes (fotos de preços em postos)

### [❌] 2.4 Dashboard Coletivo

* **Atores:** Entregadores, Sistema
* **Descrição:** Visualização transparente de dados de custos e demanda
* **Fluxo Principal:**
    1. Entregador acessa dashboard no app
    2. Visualiza gráficos de custos regionais (combustível, inflação)
    3. Acompanha histórico de demanda e lucratividade média
    4. Compara desempenho com médias regionais
    5. Acessa simuladores para testar cenários
* **Fluxo Alternativo:**
    * Se dados estiverem desatualizados, sistema mostra aviso
    * Entregador pode solicitar dados adicionais via comitê
