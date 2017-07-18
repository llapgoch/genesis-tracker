<script>
       function formValidation(login,pswd) {
         if (login != '')
     if (pswd != '') {
 window.location = "/prescription-exercises/?username=" + login + unescape("%26") + "password=" +pswd;
     }
     }
</script> 

<?php 
echo GenesisThemeShortCodes::readingBox('Enter Your Physiotec Login Details', 'Please enter your login details to view the Physiotec prescription exercise videos. <em>Please note: these are different to your 2 Day Wythenshawe login details</em>');
?>
		
<div id="PhysiotecIFrame"></div>
    
<div class="physiotec-login">
    <table width="300" >
        <tr>
            <td align="right">Login:</td><td><input id="physiologin" type="text" class="general-input" name="username"></td>
        </tr>
        <tr>
            <td align="right">Password:</td><td><input id="pswd" type="password" class="general-input" name="password"></td>
        </tr>
        <tr>
            <td colspan="2" class="login-button"><button class="button green large" onclick="formValidation(document.getElementById('physiologin').value,document.getElementById('pswd').value);">Log In to Physiotec</button></td>
        </tr>

    </table>
</div>