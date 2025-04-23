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

2. **Classes de teste**: Devem corresponder ao nome do arquivo
   - Exemplo: `class UserTest extends TestCase`

3. **Métodos de teste**: Podem ser definidos de duas formas:
   - Com prefixo `test` em camelCase: `public function testUserRegistration()`
   - Com anotação `@test`: `/** @test */ public function user_registration()`

### Como executar os testes

Execute todos os testes:
```bash
./vendor/bin/phpunit
```

Executar apenas testes unitários:
```bash
./vendor/bin/phpunit --testsuite=Unit
```

Executar apenas testes de feature:
```bash
./vendor/bin/phpunit --testsuite=Feature
```

### Configuração
Os testes estão configurados em `phpunit.xml` com:
- Ambiente de teste configurado
- Banco de dados SQLite em memória
- Configurações otimizadas para testes
