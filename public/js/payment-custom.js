$(document).ready(() => {
    $("#order-create").on("click", function(event) {
        event.preventDefault();

        const createOrderUrl = $(this).data("create-order-url");

        $.ajax({
            url: createOrderUrl,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({}),
            success: function() {
                alert("Commande créée avec succès !");
            },
            error: function() {
                alert("Erreur lors de la création de la commande.");
            }
        });
    });
});
