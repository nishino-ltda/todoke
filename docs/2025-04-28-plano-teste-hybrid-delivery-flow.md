# Plano de Implementação do Teste de Fluxo de Entrega Híbrida (TDD)

## 1. Contexto

O teste `test_hybrid_delivery_flow` no arquivo `PartnerDeliveryTest.php` está atualmente marcado como incompleto quando não encontra as etapas (stages) da entrega híbrida. Conforme a análise de testes (docs/2025-04-27-analise-testes.md), este teste deve ser corrigido para fazer asserções reais sobre o JSON de stages em vez de usar `markTestIncomplete`.

## 2. Objetivo

Implementar corretamente o teste de fluxo de entrega híbrida seguindo os princípios de TDD (Test-Driven Development), garantindo que o sistema suporte adequadamente o caso de uso 1.9 (Entrega Híbrida: Motoqueiro + Drone Hub) descrito na documentação.

## 3. Estrutura de Dados Necessária

### 3.1 Modelo de Dados

Baseado nas migrações e modelos existentes:

- **Delivery**:
  - `stages`: Array JSON com etapas da entrega híbrida
  - `logistics_partner_id`: ID do parceiro logístico

  - `status`: Enum com valores como 'pending', 'accepted', 'in_transit', 'collected', 'delivered', 'canceled'

- **DeliveryAssignment**:
  - `delivery_id`: ID da entrega
  - `partner_id`: ID do parceiro responsável pela etapa
  - `stage`: Número inteiro representando a ordem da etapa
  - `status`: Status da etapa específica

### 3.2 Enums Específicos para Drones

Conforme recomendado na análise de testes, precisamos adicionar status específicos para drones:

- `drone_launched`: Drone foi lançado do hub
- `drone_in_route`: Drone está em rota para o destino
- `drone_arrived`: Drone chegou ao destino
- `drone_returned`: Drone retornou ao hub

## 4. Abordagem TDD

### 4.1 Etapa 1: Escrever o Teste Falho

Modificar o teste `test_hybrid_delivery_flow` para:

1. Remover o `markTestIncomplete`
2. Adicionar asserções específicas para verificar a estrutura e conteúdo do JSON de stages
3. Verificar a criação correta dos DeliveryAssignments
4. Testar a atualização de status específicos para drones

### 4.2 Etapa 2: Implementar o Código Mínimo para Passar

1. Modificar o `DeliveryController` para garantir que o campo `stages` seja corretamente preenchido
2. Atualizar o `DeliveryStatusService` para suportar os novos status específicos de drone
3. Implementar a lógica de criação automática de DeliveryAssignments

### 4.3 Etapa 3: Refatorar

1. Melhorar a estrutura do código
2. Remover duplicações
3. Garantir que a implementação siga as boas práticas

## 5. Modificações Necessárias

### 5.1 Arquivo `tests/Feature/PartnerDeliveryTest.php`

```php
// Modificar o teste test_hybrid_delivery_flow:
// 1. Remover o markTestIncomplete
// 2. Adicionar asserções específicas para o JSON de stages
// 3. Verificar a criação automática de DeliveryAssignments
```

### 5.2 Arquivo `app/Http/Controllers/API/DeliveryController.php`

```php
// Modificar o método prepareDeliveryData:
// 1. Garantir que o campo stages seja corretamente preenchido para entregas híbridas
// 2. Adicionar lógica para associar automaticamente os parceiros às etapas
```

### 5.3 Arquivo `app/Services/DeliveryStatusService.php`

```php
// 1. Adicionar constantes para os novos status específicos de drone
// 2. Modificar o método updateStageStatus para suportar os novos status
// 3. Melhorar a lógica de atualização de DeliveryAssignments
```

## 6. Plano de Implementação Detalhado

### 6.1 Modificação do Teste

1. Atualizar o teste `test_hybrid_delivery_flow` para:
   - Verificar se o campo `stages` contém as etapas corretas
   - Verificar se os DeliveryAssignments são criados automaticamente
   - Testar a atualização de status específicos para cada etapa

2. Adicionar um novo teste `test_hybrid_delivery_status_updates` para:
   - Verificar a atualização de status específicos para drones
   - Testar a propagação de status entre etapas
   - Verificar a conclusão da entrega quando todas as etapas são concluídas

### 6.2 Implementação do Controller

1. Modificar o método `prepareDeliveryData` no `DeliveryController` para:
   - Definir corretamente o campo `stages` para entregas híbridas
   - Incluir informações sobre o tipo de parceiro em cada etapa

2. Implementar lógica para associar automaticamente os parceiros às etapas com base em:
   - Tipo de parceiro (delivery_point, distribution_center)
   - Região geográfica
   - Disponibilidade do parceiro

### 6.3 Implementação do Service

1. Atualizar o `DeliveryStatusService` para:
   - Adicionar constantes para os novos status específicos de drone
   - Modificar o método `updateStageStatus` para suportar os novos status
   - Implementar lógica para atualizar automaticamente os DeliveryAssignments

2. Implementar lógica para propagar status entre etapas:
   - Quando uma etapa é concluída, a próxima deve ser iniciada
   - Quando todas as etapas são concluídas, a entrega deve ser marcada como entregue

## 7. Validações e Testes Adicionais

### 7.1 Validações

1. Verificar se o parceiro tem permissão para atualizar a etapa específica
2. Validar se a etapa anterior foi concluída antes de iniciar a próxima
3. Garantir que os status sejam atualizados na ordem correta

### 7.2 Testes de Borda

1. Testar o comportamento quando um parceiro cancela sua etapa
2. Verificar o que acontece quando um drone não pode completar a entrega
3. Testar a recuperação de falhas durante o processo de entrega

## 8. Considerações de Implementação

### 8.1 Notificações

Garantir que as notificações sejam enviadas para:
- Cliente: em cada mudança de status
- Parceiros: quando sua etapa está pronta para ser iniciada
- Administrador: em caso de falhas ou cancelamentos

### 8.2 Rastreamento

Implementar rastreamento detalhado para entregas híbridas:
- Posição atual do item em cada etapa
- Histórico de status com timestamps
- Informações sobre o parceiro responsável pela etapa atual

## 9. Cronograma de Implementação

1. **Dia 1**: Modificar o teste para que falhe corretamente
2. **Dia 2**: Implementar as mudanças no Controller e Service
3. **Dia 3**: Adicionar testes adicionais e refatorar o código
4. **Dia 4**: Testar em ambiente de desenvolvimento e corrigir problemas
5. **Dia 5**: Documentar as mudanças e finalizar a implementação

## 10. Conclusão

A implementação do teste de fluxo de entrega híbrida seguindo os princípios de TDD garantirá que o sistema suporte adequadamente o caso de uso 1.9 (Entrega Híbrida: Motoqueiro + Drone Hub). As modificações propostas melhorarão a qualidade do código e a cobertura de testes, além de fornecer uma base sólida para futuras expansões do sistema.
