"use strict";

jQuery(document).ready(function ($) {
    $( document ).on( 'click', '.notice.is-dismissible .notice-dismiss, .sgg-notice', function(e) {
        const $trigger = $(this);

        if ($trigger.attr('href') === '#') {
            e.preventDefault();
        }

        let $notice = $trigger.closest('.notice');
        let noticeId = $notice.attr('data-notice');

        if(!noticeId) {
            $notice = $trigger.closest('.grim-notice-data');
            noticeId = $notice.attr('data-notice');
        }

        if (!['sgg_rate', 'sgg_buy_pro'].includes(noticeId)) {
            return;
        }

        const permanent = parseInt($trigger.attr('data-permanent'), 10) === 1;

        $.ajax({
            url: sggNotice.ajax_url,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'sgg_disable_notice',
                nonce: sggNotice.nonce,
                notice: noticeId,
                permanent: permanent ? 1 : 0
            }
        });

        $notice.fadeOut();
    });
});