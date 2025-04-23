# Test Status

## Implemented Test Files
- tests/Unit/ModelTest.php - Testes básicos para todos os modelos
- tests/Feature/SecurityTest.php - Testes de segurança e autenticação  
- tests/Feature/PerformanceTest.php - Testes de desempenho e carga
- tests/Feature/UsabilityTest.php - Testes de usabilidade e consistência
- tests/Feature/SpecialTest.php - Testes de casos especiais e edge cases
- tests/Feature/DeliveryTest.php
- tests/Feature/MenuTest.php
- tests/Feature/AuthTest.php
- tests/Feature/PartnerTest.php

## Notes
- Some test files had incorrect plural names (e.g. SpecialTests.php) but didn't exist
- Follow naming convention of ending with Test.php (singular)

## Correções Realizadas (22/04/2025)
- Adicionado o trait HasUuids aos modelos que usam UUID como chave primária:
  - Product
  - Order
  - Delivery
  - Node
  - Rating
  - Region (já tinha o trait)
- Corrigido o ProductFactory para usar os nomes de campos corretos (nome, descricao, preco, categoria, status)
- Corrigido o OrderItem model para usar os nomes de campos corretos (product_id, precoUnitario)
- Corrigido os testes para usar os nomes de campos corretos e os valores de enum corretos:
  - Tipo de usuário: 'entregador' em vez de 'motoboy'
  - Status de entrega: 'aceito', 'em_transporte', 'entregue', 'cancelado'
