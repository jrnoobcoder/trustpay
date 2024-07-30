<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch API Example</title>
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>


  <!-- resources/views/payment/form.blade.php -->
<div class="container">
@if (session('success_message'))
    <div class="alert alert-success">
        {{ session('success_message') }}
    </div>
@endif
		<div class="row justify-content-center d-none" id="expireMsg">
            <div class="col-md-8">
				<p><span>Link Expired</span><br> Kindly talk to your Agent!</p>
			</div>
		</div>
        <div class="row justify-content-center" id="formSection">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Payment Form</div>
                    <div class="card-body">
                        <form id="payment-form" action="{{ route('payment.process') }}" method="POST">
                            @csrf
							
							<div class="form-group pb-2">
								<label for="name" class="form-label">Name</label>
								<input type="text" class="form-control" id="name" name="name" placeholder="Card holder name">
							</div>
							<div class="form-group pb-2">
								<label for="email" class="form-label">Email address</label>
								<input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" disabled>
							</div>
                            <div class="form-group pb-2">
                                <label for="card-element" class="form-label">
                                    Credit or debit card
                                </label>
                                <div id="card-element" class="">
                                    <!-- A Stripe Element will be inserted here. -->
                                </div>
                                <!-- Used to display form errors. -->
                                <div id="card-errors" role="alert"></div>
                            </div>
							<div class="form-group">
							<button id="card-button" class="btn btn-primary" type="submit" data-secret="">
                                Pay Now
                            </button>
							</div>
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div>

    <script src="https://js.stripe.com/v3/"></script>
   
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const pid = "{{$p_id}}";
		const url = "/api/payment/intent?payment_id=" + pid;
		console.log(url);
		fetch(url)
			.then(response => response.json())
			.then(data => {
				let msg = document.getElementById('expireMsg');
				let frm = document.getElementById('formSection');
				if(data.response.payment_status == "paid"){
					msg.classList.remove("d-none");
					frm.classList.add("d-none");
				} else {
					frm.classList.remove("d-none");
					msg.classList.add("d-none");
					const button = document.getElementById('card-button');
					const email = document.getElementById('email');
					button.setAttribute('data-secret', data.response.intent.client_secret);
					email.setAttribute('value', data.response.user.customer_email);
					const client_secret = data.response.intent.client_secret;
					
					console.log(data.response.intent); 
					console.log(data.response.user); 
					
					const stripe = Stripe('{{ env('STRIPE_KEY') }}');
					const appearance = {
						theme: 'stripe',
						rules: {
							'.Label': {
								fontWeight: 'bold',
								textTransform: 'uppercase',
							}
						}
					};
					const elements = stripe.elements({ clientSecret: client_secret, appearance });
					const paymentElement = elements.create('payment');
					paymentElement.mount('#card-element');
					paymentElement.on('change', function(event) {
						const cardErrors = document.getElementById('card-errors');
						if (event.error) {
							cardErrors.textContent = event.error.message;
						} else {
							cardErrors.textContent = '';
						}
					});
					
					const form = document.getElementById('payment-form');
					form.addEventListener('submit', async function(event) {
						event.preventDefault();
						button.disabled = true; 

						const { paymentIntent, error } = await stripe.confirmPayment({
							elements,
							confirmParams: {
								return_url: '{{ route("payment.process") }}',
							}
						});

						if (error) {
							const cardErrors = document.getElementById('card-errors');
							cardErrors.textContent = error.message;
							button.disabled = false; 
						} else {
							if (paymentIntent.status === 'succeeded') {
								const hiddenInput = document.createElement('input');
								hiddenInput.setAttribute('type', 'hidden');
								hiddenInput.setAttribute('name', 'payment_intent_id');
								hiddenInput.setAttribute('value', paymentIntent.id);
								form.appendChild(hiddenInput);
								console.log("Success");
								form.submit(); 
							}
						}
					});
				}
			})
			.catch(error => {
				console.error('Error:', error);
			});
	});
</script>

</body>
</html>
