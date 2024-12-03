# WebAuthn Login por Digital

https://youtu.be/KpuxzaQEKZc

## Visão Geral

Este repositório contém uma aplicação baseada no **WebAuthn**, um padrão moderno para autenticação na web sem o uso de senhas. O WebAuthn é parte das especificações da **FIDO Alliance** e oferece uma solução mais segura para autenticação de usuários por meio de chaves criptográficas geradas no dispositivo do usuário.

Nesta aplicação, foi implementado o login por digital, utilizando a biblioteca WebAuthn em PHP. Este recurso permite que os usuários se autentiquem por meio de dispositivos compatíveis, como leitores de digitais ou chaves de segurança USB/NFC, em vez de usar credenciais tradicionais, como nome de usuário e senha.

## Recursos Principais

-   **Registro de Credenciais**: Os usuários podem registrar uma credencial biométrica (como impressão digital) ou dispositivos de autenticação suportados.
-   **Autenticação Segura**: A autenticação ocorre sem que o servidor precise armazenar dados sensíveis, como senhas. Em vez disso, utiliza chaves criptográficas.
-   **Compatibilidade com Múltiplos Dispositivos**: Suporte a dispositivos como USB, NFC, BLE e leitores internos (smartphones ou notebooks).
-   **Segurança Avançada**: Proteção contra ataques como phishing e reutilização de credenciais, com suporte a criptografia de ponta a ponta.

## Tecnologias Utilizadas

-   **PHP**: Linguagem de back-end para manipular solicitações WebAuthn e interagir com a base de dados.
-   **Biblioteca WebAuthn**: Biblioteca utilizada para gerenciar as credenciais e interagir com o protocolo WebAuthn.
-   **JavaScript**: Para a interação com o navegador e APIs do WebAuthn.
-   **Banco de Dados**: Armazenamento das credenciais criptográficas no backend.

## Como Funciona o WebAuthn?

### 1. Registro de Usuário

1.  **Solicitação de Registro**: O cliente (navegador) faz uma solicitação ao servidor para registrar uma nova credencial.
2.  **Criação de Credenciais**: O navegador usa o dispositivo de autenticação do usuário (como um leitor de digitais) para criar uma chave pública única.
3.  **Armazenamento Seguro**: A chave pública gerada é enviada ao servidor e armazenada junto com informações do usuário.

### 2. Autenticação de Usuário

1.  **Desafio Criptográfico**: O servidor envia um desafio ao cliente.
2.  **Assinatura Digital**: O dispositivo do usuário usa a chave privada correspondente para assinar o desafio.
3.  **Verificação no Servidor**: O servidor verifica a assinatura usando a chave pública previamente armazenada. Se for válida, o login é concluído.

### Segurança

-   Os dados biométricos (como impressões digitais) **nunca deixam o dispositivo do usuário**.
-   O servidor só armazena a chave pública, que é inútil sem a chave privada armazenada no dispositivo do usuário.
-   As chaves criptográficas são únicas por site, impedindo ataques de reutilização.

## Arquitetura da Aplicação

-   **Frontend (JavaScript)**:
    
    -   Usa a API `navigator.credentials` para interagir com o dispositivo do usuário.
    -   Envia e recebe desafios criptográficos do servidor.
-   **Backend (PHP)**:
    
    -   Gera desafios e argumentos para registro e autenticação.
    -   Processa as respostas do cliente e verifica a autenticidade das credenciais.

### Fluxo de Comunicação
               REGISTRO DE USUÁRIO
   Cliente (Navegador)          |         Servidor (PHP)
--------------------------------|--------------------------------
   Solicita argumentos          |    Gera argumentos para registro
         para registro          |    e os envia ao cliente
--------------------------------------------|--------------------------------
   Cria credenciais                                  |    Processa e armazena as credenciais
   e as envia ao servidor       |    criptográficas do usuário
--------------------------------|--------------------------------
   Login concluído              |    Retorna sucesso ou falha


## Termos de Privacidade

### Dados Coletados

-   Chaves públicas associadas ao usuário (armazenadas no banco de dados).
-   Informações de hardware do dispositivo de autenticação.

### Uso de Dados

-   As informações coletadas são utilizadas exclusivamente para autenticação.
-   Nenhuma informação biométrica (como impressões digitais) é enviada ou armazenada no servidor.

### Segurança

-   Os dados armazenados são protegidos por criptografia e seguem as melhores práticas de segurança.
-   Em caso de comprometimento do servidor, as informações armazenadas não podem ser usadas para comprometer as credenciais do usuário.

## Requisitos para Uso

-   **Navegador Compatível**: Google Chrome, Firefox ou qualquer navegador que suporte WebAuthn.
-   **Servidor PHP**: Versão 7.4 ou superior.
-   **Banco de Dados**: MySQL ou SQLite.

## Configuração

1.  Clone o repositório e inicie o PHP:
```
git clone https://github.com/faustinopsy/digital/.git
cd digital
php -S localhost:8000
```

para testar:
https://digital.ceuvago.com




