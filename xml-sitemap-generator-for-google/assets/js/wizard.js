"use strict";
jQuery(document).ready(function($) {
    const stepMenus = $('[class^="wizard-step-menu"]');
    const formSteps = $('[class^="wizard-form-step-"]');
    const formSubmitBtn = $('.wizard-btn');
    const formBackBtn = $('.wizard-back-btn');

    let currentStep = 0;

    updateStep(currentStep);

    formSubmitBtn.on("click", function(event) {
        event.preventDefault();

        if (currentStep < formSteps.length - 1) {
            currentStep++;
            updateStep(currentStep);
        } else {
            $('form').submit();
        }
    });

    formBackBtn.on("click", function(event) {
        event.preventDefault();

        if (currentStep > 0) {
            currentStep--;
            updateStep(currentStep);
        }
    });

    function updateStep(stepIndex) {
        formSteps.removeClass('active');
        formSteps.eq(stepIndex).addClass('active');

        stepMenus.removeClass('active');
        stepMenus.eq(stepIndex).addClass('active');

        if (stepIndex === 0) {
            formBackBtn.removeClass('active');
        } else {
            formBackBtn.addClass('active');
        }

        if (stepIndex === formSteps.length - 1) {
            formSubmitBtn.text('Finish');
        } else {
            formSubmitBtn.text('Continue');
        }
    }

    // Toggle Depended Input Fields
    $('.wizard-form-checkbox').on('change', function() {
        const target = $(this).attr('id');
        $(`.wizard-input-wrapper[data-dependency="${target}"]`).toggleClass('active', this.checked);
    });
});
