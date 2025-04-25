## Convenção de Nomes de Campos

Para manter consistência no banco de dados, todos os nomes de campos devem seguir o padrão em inglês:

- clientId (não usar clienteId)
- restaurantId (não usar restauranteId)
- productId (não usar produtoId)

Essa convenção deve ser seguida em:
- Migrações do banco de dados
- Modelos Eloquent
- Controllers e validações

Os parâmetros de API podem manter os nomes em português para compatibilidade com clientes existentes, mas devem ser mapeados corretamente para os campos em inglês no banco de dados.
