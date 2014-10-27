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
			vars = window[namespace + '_vars'], i18n = window[namespace + '_i18n'],
			chosenOps = {search_contains: true, disable_search_threshold: 10, allow_single_deselect: true};

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

		var subFormPostIdProps = { // Initialize.
			$select: $menuPageForm.find('> form tr.pmp-sub-form-post-id select'),
			$input : $menuPageForm.find('> form tr.pmp-sub-form-post-id input')
		};
		if(subFormPostIdProps.$select.length) // Have select options?
			subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$select.val());
		else subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$input.val());

		subFormPostIdProps.handler = function()
		{
			var $this = $(this), // Initialize.
				commentIdProps = {}, requestVars = {};
			subFormPostIdProps.newId = $.trim($this.val());

			if(subFormPostIdProps.newId === subFormPostIdProps.lastId)
				return; // Nothing to do; i.e. no change, new post ID is the same.
			subFormPostIdProps.lastId = subFormPostIdProps.newId; // Update last ID.

			commentIdProps.$lastRow = $menuPageForm.find('> form tr.pmp-sub-form-comment-id'),
				commentIdProps.$lastChosenContainer = commentIdProps.$lastRow.find('.chosen-container'),
				commentIdProps.$lastInput = commentIdProps.$lastRow.find(':input');

			if(!commentIdProps.$lastRow.length || !commentIdProps.$lastInput.length)
				return; // Nothing we can do here; expecting a comment ID row.

			commentIdProps.$lastChosenContainer.remove(), // Loading; i.e. prepare for new comment ID row.
				commentIdProps.$lastInput.replaceWith('<img src="' + vars.plugin_url + '/client-s/images/tiny-progress-bar.gif" />');

			requestVars[namespace] = {comment_id_row_via_ajax: {post_id: subFormPostIdProps.newId}},
				$.get(vars.ajax_endpoint, requestVars, function(newCommentIdRowMarkup)
				{
					commentIdProps.$newRow = $(newCommentIdRowMarkup),
						commentIdProps.$lastRow.replaceWith(commentIdProps.$newRow),
						commentIdProps.$newRow.find('select').chosen(chosenOps);
				});
		};
		subFormPostIdProps.$select.on('change', subFormPostIdProps.handler).chosen(chosenOps),
			subFormPostIdProps.$input.on('blur', subFormPostIdProps.handler);

		$menuPageForm.find('> form tr.pmp-sub-form-comment-id select').chosen(chosenOps);
		$menuPageForm.find('> form tr.pmp-sub-form-user-id select').chosen(chosenOps);

		$menuPageForm.find('> form tr.pmp-sub-form-status select').on('change', function()
		{
			var $this = $(this), status = $.trim($this.val()),
				$checkboxContainer = $this.siblings('.checkbox'),
				$checkbox = $checkboxContainer.find('input');

			if(status === 'unconfirmed')
				$checkboxContainer.show(); // Display checkbox option.
			else $checkbox.prop('checked', false), $checkboxContainer.hide();

		}).trigger('change').chosen(chosenOps); // Fire immediately.

		$menuPageForm.find('> form tr.pmp-sub-form-deliver select').chosen(chosenOps);

		$menuPageForm.find('> form').on('submit', function(e)
		{
			var $this = $(this),
				errors = '', // Initialize.
				missingRequiredFields = [];

			$this.find('.form-required :input[required]')
				.each(function(/* Missing required fields? */)
				      {
					      var $this = $(this),
						      val = $.trim($this.val());

					      if(typeof val === 'undefined' || val === '0' || val === '')
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