$(document).ready(async () => {
    const stripe = Stripe(stripePublicKey);
    const cardElement = initStripeElements(stripe);

    const montantTotalTtc = parseFloat($("#montantTotalPriceTtc").text().replace(",", "."));
    const nbTotalArticles = $("#nbTotalArticles").val();

    const userDatas = await fetchUserData();

    $("#payment-form").on("submit", async (event) => {
        event.preventDefault();

        const cardholderName = $("#cardholder-name").val();
        const cardCountry = $("#cardcountry").val();

        await handlePayment(stripe, cardElement, {
            cardholderName,
            cardCountry,
            amount: Math.round(montantTotalTtc * 100),
            quantity: nbTotalArticles,
            userDatas,
        });
    });
});


/**
 * Initializes and mounts the Stripe card element.
 *
 * @param {object} stripe
 * @return {object}
 */
function initStripeElements(stripe) {
    const elements = stripe.elements();
    const cardElement = elements.create("card");
    cardElement.mount("#card-element");
    return cardElement;
}


/**
 * Handles the payment process by integrating with Stripe API and back-end payment intent.
 *
 * @return {Promise<boolean>}
 */
async function handlePayment(stripe, cardElement, { cardholderName, cardCountry, amount, quantity, userDatas }) {
    try {
        const { error: backendError, clientSecret } = await $.ajax({
            url: "/create-payment-intent",
            method: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ amount, quantity })
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
                        line1: userDatas.address?.line1 || "",
                        line2: "",
                        city: userDatas.address?.city || "",
                        state: "",
                        postal_code: userDatas.address?.postal_code || "",
                        country: cardCountry
                    }
                }
            }
        });

        if (stripeError) {
            $("#card-errors").text(stripeError.message);
        } else {
            return true;
        }
    } catch (err) {
        $("#card-errors").text("Erreur inattendue : " + err.message);
    }
}

/**
 * Retrieves user-related payment information from the server.
 *
 * @return {Promise<Object>}
 */
async function fetchUserData() {
    try {
        return await $.ajax({
            url: "/payment/infos",
            method: "GET",
            contentType: "application/json",
            dataType: "json"
        });
    } catch (error) {
        console.error("Impossible de récupérer les données utilisateur.");
        return {};
    }
}
