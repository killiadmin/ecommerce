$(document).ready(function() {
    // Click handler for quantity buttons with event delegation
    $(".num-in").on("click", "span", function () {
        var $this = $(this);
        var $numBlock = $this.closest(".num-block");
        var $input = $numBlock.find(".in-num");
        var currentValue = parseInt($input.val(), 10);
        var newValue = currentValue;

        // Check if it's a "minus" or "plus" button
        if ($this.hasClass("minus")) {
            newValue = Math.max(1, currentValue - 1);
            $this.toggleClass("dis", newValue <= 1);
        } else if ($this.hasClass("plus")) {
            newValue = currentValue + 1;
            $numBlock.find(".minus").removeClass("dis");
        }

        // Quantity update
        $input.val(newValue);

        // Updated data-quantity in the "AddToBasket" button
        $numBlock.closest(".quantity-selector").find(".add-to-cart").attr("data-quantity", newValue);

        return false;
    });

    // Click handler for add to cart buttons with event delegation
    $(".quantity-selector").on("click", ".add-to-cart", function () {
        var $this = $(this);
        var url = $this.data("url");
        var quantity = $this.data("quantity");

        $.ajax({
            url: url,
            type: "POST",
            data: {
                quantity: quantity
            },
            success: function(response) {
                window.location.href = response.redirectUrl;
            },
            error: function() {
                alert("Erreur lors de l\'ajout au panier.");
            }
        });
    });
});
