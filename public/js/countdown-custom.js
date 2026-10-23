jQuery(document).ready(function() {
    $(function () {
        var $countdown = $('#defaultCountdown');
        if ($countdown.length === 0) {
            return;
        }

        var untilAttr = $countdown.attr('data-until');
        var untilDate = untilAttr ? new Date(untilAttr) : new Date(2026, 10 - 1, 1, 8);

        if (isNaN(untilDate.getTime())) {
            untilDate = new Date(2026, 10 - 1, 1, 8);
        }

        $countdown.countdown({ until: untilDate });
    });
});
