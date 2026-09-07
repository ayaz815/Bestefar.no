<!DOCTYPE html>
<html lang="en">
<head>
  <title>Registration Form</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
	.lh{
		line-height: 30px;
	}
  </style>
</head>
<body>
  
<div class="wrapper" style="background-color:#;padding:60px;">
	<div class="container" align="center">
		<div style="border: 2px solid #186eab;padding:30px;border-radius: 20px;background-color: #d5e7f8;">
			 <?php if($response == 'success'){ ; ?>
			 <h2>Vi takker for din registrering!<h2>
			 <h4>Du vil bli kontaktet når</h4>
			<h4>innmeldingen din er behandlet</h4>
			 <?php }elseif($response == 'already_exist'){ ?>
			<h2>E-postadressen din eksisterer allerede<h2>
			 <?php } ?>
		</div>
	</div>
</div>

</body>
</html>
