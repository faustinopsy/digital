async function createRegistration() {
    try {

        // check browser support
        if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
            throw new Error('Browser not supported.');
        }
        // Check if the email and user name fields are filled and valid
        
        let emailField = document.getElementById('userName');
        let userNameField = document.getElementById('userDisplayName');
  
        var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        // if (!emailField.value) {
        //     showMessage('Por favor, insira um e-mail válido.');
        //     return;
        // }

        if (!userNameField.value) {
            showMessage('Por favor, insira o nome do usuário.');
            return;
        }
        // get create args
        let response = await window.fetch('server.php?fn=getCreateArgs' + getGetParams(), {
            method:'GET', 
            cache:'no-cache'
        });
        const createArgs = await response.json();

        // error handling
        if (createArgs.success === false) {
            throw new Error(createArgs.msg || 'unknown error occured');
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(createArgs);

        // create credentials
        const cred = await navigator.credentials.create(createArgs);

        // create object
        const authenticatorAttestationResponse = {
            transports: cred.response.getTransports  ? cred.response.getTransports() : null,
            clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
        };

        // check auth on server side
        response = await window.fetch('server.php?fn=processCreate' + getGetParams(), {
            method  : 'POST',
            body    : JSON.stringify(authenticatorAttestationResponse),
            cache   : 'no-cache'
        });
        const authenticatorAttestationServerResponse = await response.json();

        // prompt server response
        if (authenticatorAttestationServerResponse.success) {
            //reloadServerPreview();
            //window.alert(authenticatorAttestationServerResponse.msg || 'registration success');
            showMessage(authenticatorAttestationServerResponse.msg);
            document.getElementById('userName').value='';
            document.getElementById('userDisplayName').value='';
        } else {
            throw new Error(authenticatorAttestationServerResponse.msg);
        }

    } catch (err) {
        //reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    }
}


/**
 * checks a FIDO2 registration
 * @returns {undefined}
 */
async function checkRegistration() {
    try {

        if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
            throw new Error('Browser not supported.');
        }

        // get check args
        let rep = await window.fetch('server.php?fn=getGetArgs' + getGetParams(), {method:'GET',cache:'no-cache'});
        const getArgs = await rep.json();

        // error handling
        if (getArgs.success === false) {
            throw new Error(getArgs.msg);
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(getArgs);

        // check credentials with hardware
        const cred = await navigator.credentials.get(getArgs);

        // create object for transmission to server
        const authenticatorAttestationResponse = {
            id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
            clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
            signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
            userHandle: cred.response.userHandle ? arrayBufferToBase64(cred.response.userHandle) : null
        };

        // send to server
        rep = await window.fetch('server.php?fn=processGet' + getGetParams(), {
            method:'POST',
            body: JSON.stringify(authenticatorAttestationResponse),
            cache:'no-cache'
        });
        const authenticatorAttestationServerResponse = await rep.json();

        // check server response
        if (authenticatorAttestationServerResponse.success) {
            //reloadServerPreview();
            showMessage(authenticatorAttestationServerResponse.msg);
            window.location.href = "minhaarea.php";
        } else {
            throw new Error(authenticatorAttestationServerResponse.msg);
        }

    } catch (err) {
        //reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    }
}

function clearRegistration() {
    window.fetch('server.php?fn=clearRegistrations' + getGetParams(), {method:'GET',cache:'no-cache'}).then(function(response) {
        return response.json();

    }).then(function(json) {
       if (json.success) {
           //reloadServerPreview();
           window.alert(json.msg);
       } else {
           throw new Error(json.msg);
       }
    }).catch(function(err) {
        //reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    });
}


function queryFidoMetaDataService() {
    window.fetch('server.php?fn=queryFidoMetaDataService' + getGetParams(), {method:'GET',cache:'no-cache'}).then(function(response) {
        return response.json();

    }).then(function(json) {
       if (json.success) {
           window.alert(json.msg);
       } else {
           throw new Error(json.msg);
       }
    }).catch(function(err) {
        window.alert(err.message || 'unknown error occured');
    });
}

/**
 * convert RFC 1342-like base64 strings to array buffer
 * @param {mixed} obj
 * @returns {undefined}
 */
function recursiveBase64StrToArrayBuffer(obj) {
    let prefix = '=?BINARY?B?';
    let suffix = '?=';
    if (typeof obj === 'object') {
        for (let key in obj) {
            if (typeof obj[key] === 'string') {
                let str = obj[key];
                if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                    str = str.substring(prefix.length, str.length - suffix.length);

                    let binary_string = window.atob(str);
                    let len = binary_string.length;
                    let bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++)        {
                        bytes[i] = binary_string.charCodeAt(i);
                    }
                    obj[key] = bytes.buffer;
                }
            } else {
                recursiveBase64StrToArrayBuffer(obj[key]);
            }
        }
    }
}

/**
 * Convert a ArrayBuffer to Base64
 * @param {ArrayBuffer} buffer
 * @returns {String}
 */
function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

/**
 * Get URL parameter
 * @returns {String}
 */
function toHex(string) {
    var result = '';
    for (var i=0; i<string.length; i++) {
        result += string.charCodeAt(i).toString(16);
    }
    return result;
}


function getGetParams() {
    var userName = document.getElementById('userNamel').value;
    if(userName==''){
        userName = document.getElementById('userName').value;
    }
    var userNameHex = toHex(userName);
    let url = '';

    url += '&apple=1' ;
    url += '&yubico=1' ;
    url += '&solo=1' ;
    url += '&hypersecu=1' ;
    url += '&google=1' ;
    url += '&microsoft=1' ;
    url += '&mds=1' ;

    url += '&requireResidentKey=1' ;

    url += '&type_usb=1' ;
    url += '&type_nfc=1' ;
    url += '&type_ble=1' ;
    url += '&type_int=1' ;
    url += '&type_hybrid=1' ;

    url += '&fmt_android-key=1' ;
    url += '&fmt_android-safetynet=1' ;
    url += '&fmt_apple=1' ;
    url += '&fmt_fido-u2f=1' ;
    url += '&fmt_none=1' ;
    url += '&fmt_packed=1' ;
    url += '&fmt_tpm=1' ;

    url += '&rpId='+ encodeURIComponent(location.host) ;

    url += '&userId=' + encodeURIComponent(userNameHex);
    url += '&userName=' + encodeURIComponent(userName);
    url += '&userDisplayName=' + encodeURIComponent(document.getElementById('userDisplayName').value);

    //url += '&userVerification=required';
    //url += '&userVerification=preferred';
    url += '&userVerification=discouraged';
    

    return url;
}

// function reloadServerPreview() {
//     let iframe = document.getElementById('serverPreview');
//     iframe.src = iframe.src;
// }

function setAttestation(attestation) {
    let inputEls = document.getElementsByTagName('input');
    for (const inputEl of inputEls) {
        if (inputEl.id && inputEl.id.match(/^(fmt|cert)\_/)) {
            inputEl.disabled = !attestation;
        }
        if (inputEl.id && inputEl.id.match(/^fmt\_/)) {
            inputEl.checked = attestation ? inputEl.id !== 'fmt_none' : inputEl.id === 'fmt_none';
        }
        if (inputEl.id && inputEl.id.match(/^cert\_/)) {
            inputEl.checked = attestation ? inputEl.id === 'cert_mds' : false;
        }
    }
}

/**
 * force https on load
 * @returns {undefined}
 */
 function hideElements() {
// Array dos IDs dos elementos que você quer manter visíveis
const visibleElementIds = [ 'userName', 'userDisplayName', 'createRegistration', 'checkRegistration'];

// Selecionando todos os divs da página
const divs = document.getElementsByTagName('div');

// Iterando sobre todos os divs
for (let i = 0; i < divs.length; i++) {
// Se o div não contém um elemento que queremos manter visível, o escondemos
if (!containsVisibleElement(divs[i], visibleElementIds)) {
    divs[i].style.display = 'none';
}
}
}

function containsVisibleElement(element, visibleElementIds) {
for (let i = 0; i < visibleElementIds.length; i++) {
if (element.querySelector(`#${visibleElementIds[i]}`)) {
    return true;
}
}
return false;
}

// document.addEventListener("DOMContentLoaded", function() {
//     // Pegando o RP ID do domínio atual
//     const rpId = window.location.hostname;
//     document.getElementById('rpId').value = rpId;

//     // Escondendo elementos desnecessários
//     hideElements();
// });
