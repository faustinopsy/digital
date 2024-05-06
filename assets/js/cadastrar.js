document.getElementById('btnRegister').addEventListener('click', function() {
    var username = document.getElementById('username').value;
    var email = document.getElementById('email').value;
    var password = document.getElementById('password').value;
    
    // Em um caso real, o desafio deve ser fornecido pelo servidor
    var challenge = new Uint8Array(32);
    window.crypto.getRandomValues(challenge);
    
    var publicKeyCredentialCreationOptions = {
      challenge: challenge,
      rp: { name: "Exemplo RP", id: location.host },
      user: {
        id: new TextEncoder().encode(username),
        name: username,
        displayName: username
      },
      pubKeyCredParams: [{ type: "public-key", alg: -7 }],
      authenticatorSelection: { authenticatorAttachment: "platform" },
      timeout: 60000,
      attestation: "direct"
    };
    
    navigator.credentials.create({ publicKey: publicKeyCredentialCreationOptions })
      .then(function (newCredentialInfo) {
        console.log("Credencial criada:", newCredentialInfo);
        
        var id = newCredentialInfo.id;
        var attestationObject = new Uint8Array(newCredentialInfo.response.attestationObject);
        var clientDataJSON = new Uint8Array(newCredentialInfo.response.clientDataJSON);
        
        var user = {
          username: username,
          email: email,
          password: password, // Em uma situação real, as senhas devem ser armazenadas de forma segura e não em texto simples
          credId: id,
          attestationObject: arrayBufferToBase64(attestationObject),
          clientDataJSON: arrayBufferToBase64(clientDataJSON)
        };
        
        fetch('api/index.php/usuarios', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(user)
        }).then(function (response) {
          if (response.ok) {
            return response.json();
          } else {
            throw new Error('Erro ao registrar usuário: ' + response.statusText);
          }
        }).then(function (data) {
            showMessage( data.mensagem)
            document.getElementById('username').value='';
            document.getElementById('email').value='';
            document.getElementById('password').value='';
          //console.log('Usuário registrado com sucesso:', data);
        }).catch(function (error) {
          console.error('Erro:', error);
        });
        
      }).catch(function (err) {
        console.error("Erro ao criar credencial:", err);
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