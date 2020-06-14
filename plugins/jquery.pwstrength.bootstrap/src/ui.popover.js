/*
 * jQuery Password Strength plugin for Twitter Bootstrap
 *
 * Copyright (c) 2008-2013 Tane Piper
 * Copyright (c) 2013 Alejandro Blanco
 * Dual licensed under the MIT and GPL licenses.
 */

/* global ui */

(function() {
    'use strict';

    ui.initPopover = function(options, $el) {
        try {
            $el.popover('destroy');
        } catch (error) {
            // Bootstrap 4.2.X onwards
            $el.popover('dispose');
        }
        $el.popover({
            html: true,
            placement: options.ui.popoverPlacement,
            trigger: 'manual',
            content: ' '
        });
    };

    ui.updatePopover = function(options, $el, verdictText, remove) {
        var popover = $el.data('bs.popover'),
            html = '',
            hide = true;

        if (
            options.ui.showVerdicts &&
            !options.ui.showVerdictsInsideProgressBar &&
            verdictText.length > 0
        ) {
            html =
                '<h5><span class="password-verdict">' +
                verdictText +
                '</span></h5>';
            hide = false;
        }
        if (options.ui.showErrors) {
            if (options.instances.errors.length > 0) {
                hide = false;
            }
            html += options.ui.popoverError(options);
        }

        if (hide || remove) {
            $el.popover('hide');
            return;
        }

        if (options.ui.bootstrap2) {
            popover = $el.data('popover');
        }

        if (popover.$arrow && popover.$arrow.parents('body').length > 0) {
            $el.find('+ .popover .popover-content').html(html);
        } else {
            // It's hidden
            if (options.ui.bootstrap2 || options.ui.bootstrap3) {
                popover.options.content = html;
            } else {
                popover.config.content = html;
            }
            $el.popover('show');
        }
    };
})();
