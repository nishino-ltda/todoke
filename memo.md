# Memo TODOKE

## Estrutura da tabela deliveries

Principais campos:
- `customer_id`: ID do cliente solicitante
- `courier_id`: ID do entregador (para entregas diretas)
- `logistics_partner_id`: ID do parceiro logístico (para entregas entre parceiros)
- `status`: Enum com valores possíveis:
  - pending
  - accepted  
  - in_transit
  - collected
  - delivered
  - canceled
- `type`: Enum com tipos de entrega:
  - standard
  - express
  - sustainable  
  - priority
- `stages`: JSON com etapas para entregas híbridas
- `node_id`: ID do node associado (hub, drone, etc)

## Fluxos testados

### DeliveryTest.php
- Fluxo básico customer -> courier
- Testa:
  - Criação de entrega
  - Aceite pelo entregador
  - Atualização de status
  - Rastreamento
  - Confirmação de entrega

### PartnerDeliveryTest.php  
- Fluxo entre parceiros (BistroTech -> LogisMaster)
- Testa:
  - Coordenação entre parceiros
  - Entregas híbridas (moto + drone)
  - Atualização de status por parceiro drone
  - Divisão em etapas (stages)

## Observações importantes
- Ambos testes usam a mesma tabela mas com enfoques diferentes
- Campos como logistics_partner_id e stages são específicos para PartnerDeliveryTest
- Campos como courier_id são específicos para DeliveryTest
- Enums de status e type são compartilhados e devem ser mantidos consistentes


# Testes de Segurança Adicionados - 27/04/2025

Foram implementados testes abrangentes para verificar vulnerabilidades de segurança no sistema de entregas:

1. **Proteção contra mudança de proprietário**:
   - Teste: user_cannot_change_delivery_customer_id()
   - Verifica se um usuário não pode alterar o customer_id de uma entrega

2. **Acesso a entregas de outros usuários**:
   - Teste: user_cannot_access_other_users_deliveries()
   - Garante que usuários só possam acessar suas próprias entregas

3. **Proteção contra campos forjados**:
   - Teste: user_cannot_forge_fields_in_payload()
   - Verifica se campos adicionais não autorizados são rejeitados

4. **Proteção de campos sensíveis**:
   - Teste: user_cannot_update_sensitive_fields_directly()
   - Garante que campos como confirmation_code e value não podem ser alterados diretamente

5. **Proteção contra alteração de email**:
   - Teste: user_cannot_update_email_through_delivery_api()
   - Verifica se não é possível alterar o email do usuário através da API de entregas

Todos os testes nao foram realizados


# Correções realizadas em 27/04/2025

## PartnerDeliveryTest - Problemas resolvidos

1. **Discrepância no ID do parceiro (esperado 36, recebido 30)**
   - Adicionada validação no modelo Delivery para verificar existência de partner_id nos stages
   - Garantido que os IDs de parceiros sejam válidos antes de serializar os stages

2. **Status 'collected' não encontrado**
   - Modificado o mapeamento de status no DeliveryStatusService para manter 'collected'
   - Adicionados logs detalhados para depuração de atualizações de status

## Boas práticas adicionadas
- Sempre validar partner_id ao definir stages
- Manter logs detalhados para transições de status
- Usar match() para mapeamento claro de status
