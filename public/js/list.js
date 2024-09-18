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
    $(".quantity-selector").on("click", ".add-to-cart", function (e) {
        e.preventDefault();

        var url = $(this).data("url");
        var quantity = $(this).siblings(".num-block").find(".in-num").val();

        if (!url) {
            return;
        }

        $.ajax({
            url: url,
            type: "POST",
            data: {
                quantity: quantity
            },
            success: function(response) {
                $("#successModal .modal-body").text(response.message);
                $("#successModal").modal("show");

                $("#badge-quantity-desk").text(response.cartItemsCount);
                $("#badge-quantity-mobile").text(response.cartItemsCount);
            },
            error: function() {
                window.location.href = "/connexion"
            }
        });
    });
});
