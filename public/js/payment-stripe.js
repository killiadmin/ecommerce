$(document).ready(async () => {
    const stripe = Stripe(stripePublicKey);
    const cardElement = initStripeElements(stripe);

    const userDatas = await fetchUserData();

    $("#payment-form").on("submit", async (event) => {
        event.preventDefault();

        const cardholderName = $("#cardholder-name").val();
        const cardCountry = $("#cardcountry").val();

        await handlePayment(stripe, cardElement, {
            cardholderName,
            cardCountry,
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
async function handlePayment(stripe, cardElement, { cardholderName, cardCountry, userDatas }) {
    try {
        const { error: backendError, clientSecret } = await $.ajax({
            url: "/create-payment-intent",
            method: "POST",
            contentType: "application/json",
            dataType: "json",
        });

        if (backendError || !clientSecret) {
            $("#card-errors").text(backendError || "Erreur lors de la création du PaymentIntent");
            return;
        }

        const { paymentIntent, error: stripeError } = await stripe.confirmCardPayment(clientSecret, {
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
                        country: cardCountry,
                    }
                }
            }
        });

        if (stripeError) {
            $("#card-errors").text(stripeError.message);
            return;
        }

        if (paymentIntent && paymentIntent.status === "succeeded") {
            window.location.href = "/commande-valide";
        } else {
            console.info("Paiement en attente ou échoué :", paymentIntent);
            $("#card-errors").text("Le paiement n'a pas pu être confirmé.");
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
