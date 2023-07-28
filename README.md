# KeyAnyWhere

KeyAnyWhere ou KAW, é um chaveiro que procura focar nas funcionalidades essenciais de um gerenciador de senhas.
O KAW se divide em Web (este projeto), Mobile e Desktop. Cada um sendo desenvolvido em um reponsitório a parte.

# Princípios

* A ferramenta tem por princípio a segurança em detrimento da escolha do usuário. Ou seja, muitas coisas são obrigatórias para o seu bem ;).
* Implementamos somente as funcionalidades **essenciais** de um gerenciador de senha. Menos é mais!

LOGIN

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/49f7de3d915af3b27e0982630e04a05cab9f275a/artefatos/imagens/login.png" width="500">

HOME

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/c83e3057c5d0d96e5c7c8273aaf20bf599bdcc69/artefatos/imagens/home.png" width="500">

ENTRADAS

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/49f7de3d915af3b27e0982630e04a05cab9f275a/artefatos/imagens/listagem-entradas.png" width="500">

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/c83e3057c5d0d96e5c7c8273aaf20bf599bdcc69/artefatos/imagens/entrada-nova.png" width="500">

VERIFICAÇÃO DE SENHA INSEGURA/VAZADA

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/c83e3057c5d0d96e5c7c8273aaf20bf599bdcc69/artefatos/imagens/entrada-senha-insegura.png" width="500">

LOGs DE ATIVIDADES SUSPEITAS

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/49f7de3d915af3b27e0982630e04a05cab9f275a/artefatos/imagens/logs.png" width="500">

IPs BLOQUEADOS

<img src="https://github.com/arthusantiago/KeyAnyWhere/blob/49f7de3d915af3b27e0982630e04a05cab9f275a/artefatos/imagens/ips-bloqueados.png" width="500">

# Requisitos mínimos

Memória RAM: >= 10MB
Uso de espaço em disco (ferramenta executando em produção): 235MB

## Software

* PHP >= 8.0
* SSL/TLS (leia a documentação)

# Principais tecnologia aplicadas

* [Bootstrap 5.0](https://getbootstrap.com/docs/5.0/getting-started/introduction/)
* [Bootstrap Icons](https://icons.getbootstrap.com/)
* [Fontawesome](https://fontawesome.com/)
* [EasyTimer.js](https://albert-gonzalez.github.io/easytimer.js/)
* [PHP 8.1](https://www.php.net/releases/8.1/en.php)
* [CakePHP 4](https://book.cakephp.org/4/en/index.html)
* [PostgresSQL](https://www.postgresql.org/) (recomendado)
* [phpDocumentor](https://docs.phpdoc.org/)
* [OWASP Top Ten](https://owasp.org/www-project-top-ten/) (30% aplicado)

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
7. No primeiro acesso você será direcionado para o tutorial inicial.

### Dados para testes

Execute as Seeds na ordem:

1. Tenha um usuário cadastrado
2. `php bin/cake.php migrations seed --seed=CategoriasSeed`
3. `php bin/cake.php migrations seed --seed=EntradasSeed`
Todas as entradas seram vinculadas a usuário ID 1

## Ambiente de produção

Este é um **exemplo** de passos que você seguir para colocar a aplicação em produção.

1. Seguir os passos listados na seção 'Configuração inicial da aplicação'.
2. Instalar o Apache2 ([post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/)).
    * A pasta `webroot/` deve ser o DocumentRoot do site. No arquivo de configuração do site (ex.:`/etc/apache2/sites-enabled/000-default.conf`), insira a configuração `DocumentRoot /var/www/html/webroot`.
3. **É OBRIGATÓRIO** que você configure o SSL/TLS no seu domínio. Nesse [post](https://avds.eti.br/programacao/instalando-o-apache2-e-configurando-ssl-tls/399/) eu ensino como fazer isso.
4. Permissões de acesso
    * Usando o Apache, sete o `www-data` como dono e grupo da pasta: `sudo chown -R www-data:www-data /var/www/html/`
    * Permissões gerais da aplicação: `sudo chmod -R 750 /var/www/html/`
    * Permissões das pastas `/var/www/html/tmp/` e `/var/www/html/logs/`. Instale o utilitário setfacl `sudo apt install acl` e siga o capítulo [Permissions](https://book.cakephp.org/4/en/installation.html#permissions) da documentação do CakePHP.
5. Configure variáveis de ambiente.
Há diferentes maneiras de criá-las, uma delas é criar as variaveis dentro do Apache. [post](https://avds.eti.br/programacao/configurando-variaveis-de-ambiente-no-apache/411/)
Para gerar as chaves de segurança, leia a seção 'Gerando chaves de segurança'
6. Leia o arquivo de configuração `config/app.php` e veja quais variáveis são utilizadas na função `env()`.
7. Executando a CLI do CakePHP em produção.
Dependendo de como você configura as variáveis de ambiente, a CLI do CakePHP não consegue enxerga-lás.
Dentro do arquivo `config/app.php`, você precisa passar como segundo parâmetro da função `env()` o valor da variável de ambiente, exemplo: `env('MINHA_VAR_AMB', 'valorDaVarAmb')`.
Isso deve ser feito **temporariamente**, somente durante o processo de deploy. Depois de executar tudo o que precisava na CLI do CakePHP, remova do arquivo `config/app.php` todos os valores das variáveis.
8. Execute as migrations do DB: `php bin/cake.php migrations migrate`
9. Execute os Seeds das senhas
10. No primeiro acesso você será direcionado para o tutorial inicial.

## Gerando chaves de segurança

* Gerando a chave de criptografia pela CLI: `php -r "echo PHP_EOL . sodium_bin2hex(sodium_crypto_secretbox_keygen()) . PHP_EOL;"`
* Gerar o SALT pela CLI: `php -r "echo PHP_EOL . bin2hex(random_bytes(32)) . PHP_EOL;"`

## Catálogo de senhas insegura

O KAW possui um catálogo de quase 1 milhão de senhas inseguras. Ele serve para alertar o usuário durante a criação de uma senha.
Fonte dos dados: https://github.com/danielmiessler/SecLists/tree/master/Passwords/Common-Credentials

### Populando o DB com as senhas inseguras

Execute o comando abaixo na CLI, substituindo o '[X]' pelo número correspondente ao arquivo da pasta `config/Seeds/`:
`php bin/cake.php migrations seed --seed=PasswordsInsecurePt[X]Seed`
Execute todos os seeds que tiverem o padrão 'PasswordsInsecurePt[X]Seed'

## Primeiro acesso

No primeiro acesso da ferramenta você será redirecionado para o passo a passo da configuração inicial.
