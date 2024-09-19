$(document).ready(function () {
    $(".delete-item").on("click", function (event) {
        event.preventDefault();

        var $this = $(this);
        var itemId = $this.data("item-id");
        var $row = $('tr[data-item-id="' + itemId + '"]');

        toggleDeleteButton($this, false);

        // Sending Ajax request
        $.ajax({
            url: $this.attr("href"),
            type: "POST",
            dataType: "json",
            success: function (response) {
                if (!response.success) {
                    showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
                    return;
                }

                // Update display after deletion
                handleItemRemoval($row, $this);
            },
            error: function () {
                showAlertAndRestoreButton($this, "Une erreur est survenue lors de la suppression de l'article.");
            }
        });
    });

    /**
     * Utility function to enable/disable delete button
     * @param $button
     * @param enable
     */
    function toggleDeleteButton($button, enable) {
        if (enable) {
            $button.addClass("delete-item");
        } else {
            $button.removeClass("delete-item");
        }
    }

    /**
     * Function to display error message and restore button
     * @param $button
     * @param message
     */
    function showAlertAndRestoreButton($button, message) {
        alert(message);
        toggleDeleteButton($button, true);
    }

    /**
     * Function to manage the deletion of an article
     * @param $row
     */
    function handleItemRemoval($row) {
        var itemQuantity = parseInt($row.find(".quantity[data-quantity]").data("quantity"), 10);
        $row.remove();
        updateBadgeQuantities(itemQuantity);
    }

    /**
     * Function to update badges
     * @param itemQuantity
     */
    function updateBadgeQuantities(itemQuantity) {
        var badgeQuantityDesk = $("#badge-quantity-desk");
        var badgeQuantityMobile = $("#badge-quantity-mobile");

        var badges = [
            { $element: badgeQuantityDesk, currentCount: parseInt(badgeQuantityDesk.text(), 10) || 0 },
            { $element: badgeQuantityMobile, currentCount: parseInt(badgeQuantityMobile.text(), 10) || 0 }
        ];

        var flagEmpty = ""

        badges.forEach(function (badge) {
            var newCount = badge.currentCount - itemQuantity;
            badge.$element.text(newCount >= 0 ? newCount : 0);
            flagEmpty = newCount;
        });

        if (flagEmpty === 0) {
            $(".table-custom tbody").append($("<tr>").attr({colspan: 4}).text("Votre panier est vide "));
        }
    }
});
