<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch API Example</title>
</head>
<body>
  <form id="myForm">
        
        <button type="submit">Submit</button>
    </form>

    <script>
	
	/// Replace 'https://api.example.com/submit' with your API endpoint
const apiUrl = 'https://checkout.stripe.com/pay/pi_3Pc2XDD0V1BjnH0D18SsH3HH?success_url=https%3A%2F%2Ftrustpay.risecore.in%2Fapi%2Fpayment%2Fsuccess&cancel_url=https%3A%2F%2Ftrustpay.risecore.in%2Fapi%2Fpayment%2Fcancel';

// Function to handle form submission
function handleSubmit(event) {
    event.preventDefault(); // Prevent the default form submission
window.location.assign(apiUrl);
    // Get form data
    const formData = new FormData(event.target);

    // Prepare data for POST request
    const postData = {
        method: 'POST',
        body: formData
    };

    // Perform POST request
    fetch(apiUrl, postData)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse the JSON from the response
        })
        .then(data => {
            // Work with the JSON data here
            console.log('Response from server:', data);
            // Optionally update UI to reflect successful submission
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            // Optionally update UI to reflect error
        });
}

// Add event listener to the form for 'submit' event
document.getElementById('myForm').addEventListener('submit', handleSubmit);


	</script>
</body>
</html>
