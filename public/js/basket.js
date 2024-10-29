/**
 * Sets up a click event handler for deleting items.
 * @param {string} selector
 * @return {void}
 */
function setupDeleteItemHandler(selector) {
    $(selector).on("click", function (event) {
        event.preventDefault();

        const $this = $(this);
        const itemId = $this.data("item-id");
        const $row = $(`tr[data-item-id="${itemId}"]`);

        toggleDeleteButton($this, false);

        $.ajax({
            url: $this.attr("href"),
            type: "POST",
            dataType: "json",
            success: (response) => handleDeleteResponse(response, $this, $row),
            error: () => showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.")
        });
    });
}

/**
 * Handles the response for item deletion.
 * @param {object} response
 * @param {object} $button
 * @param {object} $row
 */
function handleDeleteResponse(response, $button, $row) {
    if (!response.success) {
        showAlertAndRestoreButton($button, "Une erreur est survenue lors de la suppression de l'article.");
        return;
    }

    handleItemRemoval($row);
    basketFetcher(response);
}

/**
 * Utility function to enable/disable delete button.
 * @param {object} $button
 * @param {boolean} enable
 */
function toggleDeleteButton($button, enable) {
    $button.toggleClass("delete-item", enable);
}

/**
 * Function to display error message and restore button.
 * @param {object} $button
 * @param {string} message
 */
function showAlertAndRestoreButton($button, message) {
    alert(message);
    toggleDeleteButton($button, true);
}

/**
 * Function to manage the deletion of an article.
 * @param {object} $row
 */
function handleItemRemoval($row) {
    const itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);
    $row.remove();
    updateBadgeQuantities(itemQuantity, "remove");
}

/**
 * Updates the badge quantities for both desktop and mobile views.
 * @param {number} itemQuantity
 * @param {string} action "remove" or "change"
 */
function updateBadgeQuantities(itemQuantity, action) {
    const badgeQuantityDesk = $("#badge-quantity-desk");
    const badgeQuantityMobile = $("#badge-quantity-mobile");

    const badges = [
        { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
        { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
    ];

    let flagEmpty = "";

    badges.forEach(function (badge) {
        let newCount;
        if (action === "remove") {
            newCount = badge.currentCount - itemQuantity;
        } else {
            newCount = itemQuantity;
        }
        badge.$element.text(Math.max(newCount, 0));
        flagEmpty = newCount;
    });

    if (flagEmpty === 0) {
        $("#myBasket").append($("<tr>").attr({ colspan: 4 }).text("Votre panier est vide "));
    }
}

/**
 * Fetches the basket data if the response indicates success.
 * @param {Object} response
 * @return {void}
 */
function basketFetcher(response) {
    if (response.success){
        $.ajax({
            url: "/mon-panier",
            type: "GET",
            success: (data) => basketQuantityUpdate(data),
            error: (error) => console.error("Erreur ", error)
        });
    }
}

/**
 * Updates basket quantities and prices in the DOM based on provided data.
 * @param {Object} data - The data object containing basket information.
 * @return {void}
 */
function basketQuantityUpdate(data) {
    $("#totalCount").text(data.totalCount);
    $("#totalQuantity").text(data.totalQuantity);
    $("#totalPriceHt").text(data.totalPrice + " €");
    $("#totalPriceTtc").text(data.totalPriceTtc + " €");

    $(".total-price-ht-discount").text(data.totalPriceWithDiscount + " €");
    $(".total-price-ttc-discount").text(data.totalPriceTtcWithDiscount + " €");

    let totalUpdatedQuantity = 0;

    data.basketItems.forEach(item => {
        let $itemRow = $(`tr[data-item-id="${item.id}"]`);

        if ($itemRow !== null) {
            const totalPriceHt = item.price * item.quantity;
            const totalPriceTtc = item.priceTva * item.quantity;

            $itemRow.find(".price").text(totalPriceHt + " €").attr("data-price", totalPriceHt);
            $itemRow.find(".tva").contents().first().replaceWith(totalPriceTtc + " €").attr("data-tva", totalPriceTtc);

            totalUpdatedQuantity += item.quantity;
        }
    });

    updateBadgeQuantities(totalUpdatedQuantity, "update");
}

/**
 * Updates the quantity of a specific item in the shopping cart.
 * @param {number|string} itemId
 * @param {number} newQuantity
 * @return {void}
 */
function updateQuantity(itemId, newQuantity) {
    $.ajax({
        url: `/mon-panier/update/${itemId}`,
        type: "PUT",
        contentType: "application/json",
        data: JSON.stringify({ quantity: newQuantity }),
        success: (response) => basketFetcher(response),
        error: (error) => console.error("Erreur lors de la mise à jour : ", error)
    });
}

/**
 * Attaches a debounced input event listener to the specified selector.
 * @param {string} selector
 * @param {number} delay
 * @param {function} callback
 * @return {void}
 */
function debounceInput(selector, delay, callback) {
    let debounceTimer;

    $(selector).on("input", function () {
        clearTimeout(debounceTimer);

        const itemId = $(this).data("item-id");
        const newQuantity = $(this).val();

        debounceTimer = setTimeout(() => {
            callback(itemId, newQuantity);
        }, delay);
    });
}

$(document).ready(function () {
    // Method for deleted an item in the basket
    setupDeleteItemHandler(".delete-item");

    // Method for updated an item in the basket
    debounceInput(".quantity-input", 400, updateQuantity);
});
