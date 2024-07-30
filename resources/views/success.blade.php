<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch API Example</title>
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-success">
	<div class="container-fluid bg-success">
		<div class="row m-auto">
			@if($message)
			<div class="col-12  m-auto mt-5 bg-success text-center rounded" style="height:200px">
				<p class="text-light mt-5">
				<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
				  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
				  <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
				</svg>
				</p>
				
				<h1 class="text-light mt-2">
					Payment Successful!

				</h1>
			</div>
			<div class="col-12 col-md-6  m-auto mt-5 bg-light  border mb-3 rounded p-5 pb-2" style="height:300px">
				<h2 class="text-center  fs-2">
					CREDITS
				</h2>
				<div class="row justify-space-between mt-4">
					<div class="col-6"><p class="mb-1 fs-4">Payment</p></div>
					<div class="col-6"><p class="mb-1 fs-4 text-end">${{ $message->amount }}</p></div>
				</div>
				<div class="row justify-space-between">
					<div class="col-6"><p class="mb-1 fs-4">Total Paid</p></div>
					<div class="col-6"><p class="mb-1 fs-4 text-end">${{ $message->amount }}</p></div>
				</div>
				<div class="row justify-space-between">
					<div class="col-12"><span class=" fs-5">Reference ID - {{ $message->payment_id }} </span></div>
					<p class="text-center mt-4">Keep this reciept for your record!</p>
				</div>
			</div>
			@else
				<div class="col-12 col-md-6 m-auto mt-5 bg-light  border mb-3 rounded p-5 pb-2" style="height:300px">
					<p>No Data found</p>
				</div>
			@endif
		</div>
	</div>

</body>
</html>