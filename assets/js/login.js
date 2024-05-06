document.getElementById('btnLogin').addEventListener('click', function() {
    var username = document.getElementById('usernamel').value;
    
    // Em um caso real, o desafio deve ser fornecido pelo servidor
    var challenge = new Uint8Array(32);
    window.crypto.getRandomValues(challenge);
    
    fetch(`api/index.php/usuarios?username=${username}`)
      .then(function(response) {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error('Erro ao obter informações do usuário: ' + response.statusText);
        }
      }).then(function(user) {
        user = JSON.parse(user);
        var publicKeyCredentialRequestOptions = {
          challenge: challenge,
          timeout: 60000,
          rpId: location.host,
          allowCredentials: [{
            id: base64ToArrayBuffer(user.credId),  
            type: 'public-key'
          }]
        };
        
        navigator.credentials.get({ publicKey: publicKeyCredentialRequestOptions })
          .then(function(assertion) {
            console.log('Sucesso na autenticação:', assertion);
            
            var authenticatorData = new Uint8Array(assertion.response.authenticatorData);
            var clientDataJSON = new Uint8Array(assertion.response.clientDataJSON);
            var signature = new Uint8Array(assertion.response.signature);
            
            var loginData = {
              id: assertion.id,
              username:username,
              authenticatorData: arrayBufferToBase64(authenticatorData),
              clientDataJSON: arrayBufferToBase64(clientDataJSON),
              signature: arrayBufferToBase64(signature)
            };
            //showMessage('Autentidaco com sucesso')
            // Enviar os dados de login para o servidor para validação
            fetch('api/validar_assinatura.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(loginData)
            }).then(function(response) {
              if (!response.ok) {
                throw new Error('Erro no servidor ao validar a assinatura: ' + response.statusText);
              }
              return response.json();
            }).then(function(data) {
              showMessage('Autenticado com sucesso');
            }).catch(function(error) {
              console.error('Erro ao validar a assinatura no servidor:', error);
            });
            // e concluir a autenticação.
            
          }).catch(function(err) {
            console.error('Erro na autenticação:', err);
          });
        
      }).catch(function(err) {
        console.error('Erro ao obter informações do usuário:', err);
      });
});
// Função para converter um ArrayBuffer para uma string base64
function arrayBufferToBase64(buffer) {
    var binary = '';
    var bytes = new Uint8Array(buffer);
    var len = bytes.byteLength;
    for (var i = 0; i < len; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}

// Função para converter uma string base64 em um ArrayBuffer
function base64ToArrayBuffer(base64) {
  let base64url = base64.replace(/-/g, '+').replace(/_/g, '/');

var binary_string = window.atob(base64url);
    var len = binary_string.length;
    var bytes = new Uint8Array(len);
    for (var i = 0; i < len; i++) {
        bytes[i] = binary_string.charCodeAt(i);
    }
    return bytes.buffer;
}
