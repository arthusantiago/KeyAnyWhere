# KeyAnyWhere
**Atenção:** *Esse projeto ainda está em fase de desenvolvimento. Não sendo recomendado utilizar em produção até ser lançado um versão estável.*

KeyAnyWhere ou para os íntimos: KAW

É um chaveiro simples para facilitar o gerenciamento de senhas. O foco do KAW é nas principais funcionalidades de um chaveiro.

O KAW se divide em Web (este projeto), Mobile e Desktop. Cada um desenvolvido em um projeto separado mas consumindo os dados de uma única base de dados.

## Principais tecnologia utilizadas
* [Bootstrap](https://getbootstrap.com/)
* [PHP](https://www.php.net/releases/8.1/en.php)
* [CakePHP](https://cakephp.org/)
* [PostgresSQL](https://www.postgresql.org/)
* [phpDocumentor](https://docs.phpdoc.org/)

## Documentação
* Os artefatos gerados durando o desenvolvimento estão na pasta `artefatos/`.
* Todo o código é documentado utilizando o padrão do phpDocumentor

## Ambiente de desenvolvimento

1. Baixe o projeto: `git clone https://github.com/arthusantiago/KeyAnyWhere.git`
2. Instale os [requisitos](https://book.cakephp.org/4/en/installation.html) do CakePHP.
3. Instale as dependências: `composer install`
4. Instale o SBGD de sua preferência e a extensão PDO dele no PHP. Crie também o banco de dados que será usado.
5. Na pasta `config/`, crie o arquivo `app.php` apartir do arquivo `config/app.exemplo.php`.
Nesse novo arquivo contem toda a configuração básica para a execução da aplicação.
No começo do arquivo você deve fazer o import do drive do seu SGBD. Já na propriedade `Datasources` você deve colocar a configuração do BD.
Se estiver em ambiente de desenvolvimento, utilize o arquivo `config/app_local.exemplo.php`. Seguindo a mesma lógica de copiar e colar do arquivo `config/app.exemplo.php`.
A [documentação](https://book.cakephp.org/4/en/quickstart.html#database-configuration) do CakePHP é uma boa ajuda nessa parte.
6. Na pasta `config/`, crie o arquivo `.env` apartir do arquivo `config/app.exemplo.php`.

5- Execute as migrations do BD
`php bin/cake.php migrations migrate`

## Ambiente de produção

Este é um **exemplo** de lista de passos como você seguir para colocar a aplicação em produção.

1. Instalar o PHP
2. Instalar o PostgresSQL e pgAdmin ([post](https://avds.eti.br/redes-de-computadores/linux/como-instalar-o-postgre-e-agadmin-no-linux/217/))
3. Instalar o Apache2 ([post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/))
    3.1 A pasta `webroot/` deve ser o DocumentRoot do site. No arquivo de configuração do site (ex.:`/etc/apache2/sites-enabled/000-default.conf`), insira a configuração `DocumentRoot /var/www/html/webroot`.
4. É **extremamente importante** que você configure o SSL/TLS no seu dominio. Nesse [post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/) eu ensino como fazer isso.
5. Permissões de acesso
    5.1 Se estiver usando o Apache, sete o `www-data` como dono e grupo da pasta: `sudo chown -R www-data:www-data /var/www/html/`
    5.2 Permissões gerais da aplicação: `sudo chmod -R 750 /var/www/html/`
    5.3 Permissões das pastas `/var/www/html/tmp/` e `/var/www/html/logs/`
    Instale o utilitário setfacl `sudo apt install acl` e siga o capítulo [Permissions](https://book.cakephp.org/4/en/installation.html#permissions) da documentação do CakePHP.
6. Configure variáveis de ambiente.
   Há diferentes maneiras de criá-las, uma delas é criar as variaveis dentro do Apache. [post](https://avds.eti.br/programacao/configurando-variaveis-de-ambiente-no-apache/411/)
## Configuração inicial da aplicação

1- Configurar o servidor WEB

2- Configurar as variáveis de ambiente

* Gerando a chave de criptografia pela linha de comando:
`php -r "echo PHP_EOL . sodium_bin2hex(sodium_crypto_secretbox_keygen()) . PHP_EOL;"`
* Gerar um salt novo pela CLI
`php -r "echo PHP_EOL . bin2hex(random_bytes(32)) . PHP_EOL;"`

## Usuário default

Usuário: `teste@teste.com`
Senha: `qwe123@!`

## Criando dados para teste

Execute as Seeds na ordem:
`php bin/cake.php migrations seed --seed=UsersSeed`
`php bin/cake.php migrations seed --seed=CategoriasSeed`
`php bin/cake.php migrations seed --seed=EntradasSeed`