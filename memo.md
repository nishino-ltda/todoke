# Memo TODOKE

## Decisões sobre Precificação (24/04/2025)

1. **Representante de Zona**
   - Removido o conceito de representante de zona
   - Decisões de preço serão feitas diretamente por votação

2. **Assembleias Locais** 
   - Mantidas apenas para discussão e troca de informações
   - Sem poder decisório - decisão final será por votação

3. **Coleta de Dados**
   - Motoristas podem adicionar datapoints (preço combustível, etc) com coordenadas
   - Sistema calcula médias regionais baseadas nesses dados

4. **Dashboard Coletivo**
   - Mostrará os dados coletados pelos motoristas
   - Visualização transparente de custos regionais

5. **Tecnologia**
   - Removidos smartcontracts e comitê de mediação
   - Sistema de votação direta será a base para decisões

## Correções de Bugs (24/04/2025)

1. **Problema com Tokens e Autorização**
   - Corrigido bug onde o token do entregador não estava sendo reconhecido corretamente
   - Modificado o DeliveryController para obter o usuário diretamente do token em vez de usar $request->user()
   - Implementado em todos os métodos do DeliveryController (index, store, accept, updateStatus, show)
   - Agora verifica o tipo do usuário (entregador) em vez de verificar as habilidades do token
   - Testes testCriacaoDeEntrega e testAceitacaoEConclusaoDeEntrega agora passam com sucesso
