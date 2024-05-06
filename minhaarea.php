<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está autenticado
if (!isset($_SESSION['userName'])) {
    header("Location: index.html"); // Redireciona para a página de login se o usuário não estiver autenticado
    exit();
}
$message='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'backend/config/Database.php';
    require_once 'backend/controller/UsuarioController.php';
    require_once 'backend/model/Usuario.php';
    $db = new Database();
    $usuario = new Usuario();
    $controller = new UsuarioController($db,$usuario);
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'solicitar_codigo':
            $email = urldecode($_SESSION['userName']) ?? '';
            $message = $controller->solicitarCodigo($email);
            break;
        case 'excluir':
            $codigo = $_POST['codigo'] ?? '';
            $email = urldecode($_SESSION['userName']) ?? '';
            $message = $controller->excluir($codigo, $email);
            break;
        case 'logout':
            session_destroy();
            // Redireciona para a página de início
            header("Location: index.html");
            exit();
        break;
        default:
            $message = "Ação inválida.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang='pt_BR'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Minha Área</title>
    <link rel='stylesheet' href='assets/css/minhaarea.css?v2'>
</head>
<body>
    <div class="menu">
    Olá, <?php echo urldecode($_SESSION['userName']); ?>
        <form action="minhaarea.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" name="logout" class="logout-btn">Sair</button>
        </form>
    </div>
    <div class="card">
        <h2>Bem-vindo, <?php echo $_SESSION['userDisplayName']; ?></h2>
        <p><?php echo !empty($message)?$message:''; ?></p>
        <p>A hora atual é: <?php echo date('H:i:s'); ?></p>
        <form action="minhaarea.php" method="POST">
            <input type="hidden" name="action" value="solicitar_codigo">
            <button class="btn" type="submit">Solicitar Exclusão</button>
        </form>
        <form action="minhaarea.php" method="POST">
          <input type="hidden" name="action" value="excluir">
          <p>Código Enviado por E-mail:</p>
          <input type="text" name="codigo" id="codigo" required>
         <button class="btn" type="submit">Excluir</button>
      </form>
    </div>
    <div class="footer">
        © 2023 Minha Aplicação. Todos os direitos reservados.
    </div>
</body>
</html>
