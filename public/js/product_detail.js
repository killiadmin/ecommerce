$(document).ready(
    /**
     * Initializes a click event handler for elements with the class "add-to-cart"
     */
    function () {
        $(".add-to-cart").on("click", function (e) {
            e.preventDefault();

            const productId = $(this).data("product-id");

            $.ajax({
                url: "/mon-panier/" + productId + "/add",
                type: "POST",
                data: {
                    quantity: 1
                },
                success: function () {
                    window.location.href = "/mon-panier";
                },
                error: function (xhr, status, error) {
                    console.error(">>> " + error);
                },
            });
        });
    });
