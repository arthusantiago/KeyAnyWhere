# KeyAnyWhere
**Atenção:** *Esse projeto ainda está em fase de desenvolvimento. Não sendo recomendado utilizar em produção até ser lançada uma versão estável.*

KeyAnyWhere ou KAW, é um chaveiro que procura focar nas funcionalidades essenciais de um gerenciador de senhas. O KAW se divide em Web (este projeto), Mobile e Desktop. Cada um sendo desenvolvido em um projeto a parte.

# Principais tecnologia utilizadas
* [Bootstrap](https://getbootstrap.com/)
* [PHP](https://www.php.net/releases/8.1/en.php)
* [CakePHP](https://cakephp.org/)
* [PostgresSQL](https://www.postgresql.org/) (recomendado)
* [phpDocumentor](https://docs.phpdoc.org/)

# Documentação
* Os artefatos gerados durante o desenvolvimento estão na pasta `artefatos/`.
* Todo o código é documentado utilizando o padrão do phpDocumentor

# Configuração inicial da aplicação
Esses passos devem ser seguidos no ambiente de desenvolvimento ou produção, para que o projeto funcione.

1. Instale os [requisitos](https://book.cakephp.org/4/en/installation.html#installation) do CakePHP 4.
2. Instale o gerenciador de dependências [composer](https://getcomposer.org/download/).
3. Baixe o projeto: `git clone https://github.com/arthusantiago/KeyAnyWhere.git`
4. Instale as dependências: `composer install`
5. Instale o SGBD de sua preferência e a extensão PDO dele no PHP. Recomendo o Postgres e nesse [post](https://avds.eti.br/redes-de-computadores/linux/como-instalar-o-postgre-e-agadmin-no-linux/217/) ensino como instalar.
6. Crie banco de dados que será usado.

## Ambiente de desenvolvimento
Essas são as configurações necessárias para executar o projeto no ambiente de desenvolvimento.

1. Na pasta `config/`, crie o arquivo `config/app.php` copiando o conteúdo do arquivo `config/app.exemple.php`. No começo do arquivo você deve fazer o import da classe do SGBD escolhido.
2. Como estamos em ambiente de desenvolvimento, podemos criar o arquivo `config/app_local.php` copiado o conteúdo do arquivo `config/app_local.exemplo.php`. As configurações que estiverem nele irão sobrescrever o arquivo `config/app.php`.
3. No arquivo `config/app_local.php`, na propriedade `Datasources`, você deve inserir as configurações do banco de dados. A [documentação](https://book.cakephp.org/4/en/quickstart.html#database-configuration) do CakePHP é uma boa ajuda nessa parte.
4. Na pasta `config/`, crie o arquivo `.env` a partir do arquivo `config/.env.example`. Esse arquivo deve conter as variáveis de ambiente que são utilizadas pela aplicação no **ambiente de desenvolvimento**.
5. Execute as migrations do BD: `php bin/cake.php migrations migrate`
6. Você pode utilizar o servidor WEB interno do CakePHP para executar o projeto: `php bin/cake.php server`
7. Para logar utilize o usuário padrão da seção 'Usuário do primeiro acesso'.

### Dados para testes
Execute as Seeds na ordem:
1. `php bin/cake.php migrations seed --seed=UsersSeed`
2. `php bin/cake.php migrations seed --seed=CategoriasSeed`
3. `php bin/cake.php migrations seed --seed=EntradasSeed`

## Ambiente de produção
Este é um **exemplo** de passos que você seguir para colocar a aplicação em produção.

1. Seguir os passos listados na seção 'Configuração inicial da aplicação'.
2. Instalar o Apache2 ([post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/)).
    2.1 A pasta `webroot/` deve ser o DocumentRoot do site. No arquivo de configuração do site (ex.:`/etc/apache2/sites-enabled/000-default.conf`), insira a configuração `DocumentRoot /var/www/html/webroot`.
3. É **extremamente importante** que você configure o SSL/TLS no seu domínio. Nesse [post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/) eu ensino como fazer isso.
4. Permissões de acesso
    4.1 Usando o Apache, sete o `www-data` como dono e grupo da pasta: `sudo chown -R www-data:www-data /var/www/html/`
    4.2 Permissões gerais da aplicação: `sudo chmod -R 750 /var/www/html/`
    4.3 Permissões das pastas `/var/www/html/tmp/` e `/var/www/html/logs/`
        Instale o utilitário setfacl `sudo apt install acl` e siga o capítulo [Permissions](https://book.cakephp.org/4/en/installation.html#permissions) da documentação do CakePHP.
5. Configure variáveis de ambiente.
    Há diferentes maneiras de criá-las, uma delas é criar as variaveis dentro do Apache. [post](https://avds.eti.br/programacao/configurando-variaveis-de-ambiente-no-apache/411/)
6. Leia o arquivo de configuração `config/app.php` e veja quais variáveis são utilizadas na função `env()`.

## Gerando chaves de segurança
* Gerando a chave de criptografia pela CLI: `php -r "echo PHP_EOL . sodium_bin2hex(sodium_crypto_secretbox_keygen()) . PHP_EOL;"`
* Gerar o SALT pela CLI: `php -r "echo PHP_EOL . bin2hex(random_bytes(32)) . PHP_EOL;"`

## Usuário do primeiro acesso
Usuário: `teste@teste.com`
Senha: `qwe123@!`
