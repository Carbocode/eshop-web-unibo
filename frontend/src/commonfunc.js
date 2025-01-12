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