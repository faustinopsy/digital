<?php
require '../vendor/autoload.php';

use Webauthn\PublicKeyCredentialLoader;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;


header('Content-Type: application/json');
require_once '../backend/controller/UsuarioController.php';
require_once '../backend/model/Usuario.php';
require_once '../backend/config/Database.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['username'];
$clientDataJSON = base64_decode($data['clientDataJSON']);
$authenticatorData = base64_decode($data['authenticatorData']);
$signature = base64_decode($data['signature']);

$db = new Database();
$usuario = new Usuario();
$controller = new UsuarioController($db,$usuario);
$user = $controller->getByUsername($id);
$credential=json_decode($user,true);



$publicKeyCredentialSource = new PublicKeyCredentialSource(
    $credential['credId'],
    PublicKeyCredentialSource::USER_HANDLE_TYPE_PUBLIC,
    [],
    'localhost', 
    new PublicKeyCredentialUserEntity($id, $id, $id), 
    '', // AAGUID, this needs to be the correct AAGUID of your authenticator
    base64_decode($credential['attestationObject']), // Public key of the user
    'none',
    0
);

$publicKeyCredentialSourceRepository = new class($publicKeyCredentialSource) implements PublicKeyCredentialSourceRepository {
    private $publicKeyCredentialSource;

    public function __construct(PublicKeyCredentialSource $publicKeyCredentialSource) {
        $this->publicKeyCredentialSource = $publicKeyCredentialSource;
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource {
        return $this->publicKeyCredentialSource;
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array {
        return [$this->publicKeyCredentialSource];
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void {
        $this->publicKeyCredentialSource = $publicKeyCredentialSource;
    }
};

$publicKeyCredentialLoader = new PublicKeyCredentialLoader($publicKeyCredentialSourceRepository);
$publicKeyCredential = $publicKeyCredentialLoader->load(base64_encode($data['rawId']));
$authenticatorAssertionResponse = $publicKeyCredential->getResponse();

$authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator($publicKeyCredentialSourceRepository);

try {
    $publicKeyCredentialCreationOptions = $credential['attestationObject']; // this needs to be the PublicKeyCredentialCreationOptions from the registration
    $authenticatorAssertionResponseValidator->check(
        $authenticatorAssertionResponse,
        $publicKeyCredentialCreationOptions, 
        $clientDataJSON, 
        $publicKeyCredentialSource->getUserHandle(),
        $publicKeyCredentialSource->getAaguid()
    );

    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'failure', 'message' => 'Invalid signature', 'error' => $e->getMessage()]);
}
?>
