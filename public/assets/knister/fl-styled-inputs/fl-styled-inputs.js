jQuery(function ($) {
    let styledRadios = [];

    $(".fl-styled-input").each(function () {
        let htmlID = $(this).attr("id");

        //if ID is not set in DOM, we'll do that
        if (htmlID === undefined || htmlID === "") {
            htmlID = "fl-styled-radio-" + styledRadios.length;
            $(this).attr("id", htmlID);
        }

        let $newLabel = $("<label class='fl-styled-input-label' for='"+htmlID+"'></label>");
        $(this).hide();
        $(this).after($newLabel);
    });
}(jQuery));