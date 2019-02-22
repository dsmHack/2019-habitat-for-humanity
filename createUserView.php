<div class="wp-block-button"><a class="wp-block-button__link" onclick="send()">Add User<br></a></div>

<script>
function send() {
console.log("Here");
   if (window.XMLHttpRequest) {
       var xhr = new XMLHttpRequest();
   }
   else {
       //for IE
       var xhr = new ActiveXObject("Microsoft.XMLHTTP");
   }

   xhr.open("GET", "../wordpress/createUser.php?submit=true", true);

   xhr.onreadystatechange = function () {
       console.log("ReadyState: " + this.readyState);
       console.log("Status: " + this.status);
       if (this.readyState === 4 && this.status === 200) {
           console.log(this.responseText);
       }
   }

   xhr.send();
}
</script>