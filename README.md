# KeyAnyWhere 

**Atenção:** *Esse projeto ainda está em fase de desenvolvimento. Não sendo recomendado utilizar em produção até ser lançado um versão estável.*

KeyAnyWhere ou para os íntimos: KAW 

É um chaveiro simples para facilitar o gerenciamento de senhas. O foco do KAW é nas principais funcionalidades de um chaveiro.

O KAW se divide em Web (este projeto), Mobile e Desktop. Cada um desenvolvido em um projeto separado mas consumindo os dados de uma única base de dados. 

## Principais tecnologia utilizadas

- [Bootstrap](https://getbootstrap.com/)
- [PHP](https://www.php.net/releases/8.1/en.php)
- [CakePHP](https://cakephp.org/)
- [PostgresSQL](https://www.postgresql.org/)
-  [phpDocumentor](https://docs.phpdoc.org/)

## Documentação
- Os artefatos gerados durando o desenvolvimento estão na pasta `artefatos/`.
- Todo o código é documentado utilizando o padrão do phpDocumentor

## Instalação

1- Baixe o projeto
`git clone https://github.com/arthusantiago/KeyAnyWhere.git`

2- Verifique se os [requisitos](https://book.cakephp.org/4/en/installation.html) do CakePHP estão sendo atendidos.  Não esqueça de instalar a extensão PHP do SGBD que você usará ;)

3- Instale as dependências
`composer install`

4- Crie o Banco de Dados no seu SBGD preferido.

5- Crie o arquivo `app.php` na pasta `config/`. Copie e cole no arquivo criado o conteúdo de `config/app.exemplo.php`.
Nesse novo arquivo contem toda a configuração básica para a execução da aplicação. 
No começo do arquivo você deve fazer o import do drive do seu SBGD. Já na propriedade `Datasources` você deve colocar a configuração do BD.
Se estiver em ambiente de desenvolvimento, utilize o arquivo `config/app_local.exemplo.php`. Seguindo a mesma lógica de copiar e colar do arquivo `config/app.exemplo.php`. 
A [documentação](https://book.cakephp.org/4/en/quickstart.html#database-configuration) do CakePHP é uma boa ajuda nessa parte.

5- Execute as migrations do BD
`php bin/cake.php migrations migrate`

6- Execute o Seed do usuário padrão
`php bin/cake.php migrations seed --seed=UserDefaultSeed`
Dentro do arquivo `config/Seeds/UserDefaultSeed.php` contem os dados de acesso do usuário.
