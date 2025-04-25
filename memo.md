# Naming Convention Rules
- All field names must be in English
- All enum values must be in English
- All API response keys must be in English
- All relationships must use English names
- Avoid mixing languages in the same model/API

# Known Issues to Fix
1. Delivery model:
   - Change 'origem' to 'origin'
   - Change 'destino' to 'destination'
   - Change 'cliente' relationship to 'customer'
   - Change API response 'entregas' to 'deliveries'

2. User model:
   - UsabilityTest incorrectly checks 'tipo' field - should use 'type'
   - User model correctly uses 'type' field

3. DeliveryController:
   - Change 'origem' to 'origin'
   - Change 'destino' to 'destination'
   - Change 'endereco' to 'address'
   - Change 'posicaoAtual' to 'current_position'
   - Change 'historicoStatus' to 'status_history'
   - Change 'clienteId' to 'customer_id'
   - Change 'courierId' to 'courier_id'
   - Change 'entrega_atualizada' to 'delivery_updated'
   - Change Portuguese response messages to English
   - Change 'fotoUrl' to 'photoUrl'
   - Change 'texto' to 'text'
   - Change 'autor' to 'author'
