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
  
<div class="wrapper" style="background-color:#d5e7f8;padding:60px;">
	<form method="POST" action="<?php echo base_url(); ?>/login/register/create"  enctype="multipart/form-data">
	<div class="container">
	  <div class="row">
		<div class="col-md-12">
		  <h3><b>Registrering</b></h3>
		</div>
		
	  </div>
	  <div class="row">
		<div class="col-md-3 lh">
		  Institusjon/forening:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="institute" required>
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Gateadresse:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="address" required> 
		</div>
	  </div>
	  
	  <br/>
	  <div class="row">
		<div class="col-md-3 lh">
		  Poststed:
		</div>
		<div class="col-md-3">
		 <input type="text" placeholder="Nr" class="form-control" name="postal1" required>
		</div>
		<div class="col-md-6">
		 <input type="text" placeholder="Sted" class="form-control" name="postal2" required>
		</div>
	  </div>
	   <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Epost-adresse:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="email" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Telefon: 
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="phone" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-12">
		  <b>Kontaktperson:</b>
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Fornavn:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="fname" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Etternavn:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="lname" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Stilling/funksjon:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="position" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		 Epost-adresse:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="email2" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		Passord
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="password" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-3 lh">
		  Telefon: 
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="mobile" required> 
		</div>
	  </div>
	  <br/>
	  
	  <div class="row">
		<div class="col-md-12 right">
		 <input type="submit" class="btn btn-primary btn-lg" name="register" value="Send inn" style="float:right"> 
		</div>
	  </div>
	  <br/>
	  
	  
	</div>
	</form>
</div>

</body>
</html>
