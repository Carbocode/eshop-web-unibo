const method= "Storage";
function getCookies(name){
    var name = name + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
}
function readTokenFromStorage(){
    return localStorage.getItem('auth_token');
}
export function getToken(){
    if(method == "Cookies"){
        return getCookies("auth_token");
    }
    return readTokenFromStorage();
}
document.querySelector('.fa-circle-user').addEventListener('click', () => {
  const token = getToken();
  const loginPageUrl = '/src/pages/login/';
  const profilePageUrl = '/src/pages/profile/';
  function isTokenExpired(token) {
      try {
          const payload = JSON.parse(atob(token.split('.')[1])); // Decode JWT payload
          const currentTime = Math.floor(Date.now() / 1000); // Current time in seconds
          return payload.exp < currentTime; // Check expiration
      } catch (e) {
          console.error('Invalid JWT:', e);
          return true; // Treat invalid token as expired
      }
  }

  // Check the token
  const jwt =token;
  if (!jwt || isTokenExpired(jwt)) {
    console.log('You need to login.');
      window.location.href = loginPageUrl; // Redirect to login page
  }
  else {
    console.log('You are already logged in.');
      window.location.href = profilePageUrl;
  }
});