function showForm(formId) {
    const forms = ['login-form', 'cadastro-form'];
    forms.forEach(function (id) {
       document.getElementById(id).style.display = id === formId ? 'block' : 'none';
       document.getElementById('userNamel').value='';
    });
  }
  window.addEventListener('load', function () {
    const loadingPercentage = document.getElementById('loadingPercentage');
    const splashScreen = document.getElementById('splashScreen');
    const conteudo = document.getElementById('conteudo');
    let percentage = 0;
    const interval = setInterval(() => {
        percentage += 5;
        loadingPercentage.style.width = `${percentage}%`;
        if (percentage >= 100) {
        clearInterval(interval);
        splashScreen.style.display = 'none';
        conteudo.style.display = 'block';
        showMessage('')
        }
    }, 50);
    const privacy = getCookie('privacy');
    if (privacy === 'aceito' || privacy === 'rejeito') {
      hidePopup();
     }
  });
   function showMessage(str) {
  
    let resultado=str;
   let messageText = document.getElementById('message-text');
    let messagetitulo = document.getElementById('titulo');
   if(resultado==''||resultado==null){
        messageText.innerText = 'Seja bem vindo ao Sistema Web-Auth Autenticação sem senha segura.';
        messagetitulo.innerText = 'Bem vindo!!';
   }else{
       messageText.innerText =resultado;
       messagetitulo.innerText = 'Atenção';
  }
  
   var message = document.getElementById('message');
   var overla = document.getElementById('overlay');
   message.style.display = 'block';
   overla.style.display = 'block';
   
   }
  
  function hideMessage() {
    var overla = document.getElementById('overlay');
    var message = document.getElementById('message');
   message.style.display = 'none';
   overla.style.display = 'none';
  }
  
  function setCookie(name, value, days) {
   const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
   const expires = 'expires=' + date.toUTCString();
   document.cookie = name + '=' + value + ';' + expires + ';path=/; domain='+ location.host +'; Secure;';
   }
  
   function getCookie(name) {
    const value = '; ' + name + '=';
    const decodedCookie = decodeURIComponent(document.cookie);
    let cookies = decodedCookie.trim();
    cookies = decodedCookie.split('; ');
      for (let i = 0; i < cookies.length; i++) {
          if (cookies[i].startsWith(name)) {
              return cookies[i].split('=')[1];
          }
      }
      return null;

   }
  
  function hidePopup() {
    document.getElementById('privacy-popup').style.display = 'none';
  }
 
  
  const acceptButton = document.getElementById('accept');
  const declineButton = document.getElementById('decline');
  acceptButton.addEventListener('click', () => {
    //localStorage.setItem('privacy', 'aceito'); 
    setCookie('privacy', 'aceito', '365')
    hidePopup();
  });
  
  declineButton.addEventListener('click', () => {
    //localStorage.setIte('privacy', 'rejeito'); 
    setCookie('privacy', 'rejeito', '365')
   hidePopup();
  });