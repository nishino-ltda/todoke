# TODOKE: Testes

Este documento descreve a abordagem de testes da plataforma TODOKE, incluindo convenções, estrutura e exemplos.

## Estrutura de Testes

A plataforma TODOKE segue uma abordagem de testes em duas camadas:

1. **Testes Unitários** (`tests/Unit/`): Testam componentes isolados, como modelos e serviços.
2. **Testes de Feature** (`tests/Feature/`): Testam funcionalidades completas, simulando requisições HTTP.

## Convenções de Testes

1. **Nomenclatura de arquivos**: Devem terminar com `Test.php`
   - Exemplo: `UserTest.php`, `DeliveryTest.php`

2. **Classes de teste**: Devem corresponder ao nome do arquivo
   - Exemplo: `class UserTest extends TestCase`

3. **Métodos de teste**: Podem ser definidos de duas formas:
   - Com prefixo `test` em camelCase: `public function testUserRegistration()`
   - Com anotação `@test`: `/** @test */ public function user_registration()`

## Principais Testes de Feature

### MenuTest

Este teste verifica a funcionalidade do menu, produtos, addons e pedidos:

1. **Listagem de Produtos**:
   - Verifica se a API retorna produtos disponíveis
   - Testa a estrutura da resposta JSON

2. **Filtro por Categoria**:
   - Verifica se a filtragem de produtos por categoria funciona corretamente

3. **Criação de Produtos**:
   - Testa a criação de novos produtos por parceiros
   - Verifica se os dados são salvos corretamente no banco

4. **Atualização de Produtos**:
   - Testa a atualização de produtos existentes
   - Verifica se apenas o proprietário pode atualizar seus produtos

5. **Criação de Addons**:
   - Testa a criação de complementos (toppings) para produtos
   - Verifica se os dados são salvos corretamente

6. **Associação de Addons com Produtos**:
   - Testa a associação de complementos a produtos específicos
   - Verifica se as relações são estabelecidas corretamente

7. **Obtenção de Addons de um Produto**:
   - Verifica se a API retorna os complementos disponíveis para um produto

8. **Criação de Pedidos com Addons**:
   - Testa a criação de pedidos com produtos e complementos
   - Verifica se o cálculo do valor total inclui os complementos

9. **Validação de Compatibilidade de Addons**:
   - Testa se o sistema rejeita complementos incompatíveis com produtos

```php
public function testOrderCreationWithAddons()
{
    $product = Product::factory()->forPartner($this->partner->id)->create([
        'price' => 10.00
    ]);
    
    $addon = Addon::factory()->forPartner($this->partner->id)->create([
        'price' => 2.50
    ]);
    
    // Associate addon with product
    $product->addons()->attach($addon->id);
    
    $orderData = [
        'partner_id' => (string)$this->partner->id,
        'items' => [
            [
                'product_id' => $product->id, 
                'quantity' => 1,
                'addons' => [
                    ['addon_id' => $addon->id, 'quantity' => 2]
                ]
            ]
        ],
        'delivery' => [
            'destination' => [
                'lat' => -23.5614,
                'lng' => -46.6559,
                'address' => 'Rua Augusta, 500'
            ]
        ]
    ];
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->customerToken
    ])->postJson('/api/v1/orders', $orderData);
    
    $response->assertStatus(201)
        ->assertJsonPath('total_value', '15.00'); // 10.00 + (2.50 * 2)
}
```

### DeliveryTest

Testa o fluxo de entregas, incluindo:
- Criação de entregas
- Aceitação por entregadores
- Atualizações de status
- Entregas híbridas (motoboy + drone)

### SecurityTest

Verifica aspectos de segurança como:
- Validação de payload
- Prevenção de acesso não autorizado
- Proteção contra injeção de código malicioso

## Teste de Recuperação de Falhas

O teste `system_recovers_from_database_failure` verifica se o sistema:
1. Retorna status 500 quando ocorre uma falha no banco de dados
2. Inclui uma mensagem de erro na resposta (sem validar o texto exato)

Esta abordagem flexível permite que:
- A mensagem de erro possa variar entre ambientes
- O teste continue válido mesmo se a mensagem for melhorada/atualizada
- Foque no comportamento essencial (erro 500 + mensagem)

```php
$response->assertStatus(500);
$this->assertNotEmpty($response->json('message'));
```

## Padrões de Isolamento de Testes

Para garantir que os testes sejam independentes e não interfiram uns nos outros, implementamos os seguintes padrões:

1. **Limpeza de Mocks**:
   - Usar `Mockery::close()` no método `setUp()` para limpar mocks de testes anteriores
   - Evitar mocks estáticos sem limpeza adequada

2. **Autenticação Direta**:
   - Gerar tokens diretamente usando Sanctum em vez de depender de controllers
   - Exemplo:
   ```php
   private function getAuthToken(User $user): string
   {
       // Create a token directly
       $token = $user->createToken('test-token')->plainTextToken;
       return $token;
   }
   ```

3. **Estado do Banco de Dados**:
   - Usar `RefreshDatabase` em vez de `DatabaseTransactions` para garantir um estado limpo
   - Criar dados de teste específicos para cada teste

4. **Evitar Dependências entre Testes**:
   - Cada teste deve configurar seu próprio ambiente
   - Não depender de dados criados por outros testes

Estes padrões foram implementados no MenuTest para resolver um problema onde o teste passava quando executado individualmente, mas falhava quando executado como parte da suíte completa.

## Como executar os testes

Execute todos os testes:
```bash
php artisan test
```

Executar apenas testes unitários:
```bash
php artisan test --testsuite=Unit
```

Executar apenas testes de feature:
```bash
php artisan test --testsuite=Feature
```

Executar um arquivo de teste específico:
```bash
php artisan test tests/Feature/MenuTest.php
```

## Configuração
Os testes estão configurados em `phpunit.xml` com:
- Ambiente de teste configurado
- Banco de dados SQLite em memória
- Configurações otimizadas para testes

## Factories

O sistema utiliza factories para criar dados de teste:

1. **UserFactory**: Cria usuários de diferentes tipos (cliente, parceiro, entregador)
2. **ProductFactory**: Cria produtos associados a parceiros
3. **AddonFactory**: Cria complementos (toppings) associados a parceiros
4. **OrderFactory**: Cria pedidos com itens
5. **DeliveryFactory**: Cria entregas com diferentes status

Exemplo de uso de factories nos testes:

```php
// Criar um produto para um parceiro específico
$product = Product::factory()->forPartner($partnerId)->create();

// Criar um addon para um parceiro específico
$addon = Addon::factory()->forPartner($partnerId)->create();

// Associar o addon ao produto
$product->addons()->attach($addon->id);
