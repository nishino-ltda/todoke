## Convenção de Nomes de Campos

Para manter consistência no banco de dados, todos os nomes de campos devem seguir o padrão em inglês:

- client_id (não usar clienteId)
- restaurant_id (não usar restauranteId)
- product_id (não usar produtoId)

Essa convenção deve ser seguida em:
- Migrações do banco de dados
- Modelos Eloquent
- Controllers e validações

Os parâmetros de API podem manter os nomes em português para compatibilidade com clientes existentes, mas devem ser mapeados corretamente para os campos em inglês no banco de dados.
