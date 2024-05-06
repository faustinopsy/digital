<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
/*
 * Copyright (C) 2022 Lukas Buchs
 * license https://github.com/lbuchs/WebAuthn/blob/master/LICENSE MIT
 *
 * Server test script for WebAuthn library. Saves new registrations in session.
 *
 *            JAVASCRIPT            |          SERVER
 * ------------------------------------------------------------
 *
 *               REGISTRATION
 *
 *      window.fetch  ----------------->     getCreateArgs
 *                                                |
 *   navigator.credentials.create   <-------------'
 *           |
 *           '------------------------->     processCreate
 *                                                |
 *         alert ok or fail      <----------------'
 *
 * ------------------------------------------------------------
 *
 *              VALIDATION
 *
 *      window.fetch ------------------>      getGetArgs
 *                                                |
 *   navigator.credentials.get   <----------------'
 *           |
 *           '------------------------->      processGet
 *                                                |
 *         alert ok or fail      <----------------'
 *
 * ------------------------------------------------------------
 */
require_once 'backend/config/Database.php';
require_once 'backend/controller/UsuarioController.php';
require_once 'backend/model/Usuario.php';
require_once 'src/WebAuthn.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");
$database = new Database();
$db = new Database();
$usuario = new Usuario();
$controller = new UsuarioController($db,$usuario);
try {
    session_start();

    // read get argument and post body
    $fn = filter_input(INPUT_GET, 'fn');
    $requireResidentKey = !!filter_input(INPUT_GET, 'requireResidentKey');
    $userVerification = filter_input(INPUT_GET, 'userVerification', FILTER_SANITIZE_SPECIAL_CHARS);

    $userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
    $userName = filter_input(INPUT_GET, 'userName', FILTER_SANITIZE_EMAIL);

    $userDisplayName = filter_input(INPUT_GET, 'userDisplayName', FILTER_SANITIZE_SPECIAL_CHARS);

    //$userId = preg_replace('/[^0-9a-f]/i', '', $userId);
    //$userName = preg_replace('/[^0-9a-z]/i', '', $userName);
    //$userDisplayName = preg_replace('/[^0-9a-z öüäéèàÖÜÄÉÈÀÂÊÎÔÛâêîôû]/i', '', $userDisplayName);

    $post = trim(file_get_contents('php://input'));
    if ($post) {
        $post = json_decode($post, null, 512, JSON_THROW_ON_ERROR);
    }

    if ($fn !== 'getStoredDataHtml') {

        // Formats
        $formats = [];
        if (filter_input(INPUT_GET, 'fmt_android-key')) {
            $formats[] = 'android-key';
        }
        if (filter_input(INPUT_GET, 'fmt_android-safetynet')) {
            $formats[] = 'android-safetynet';
        }
        if (filter_input(INPUT_GET, 'fmt_apple')) {
            $formats[] = 'apple';
        }
        if (filter_input(INPUT_GET, 'fmt_fido-u2f')) {
            $formats[] = 'fido-u2f';
        }
        if (filter_input(INPUT_GET, 'fmt_none')) {
            $formats[] = 'none';
        }
        if (filter_input(INPUT_GET, 'fmt_packed')) {
            $formats[] = 'packed';
        }
        if (filter_input(INPUT_GET, 'fmt_tpm')) {
            $formats[] = 'tpm';
        }

        $rpId = 'localhost';
        if (filter_input(INPUT_GET, 'rpId')) {
            $rpId = filter_input(INPUT_GET, 'rpId', FILTER_VALIDATE_DOMAIN);
            if ($rpId === false) {
                throw new Exception('invalid relying party ID');
            }
        }

        // types selected on front end
        $typeUsb = !!filter_input(INPUT_GET, 'type_usb');
        $typeNfc = !!filter_input(INPUT_GET, 'type_nfc');
        $typeBle = !!filter_input(INPUT_GET, 'type_ble');
        $typeInt = !!filter_input(INPUT_GET, 'type_int');
        $typeHyb = !!filter_input(INPUT_GET, 'type_hybrid');

        // cross-platform: true, if type internal is not allowed
        //                 false, if only internal is allowed
        //                 null, if internal and cross-platform is allowed
        $crossPlatformAttachment = null;
        if (($typeUsb || $typeNfc || $typeBle || $typeHyb) && !$typeInt) {
            $crossPlatformAttachment = true;

        } else if (!$typeUsb && !$typeNfc && !$typeBle && !$typeHyb && $typeInt) {
            $crossPlatformAttachment = false;
        }


        // new Instance of the server library.
        // make sure that $rpId is the domain name.
        $WebAuthn = new lbuchs\WebAuthn\WebAuthn('WebAuthn Library', $rpId, $formats);

        // add root certificates to validate new registrations
        if (filter_input(INPUT_GET, 'solo')) {
            $WebAuthn->addRootCertificates('rootCertificates/solo.pem');
        }
        if (filter_input(INPUT_GET, 'apple')) {
            $WebAuthn->addRootCertificates('rootCertificates/apple.pem');
        }
        if (filter_input(INPUT_GET, 'yubico')) {
            $WebAuthn->addRootCertificates('rootCertificates/yubico.pem');
        }
        if (filter_input(INPUT_GET, 'hypersecu')) {
            $WebAuthn->addRootCertificates('rootCertificates/hypersecu.pem');
        }
        if (filter_input(INPUT_GET, 'google')) {
            $WebAuthn->addRootCertificates('rootCertificates/globalSign.pem');
            $WebAuthn->addRootCertificates('rootCertificates/googleHardware.pem');
        }
        if (filter_input(INPUT_GET, 'microsoft')) {
            $WebAuthn->addRootCertificates('rootCertificates/microsoftTpmCollection.pem');
        }
        if (filter_input(INPUT_GET, 'mds')) {
            $WebAuthn->addRootCertificates('rootCertificates/mds');
        }

    }

    // ------------------------------------
    // request for create arguments
    // ------------------------------------

    if ($fn === 'getCreateArgs') {
        $createArgs = $WebAuthn->getCreateArgs(\hex2bin($userId), $userName, $userDisplayName, 20, $requireResidentKey, $userVerification, $crossPlatformAttachment);

        header('Content-Type: application/json');
        print(json_encode($createArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['desafio'] = $WebAuthn->getChallenge();



    // ------------------------------------
    // request for get arguments
    // ------------------------------------

    } else if ($fn === 'getGetArgs') {
        $ids = [];

        // if ($requireResidentKey) {
        //     if (!isset($_SESSION['registrations']) || !is_array($_SESSION['registrations']) || count($_SESSION['registrations']) === 0) {
        //         throw new Exception('we do not have any registrations in session to check the registration');
        //     }

        // } else {
            //$credentials = $database->read('users', ['userId' => hex2bin($userId)]);
            $credentials = $controller->verificarLogin(hex2bin($userId));
            foreach ($credentials as $credential) {
                if ($credential['userId'] === hex2bin($userId)) {
                    $ids[] =base64_decode($credential['credentialId']);
                }
            }
            // if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
            //     foreach ($_SESSION['registrations'] as $reg) {
            //         if ($reg->userId === $userId) {
            //             $ids[] = $reg->credentialId;
            //         }
            //     }
            // }

            if (count($ids) === 0) {
                // throw new Exception('no registrations in session for userId ' . $userId);
                throw new Exception('Não há registro na base ');
            }
        // }

        $getArgs = $WebAuthn->getGetArgs($ids, 20, $typeUsb, $typeNfc, $typeBle, $typeHyb, $typeInt, $userVerification);

        header('Content-Type: application/json');
        print(json_encode($getArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['desafio'] = $WebAuthn->getChallenge();



    // ------------------------------------
    // process create
    // ------------------------------------

    } else if ($fn === 'processCreate') {
        $clientDataJSON = base64_decode($post->clientDataJSON);
        $attestationObject = base64_decode($post->attestationObject);
        $challenge = $_SESSION['desafio'];

        // processCreate returns data to be stored for future logins.
        // in this example we store it in the php session.
        // Normaly you have to store the data in a database connected
        // with the user name.
        $data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, $userVerification === 'required', true, false);

        // add user infos
        $data->userId = $userId;
        $data->userName = $userName;
        $data->userDisplayName = $userDisplayName;
        // Dados para inserir no banco de dados
        $serializedChallenge = serialize($_SESSION['desafio']);
    $dataDb = [
        'rpId' => $data->rpId,
        'attestationFormat' => $data->attestationFormat,
        'credentialId' => base64_encode($data->credentialId),
        'credentialPublicKey' => base64_encode($data->credentialPublicKey),
        'certificateChain' => base64_encode($data->certificateChain),
        'certificate' => $data->certificate,
        'certificateIssuer' => $data->certificateIssuer,
        'signatureCounter' => $data->signatureCounter,
        'AAGUID' => base64_encode($data->AAGUID),
        'rootValid' => base64_encode($data->rootValid),
        'userPresent' => $data->userPresent,
        'userVerified' => $data->userVerified,
        'userId' => hex2bin($data->userId),
        'userName' => $data->userName,
        'userDisplayName' => $data->userDisplayName,
        'challenge'=>$serializedChallenge
    ];

    // Insere os dados no banco de dados
    $response = $controller->cadastrar($dataDb);
    //$database->create('users', $dataDb);
        // if (!isset($_SESSION['registrations']) || !array_key_exists('registrations', $_SESSION) || !is_array($_SESSION['registrations'])) {
        //     $_SESSION['registrations'] = [];
        // }
        // $_SESSION['registrations'][] = $data;

        $msg = 'Registrado com sucesso.';
        // if ($data->rootValid === false) {
        //     $msg = 'registration ok, but certificate does not match any of the selected root ca.';
        // }

        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;

        header('Content-Type: application/json');
        print(json_encode($return));



    // ------------------------------------
    // proccess get
    // ------------------------------------

    } else if ($fn === 'processGet') {
        $userId = base64_decode($post->userHandle);
        $credentials = $controller->verificarLogin($userId);

        $challengedb=$_SESSION['desafio'];

        $clientDataJSON = base64_decode($post->clientDataJSON);
        $authenticatorData = base64_decode($post->authenticatorData);
        $signature = base64_decode($post->signature);
        $userHandle = base64_decode($post->userHandle);
        $id = base64_decode($post->id);
        
        $credentialPublicKey = null;
        foreach ($credentials as $credential) {
            $credentialId = base64_decode($credential['credentialId']);
            if ($credentialId === $id) {
                $credentialPublicKey = base64_decode($credential['credentialPublicKey']);
                break;
            }
        }

        if ($credentialPublicKey === null) {
            throw new Exception('Public Key for credential ID not found!');
        }

        // if we have resident key, we have to verify that the userHandle is the provided userId at registration
        if ($requireResidentKey && $userHandle !== $userId) {
            throw new \Exception('userId doesnt match (is ' . bin2hex($userHandle) . ' but expect ' . $userId . ')');
        }

        // process the get request. throws WebAuthnException if it fails
        $resultado=$WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challengedb, null, $userVerification === 'required');

        $return = new stdClass();
        $return->success = true;

        header('Content-Type: application/json');
        print(json_encode($return));
        if($resultado){
            $credentials = $controller->verificarLogin($userId);
            foreach ($credentials as $credential) {
                $_SESSION['userName']  = $credential["userName"];
                $_SESSION['userDisplayName'] = $credential["userDisplayName"];
            }
            
            //header("Location: minhaarea.php"); 
            //exit();
        }
       
    // ------------------------------------
    // proccess clear registrations
    // ------------------------------------

    } else if ($fn === 'clearRegistrations') {
        $_SESSION['registrations'] = null;
        $_SESSION['challenge'] = null;

        $return = new stdClass();
        $return->success = true;
        $return->msg = 'all registrations deleted';

        header('Content-Type: application/json');
        print(json_encode($return));

    // ------------------------------------
    // display stored data as HTML
    // ------------------------------------

    } 
    // else if ($fn === 'getStoredDataHtml') {
    //     $html = '<!DOCTYPE html>' . "\n";
    //     $html .= '<html><head><style>tr:nth-child(even){background-color: #f2f2f2;}</style></head>';
    //     $html .= '<body style="font-family:sans-serif">';
    //     if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
    //         $html .= '<p>There are ' . count($_SESSION['registrations']) . ' registrations in this session:</p>';
    //         foreach ($_SESSION['registrations'] as $reg) {
    //             $html .= '<table style="border:1px solid black;margin:10px 0;">';
    //             foreach ($reg as $key => $value) {

    //                 if (is_bool($value)) {
    //                     $value = $value ? 'yes' : 'no';

    //                 } else if (is_null($value)) {
    //                     $value = 'null';

    //                 } else if (is_object($value)) {
    //                     $value = chunk_split(strval($value), 64);

    //                 } else if (is_string($value) && strlen($value) > 0 && htmlspecialchars($value, ENT_QUOTES) === '') {
    //                     $value = chunk_split(bin2hex($value), 64);
    //                 }
    //                 $html .= '<tr><td>' . htmlspecialchars($key) . '</td><td style="font-family:monospace;">' . nl2br(htmlspecialchars($value)) . '</td>';
    //             }
    //             $html .= '</table>';
    //         }
    //     } else {
    //         $html .= '<p>There are no registrations in this session.</p>';
    //     }
    //     $html .= '</body></html>';

    //     header('Content-Type: text/html');
    //     print $html;

    // // ------------------------------------
    // // get root certs from FIDO Alliance Metadata Service
    // // ------------------------------------

    // } else if ($fn === 'queryFidoMetaDataService') {

    //     $mdsFolder = 'rootCertificates/mds';
    //     $success = false;
    //     $msg = null;

    //     // fetch only 1x / 24h
    //     $lastFetch = \is_file($mdsFolder .  '/lastMdsFetch.txt') ? \strtotime(\file_get_contents($mdsFolder .  '/lastMdsFetch.txt')) : 0;
    //     if ($lastFetch + (3600*48) < \time()) {
    //         $cnt = $WebAuthn->queryFidoMetaDataService($mdsFolder);
    //         $success = true;
    //         \file_put_contents($mdsFolder .  '/lastMdsFetch.txt', date('r'));
    //         $msg = 'successfully queried FIDO Alliance Metadata Service - ' . $cnt . ' certificates downloaded.';

    //     } else {
    //         $msg = 'Fail: last fetch was at ' . date('r', $lastFetch) . ' - fetch only 1x every 48h';
    //     }

    //     $return = new stdClass();
    //     $return->success = $success;
    //     $return->msg = $msg;

    //     header('Content-Type: application/json');
    //     print(json_encode($return));
    // }

} catch (Throwable $ex) {
    $return = new stdClass();
    $return->success = false;
    $return->msg = $ex->getMessage();

    header('Content-Type: application/json');
    print(json_encode($return));
}