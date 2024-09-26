$(document).ready(function() {
    $(".dropdown-item").on("click", function(e) {
        e.preventDefault();
        const input = $(this).find("input");
        input.prop("checked", true);
    });
});
