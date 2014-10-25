(function($)
{
	'use strict'; // Standards.

	var plugin = {},
		$window = $(window),
		$document = $(document);

	plugin.onReady = function() // jQuery DOM ready event handler.
	{
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific selectors needed by routines below.
		 ------------------------------------------------------------------------------------------------------------ */

		var namespace = 'comment_mail',
			namespaceSlug = 'comment-mail',
			$menuPage = $('.' + namespaceSlug + '-menu-page'),
			$menuPageArea = $('.' + namespaceSlug + '-menu-page-area'),
			$menuPageTable = $('.' + namespaceSlug + '-menu-page-table'),
			$menuPageForm = $('.' + namespaceSlug + '-menu-page-form'),
			vars = window[namespace + '_vars'], i18n = window[namespace + '_i18n'];

		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for any menu page area of the dashboard.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPageArea.find('[data-pmp-action]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), data = $this.data();
			if(typeof data.pmpConfirmation !== 'string' || confirm(data.pmpConfirmation))
				location.href = data.pmpAction;
		});

		/* ------------------------------------------------------------------------------------------------------------
		 JS for an actual/standard plugin menu page; e.g. options.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPage.find('.pmp-panels-open').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').addClass('open')
				.next('.pmp-panel-body').addClass('open');
		});
		$menuPage.find('.pmp-panels-close').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').removeClass('open')
				.next('.pmp-panel-body').removeClass('open');
		});
		$menuPage.find('.pmp-panel-heading').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$(this).toggleClass('open') // Toggle this panel now.
				.next('.pmp-panel-body').toggleClass('open');
		});
		$menuPage.find('select[name$="_enable\\]"], select[name$="_enable_flavor\\]"]').not('.no-if-enabled').on('change', function()
		{
			var $this = $(this), thisName = $this[0].name, thisValue = $this.val(),
				$thisPanel = $this.closest('.pmp-panel');

			if((thisName.indexOf('_enable]') !== -1 && (thisValue === '' || thisValue === '1'))
			   || (thisName.indexOf('_flavor]') !== -1 && thisValue !== '0')) // Enabled?
				$thisPanel.find('.pmp-panel-if-enabled').css('opacity', 1).find(':input').removeAttr('readonly');
			else $thisPanel.find('.pmp-panel-if-enabled').css('opacity', 0.4).find(':input').attr('readonly', 'readonly');
		})
			.trigger('change'); // Initialize.

		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for menu page tables that follow a WP standard, but need a few tweaks.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPageTable.find('> form').on('submit', function()
		{
			var $this = $(this), // Initialize vars.
				$bulkTop = $this.find('#bulk-action-selector-top'),
				$bulkBottom = $this.find('#bulk-action-selector-bottom'),
				bulkTopVal = $bulkTop.val(), bulkBottomVal = $bulkBottom.val();

			if(bulkTopVal === 'reconfirm' || bulkBottomVal === 'reconfirm')
				return confirm(i18n.bulk_reconfirm_confirmation);

			else if(bulkTopVal === 'delete' || bulkBottomVal === 'delete')
				return confirm(i18n.bulk_delete_confirmation);

			return true; // Default behavior.
		});

		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for menu page forms that follow a WP standard, but need a few tweaks.
		 ------------------------------------------------------------------------------------------------------------ */

		var subFormPostIdChangeHandler = function()
		{
			var $this = $(this), postId = $this.val(), requestVars = {},
				$progressIcon = $('<img src="' + vars.plugin_url + '/client-s/images/tiny-progress-bar.gif" />'),
				$commentIdRow = $menuPageForm.find('> form tr.pmp-sub-form-comment-id'),
				$commentIdInput = $commentIdRow.find(':input');

			if(!$commentIdRow.length || !$commentIdInput.length)
				return; // Nothing we can do here.

			$commentIdInput.replaceWith($progressIcon),
				requestVars[namespace] = {comment_id_row_via_ajax: {post_id: postId}},
				$.get(vars.ajax_endpoint, requestVars, function(newCommentIdRow)
				{
					$commentIdRow.replaceWith(newCommentIdRow);
				});
		}; // This function is needed by two different events.
		$menuPageForm.find('> form tr.pmp-sub-form-post-id select').on('change', subFormPostIdChangeHandler),
			$menuPageForm.find('> form tr.pmp-sub-form-post-id input').on('blur', subFormPostIdChangeHandler);

		$menuPageForm.find('> form tr.pmp-sub-form-status select').on('change', function()
		{
			var $this = $(this), val = $this.val(),
				$checkbox = $this.siblings('.checkbox').first();

			if(!$checkbox.length)
				return; // Not possible.

			if($checkbox[0].checked || val === 'unconfirmed')
				$checkbox.show(); // Display checkbox option.
			else $checkbox.hide(), $checkbox[0].checked = false;

		}).trigger('change'); // Fire immediately.

		$menuPageForm.find('> form').on('submit', function(e)
		{
			var $this = $(this),
				errors = '', // Initialize.
				missingRequiredFields = [];

			$this.find('.form-required :input[aria-required]')
				.each(function(/* Missing required fields? */)
				      {
					      var $this = $(this),
						      val = $.trim($this.val());

					      if(val === '0' || val === '')
						      missingRequiredFields.push(this);
				      });
			$.each(missingRequiredFields, function()
			{
				errors += $.trim($this.find('label[for="' + this.id + '"]').text().replace(/\s+/g, ' ')) + '\n';
			});
			if((errors = $.trim(errors)).length)
			{
				e.preventDefault(),
					e.stopImmediatePropagation(),
					alert(errors);
				return false;
			}
		});
	};
	$document.ready(plugin.onReady); // On DOM ready.
})(jQuery);