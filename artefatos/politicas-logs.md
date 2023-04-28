# Eventos e Logs
Um evento é uma situação que já foi mapeada e definida suas propriedades. Esse evento envolve algo que o sistema precisa monitorar. Quando o evento acontece, ele deve ser notificado e transformado em um registro de log.

Abaixo está descrito o fluxo criação de um evento até transformá-lo em um registro de log.

1. Acontece um evento. Quem o identifica também fica responsável por notificá-lo.
2. Quem notifica o evento também deve informar todos os dados necessários para registrar o evento.
3. A classe GerenciamentoLogs fica responsável por transformar as informações do evento em um registro de log.
4. Com a estrutura do log montada, é encaminhado para salvar no banco de dados.

Para a classificar a severidade do log, é utilizado o conceito de progressão no nivel do [Syslog](https://en.wikipedia.org/wiki/Syslog#Severity_level).

## Categorias de eventos

### C1 - Login
Eventos que acontecerem antes, durante e depois do login no sistema.

### C2 - Acesso não autorizado
Descreve as tentativas de acesso a algum recurso ao qual o usuário não tem permissão.
