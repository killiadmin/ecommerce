/**
 * Open the navigation menu by adjusting CSS properties.
 *
 * This method sets the width of the element with the ID `categoryMenu` to 320px
 * and moves the element with the ID `categoryToggleBtn` 300px to the right.
 * Additionally, it adds the class `menu-open` to the `categoryToggleBtn` element.
 *
 * @return {void}
 */
function openNav()
{
    $("#categoryMenu").css("width", "320px");
    $("#categoryToggleBtn").css("left", "300px");
    $("#categoryToggleBtn").addClass("menu-open");
}

/**
 * Closes the navigation menu by collapsing its width and adjusting the position of the toggle button.
 *
 * @return {void} This function does not return a value.
 */
function closeNav()
{
    $("#categoryMenu").css("width", "0");
    $("#categoryToggleBtn").css("left", "20px");
    $("#categoryToggleBtn").removeClass("menu-open");
}

/**
 * Toggles the navigation menu between open and closed states.
 *
 * The function checks the current width of the navigation menu
 * (identified by the ID "categoryMenu"). If the width is 320px,
 * it will call the `closeNav` function to close the menu.
 * Otherwise, it calls the `openNav` function to open the menu.
 *
 * @return {void}
 */
function toggleNav()
{
    $("#categoryMenu").css("width") === "320px" ? closeNav() : openNav();
}

/**
 * Filters the products displayed on the page based on the specified category.
 *
 * @param {string} category - The category to filter products by. If the category is 'All', all products will be displayed.
 * @return {void}
 */
function filterProducts(category)
{
    $("#productList .product-item").each(function() {
        const product = $(this);
        if (product.attr("data-category") === category || category === 'All') {
            product.css("display", "block");
        } else {
            product.css("display", "none");
        }
    });

    closeNav();
}

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
