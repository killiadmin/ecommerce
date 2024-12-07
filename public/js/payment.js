$(document).ready(async () => {
    const stripe = Stripe(stripePublicKey);
    const elements = stripe.elements();
    const cardElement = elements.create("card");
    cardElement.mount("#card-element");

    const test = await $.ajax({
        url: "/payment/infos",
        method: "GET",
        contentType: "application/json",
        dataType: "json"
    });
    console.log(test);


    $("#payment-form").on("submit", async (event) => {
        event.preventDefault();

        const cardholderName = $("#cardholder-name").val();
        const cardCountry = $("#cardcountry").val();

        const { error: backendError, clientSecret } = await $.ajax({
            url: "/create-payment-intent",
            method: "POST",
            contentType: "application/json",
            dataType: "json"
        });

        if (backendError) {
            $("#card-errors").text(backendError);
            return;
        }

        const {error: stripeError} = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: cardholderName,
                    email: "jean.dupont@example.com",
                    phone: "+33123456789",
                    address: {
                        line1: "123 Rue de Paradis",
                        line2: "Appartement 4B",
                        city: "Paris",
                        state: "Île-de-France",
                        postal_code: "75010",
                        country: cardCountry
                    }
                }
            }
        });

        if (stripeError) {
            $("#card-errors").text(stripeError.message);
            return;
        }

        alert("Paiement réussi!");
    });
});
