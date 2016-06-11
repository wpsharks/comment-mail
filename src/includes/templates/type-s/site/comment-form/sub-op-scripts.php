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
    (function($) // Comment Mail™ subscr. options.
    {
        'use strict'; // Strict standards.

        $(document).ready(
            function() // On DOM ready handler.
            {
                var $subOps = $('.comment-sub-ops'),
                    $subType, $subDeliver; // Initialize vars.

                if($subOps.data('auto') === 'position') $subOps.prevUntil('form')
                    .each(function(/* Auto-position subscription options. */)
                          {
                              var $this = $(this); // Cache this.
                              if($this.find(':input[type="submit"]').length)
                              {
                                  $subOps.remove(), $this.before($subOps);
                                  return false; // Break the each() loop.
                              }
                          });
                $subOps = $('.comment-sub-ops'),
                    $subType = $subOps.find('select.cso-sub-type'),
                    $subDeliver = $subOps.find('select.cso-sub-deliver');

                $subType.on('change', function()
                {
                    if($(this).val() === '')
                        $subDeliver.attr('disabled', 'disabled');
                    else $subDeliver.removeAttr('disabled');
                })
                    .trigger('change');
            });
    })(jQuery);
</script>
