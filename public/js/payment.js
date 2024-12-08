$(document).ready(async () => {
    const stripe = Stripe(stripePublicKey);
    const elements = stripe.elements();
    const cardElement = elements.create("card");
    cardElement.mount("#card-element");
    const montantTotalTtc = parseFloat($("#montantTotalPriceTtc").text().replace(",", "."));
    const nbTotalArticles = $("#nbTotalArticles").val();

    const userDatas = await $.ajax({
        url: "/payment/infos",
        method: "GET",
        contentType: "application/json",
        dataType: "json"
    });

    $("#payment-form").on("submit", async (event) => {
        event.preventDefault();

        const cardholderName = $("#cardholder-name").val();
        const cardCountry = $("#cardcountry").val();

        const { error: backendError, clientSecret } = await $.ajax({
            url: "/create-payment-intent",
            method: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                amount: Math.round(montantTotalTtc * 100),
                description: nbTotalArticles + " articles vendus",
            })
        });

        if (backendError || !clientSecret) {
            $("#card-errors").text(backendError || "Erreur lors de la création du PaymentIntent");
            return;
        }

        const { error: stripeError } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: cardholderName,
                    email: userDatas.email,
                    phone: userDatas.phone,
                    address: {
                        line1: userDatas.address.line1,
                        line2: "",
                        city: userDatas.address.city,
                        state: "",
                        postal_code: userDatas.address.postal_code,
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
