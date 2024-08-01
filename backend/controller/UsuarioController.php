<?php

class UsuarioController {
    private $db;
    private $usuario;
    public function __construct(Database $db,Usuario $usuario) {
        $this->db = $db;
        $this->usuario = $usuario;
    }

    public function cadastrar($dados) {
        $this->usuario->setRpId($dados["rpId"]);
        $this->usuario->setAttestationFormat($dados["attestationFormat"]);
        $this->usuario->setCredentialId($dados["credentialId"]);
        $this->usuario->setCredentialPublicKey($dados["credentialPublicKey"]);
        $this->usuario->setCertificateChain($dados["certificateChain"]);
        $this->usuario->setCertificate($dados["certificate"]);
        $this->usuario->setCertificateIssuer($dados["certificateIssuer"]);
        $this->usuario->setSignatureCounter($dados["signatureCounter"]);
        $this->usuario->setAAGUID($dados["AAGUID"]);
        $this->usuario->setRootValid($dados["rootValid"]);
        $this->usuario->setUserPresent($dados["userPresent"]);
        $this->usuario->setUserVerified($dados["userVerified"]);
        $this->usuario->setUserId($dados["userId"]);
        $this->usuario->setUserName($dados["userName"]); 
        $this->usuario->setUserDisplayName($dados["userDisplayName"]); 
    
        $data = [
            "rpId" => $this->usuario->getRpId(),
            "attestationFormat" => $this->usuario->getAttestationFormat(),
            "credentialId" => $this->usuario->getCredentialId(),
            "credentialPublicKey" => $this->usuario->getCredentialPublicKey(),
            "certificateChain" => $this->usuario->getCertificateChain(),
            "certificate" => $this->usuario->getCertificate(),
            "certificateIssuer" => $this->usuario->getCertificateIssuer(),
            "signatureCounter" => $this->usuario->getSignatureCounter(),
            "AAGUID" => $this->usuario->getAAGUID(),
            "rootValid" => $this->usuario->getRootValid(),
            "userPresent" => $this->usuario->getUserPresent(),
            "userVerified" => $this->usuario->getUserVerified(),
            "userId" => $this->usuario->getUserId(), 
            "userName" => $this->usuario->getUserName(), 
            "userDisplayName" => $this->usuario->getUserDisplayName() 
        ];
    
        $success = $this->db->create("users", $data);
        $retorno=['status'=>$success,'mensagem'=>"Cadastrado com sucesso"];
        return $retorno;
    }
    
    
    public function verificarLogin($dados) {
         $usuario=$this->db->read('users', ['userId' => $dados]);

        return $usuario;
    }
    public function solicitarCodigo($email) {
        $token = $this->criarTokenRecuperarSenha($email);
    
        if ($token) {
           // $linkRecuperacao = 'https://seu-site.com/recuperar-senha.php?token=' . $token;
             $to = $email;
            $from = "contato@ceuvago.com";
            $subject = "Código de segurança";
            $message = "Seu código de EXCLUSÃO é: " . $token;
            $header = implode("\r\n", [
              "From: $from",
              "MIME-Version: 1.0",
              "Content-type: text/html; charset=utf-8"
            ]);
             @mail($to, $subject, $message, $header);
            return "E-mail enviado com sucesso!";
        } else {
            return "Falha ao enviar o e-mail.";
        }
    }
    public function excluir($token, $email) {
       $resultado= $this->verificarToken($token);
        if ($resultado) {
            $result = $this->excluirDados($email);

            if ($result) {
                $this->removerToken($email);
                session_destroy();
                header('Location: index.html');
                return "Conta Excluida com sucesso!";
            } else {
                return "Falha ao tentar excluir.";
            }
        } else {
            return "Token inválido ou expirado.";
        }
    }
    public function verificarToken($token) {
        $resultado = $this->db->read('password_resets', ['token' => $token]);
        if ($resultado && $resultado[0]['expires_at'] > date('Y-m-d H:i:s')) {
            return $resultado[0]['email'];
        } else {
            return false;
        }
    }

    public function criarTokenRecuperarSenha($email) {
        $token = bin2hex(random_bytes(16));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $data = [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires_at
        ];
        $success = $this->db->create('password_resets', $data);
        if ($success) {
            return $token;
        } else {
            return false;
        }
    }
    
    public function excluirDados($email) {
        $success = $this->db->delete('users', ['userName' => $email]);
        return $success;
    }
    
    public function removerToken($email) {
        $success = $this->db->delete('password_resets', ['email' => $email]);
        return $success;
    }

    
}
