<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css">
		*{font-family: Verdana,Tahoma,Arial;}
		h1,h2,h3,h4,h5,h6{background-color: #48a;color: white;}
		h1{padding: 10px 20px;}
		.content{width: 70%;text-align: left;}
		
		.error { font-size: smaller; border: solid 1px gray; padding: 4px }
		.error div { padding: 2px; background-color: lightgray; font-weight: bold }
		.error pre { overflow-x: scroll; }
	</style>
  </head>
  <body>
	<div align="center"><div class="content">
		<h1>404 Not Found</h1>
		<p>
			Страница, которую вы запршиваете, не существует.
		</p>
		<?php if (defined('DEBUG') && DEBUG && isset($error)) { ?>
		<div class="error">
			<div>Debug Info:</div>
			<pre><?=$error?></pre>
		</div>
		<?php } ?>
	</div></div>
  </body>
</html>
