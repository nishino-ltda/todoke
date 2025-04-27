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
