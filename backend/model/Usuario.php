<?php 
class Usuario {
    private $rpId;
    private $attestationFormat;
    private $credentialId;
    private $credentialPublicKey;
    private $certificateChain;
    private $certificate;
    private $certificateIssuer;
    private $signatureCounter;
    private $AAGUID;
    private $rootValid;
    private $userPresent;
    private $userVerified;
    private $userId;
    private $userName; // Email
    private $userDisplayName; // Nome

    public function __construct() { }

    // Getters
    public function getRpId() {
        return $this->rpId;
    }

    public function getAttestationFormat() {
        return $this->attestationFormat;
    }

    public function getCredentialId() {
        return $this->credentialId;
    }

    public function getCredentialPublicKey() {
        return $this->credentialPublicKey;
    }

    public function getCertificateChain() {
        return $this->certificateChain;
    }

    public function getCertificate() {
        return $this->certificate;
    }

    public function getCertificateIssuer() {
        return $this->certificateIssuer;
    }

    public function getSignatureCounter() {
        return $this->signatureCounter;
    }

    public function getAAGUID() {
        return $this->AAGUID;
    }

    public function getRootValid() {
        return $this->rootValid;
    }

    public function getUserPresent() {
        return $this->userPresent;
    }

    public function getUserVerified() {
        return $this->userVerified;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getUserName() { // Email
        return $this->userName;
    }

    public function getUserDisplayName() { // Nome
        return $this->userDisplayName;
    }

    // Setters
    public function setRpId($rpId) {
        $this->rpId = $rpId;
    }

    public function setAttestationFormat($attestationFormat) {
        $this->attestationFormat = $attestationFormat;
    }

    public function setCredentialId($credentialId) {
        $this->credentialId = $credentialId;
    }

    public function setCredentialPublicKey($credentialPublicKey) {
        $this->credentialPublicKey = $credentialPublicKey;
    }

    public function setCertificateChain($certificateChain) {
        $this->certificateChain = $certificateChain;
    }

    public function setCertificate($certificate) {
        $this->certificate = $certificate;
    }

    public function setCertificateIssuer($certificateIssuer) {
        $this->certificateIssuer = $certificateIssuer;
    }

    public function setSignatureCounter($signatureCounter) {
        $this->signatureCounter = $signatureCounter;
    }

    public function setAAGUID($AAGUID) {
        $this->AAGUID = $AAGUID;
    }

    public function setRootValid($rootValid) {
        $this->rootValid = $rootValid;
    }

    public function setUserPresent($userPresent) {
        $this->userPresent = $userPresent;
    }

    public function setUserVerified($userVerified) {
        $this->userVerified = $userVerified;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setUserName($userName) { // Email
        $this->userName = $userName;
    }

    public function setUserDisplayName($userDisplayName) { // Nome
        $this->userDisplayName = $userDisplayName;
    }
}
