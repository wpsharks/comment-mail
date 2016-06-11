<?php
namespace WebSharks\CommentMail;

/*
 * @var Plugin   $plugin Plugin class.
 * @var Template $template Template class.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<script type="text/javascript">
    (function($) // Comment Mail™ SSO options.
    {
        'use strict'; // Strict standards.

        $(document).ready(
            function() // On DOM ready handler.
            {
                var $ssoOps = $('.login-sso-ops');

                $ssoOps.find('> a.lsso-link').on('click', function(e)
                {
                    e.preventDefault(), e.stopImmediatePropagation(),
                        winOpen($(this).attr('href'));
                });
                var winOpen = function(url, width, height, name)
                {
                    url = url ? String(url) : '';

                    width = width ? Number(width) : 0;
                    if(width <= 0 || isNaN(width))
                        width = screen.width - 200;
                    width = Math.min(1200, screen.width, width);

                    height = height ? Number(height) : 0;
                    if(height <= 0 || isNaN(height))
                        height = screen.height - 200;
                    height = Math.min(800, screen.height, height);

                    if(!(name = String(name))) name = 'winOpen';

                    var params = 'scrollbars=yes,resizable=yes,centerscreen=yes,modal=yes' +
                                 ',width=' + width + ',height=' + height + // Width, height, positions.
                                 ',top=' + ((screen.height - height) / 2) + ',left=' + ((screen.width - width) / 2) +
                                 ',screenY=' + ((screen.height - height) / 2) + ',screenX=' + ((screen.width - width) / 2);

                    var openWin; // Initialize.
                    if((openWin = open(url, name, params)))
                        openWin.focus();

                    return openWin;
                };
            });
    })(jQuery);
</script>
