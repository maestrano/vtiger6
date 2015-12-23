<?php

require_once("PortalConfig.php");
require_once("language/$default_language.lang.php");
include("version.php");
include_once('include/utils/utils.php');

@session_start();
if(isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']))
{
	header("Location: index.php?action=index&module=.'$module'");
	exit;
}
if($_REQUEST['close_window'] == 'true')
{
   ?>
	<script language="javascript">
        	window.close();
	</script>
   <?php
}
global $default_charset;
header('Content-Type: text/html; charset='.$default_charset);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html class="bg-gray">
	<head>
		<title>CPTIGER - Vtiger customer portal</title>
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="css/morris/morris.css" rel="stylesheet" type="text/css" />
        <link href="css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <link href="css/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
        <link href="css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
        <link href="css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />
	</head>

	<body class="bg-gray">
		<div class="form-box" id="login-box">
    		<div class="header" style="font-weight: 400;">Your Portal</div>
      		<form name="login" action="CustomerAuthenticate.php" method="post">
				<div class="body bg-gray">
                	<div class="form-group">
						<?php
							if($_REQUEST['login_error'] != '')
								echo getTranslatedString(base64_decode($_REQUEST['login_error'])); 
						?>
						<input type="text" id="username" name="username" class="form-control" placeholder="<?php echo getTranslatedString('LBL_EMAILID');?>">
					</div>
					<div class="form-group">
						<input type="password" id="pw" name="pw" class="form-control" placeholder="<?php echo getTranslatedString('LBL_PASSWORD');?>">
					</div>
				</div>
				<div class="footer" >
					<button type="submit" class="btn bg-light-blue btn-block" onclick="return validateLoginDetails();" style="font-size: 16px; font=weight:500;"><?php  echo getTranslatedString('LBL_LOGIN');?></button>  
                    <p><a href="supportpage.php?param=forgot_password" style="font-size:16px;"><?php  echo getTranslatedString('LBL_FORGOT_LOGIN');?></a></p>
                    <a href="#" class="text-center" style="font-size:16px;"><?php  echo getTranslatedString('LBL_LOGIN_NOTE');?></a>
            	</div>
			</form>
        </div>
	</body>
</html>

<script language="javascript">
function validateLoginDetails()
{
	var user = trim(document.getElementById("username").value);
	var pass = trim(document.getElementById("pw").value);
	if(user != '')
	{
		if(pass != '')
			return true;
		else
		{
			alert("Please enter a valid password.");
			return false;
		}
	}
	else
	{
		alert("Please enter valid username.");
		return false;
	}
}
function trim(s)
{
	while (s.substring(0,1) == " " || s.substring(0,1) == "\n")
	{
		s = s.substring(1, s.length);
	}
	while (s.substring(s.length-1, s.length) == " " || s.substring(s.length-1,s.length) == "\n") {
		s = s.substring(0,s.length-1);
	}
	return s;
}

</script>

<?php
?>
