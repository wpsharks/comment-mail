(function($)
{
	'use strict';

	var plugin = {},
		$window = $(window),
		$document = $(document);

	plugin.scriptsLoading = {};

	plugin.loadScript = function(url)
	{
		url = String(url);

		var ajaxOptions = {
			url     : url,
			cache   : true,
			dataType: 'script',
			success : function()
			{
				delete plugin.scriptsLoading[url];
			}
		};
		plugin.scriptsLoading[url] = -1,
			$.ajax(ajaxOptions);
	};
	plugin.scriptsReady = function()
	{
		return $.isEmptyObject(plugin.scriptsLoading);
	};
	plugin.onReady = function() // jQuery DOM ready event handler.
	{
		if(!plugin.scriptsReady()) return setTimeout(plugin.onReady, 100);

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

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panels-close').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').removeClass('open')
				.next('.pmp-panel-body').removeClass('open');
		});

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panel-heading').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$(this).toggleClass('open') // Toggle this panel now.
				.next('.pmp-panel-body').toggleClass('open');
		});

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

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
			$select : $menuPageForm.find('> form tr.pmp-sub-form-post-id select'),
			$input  : $menuPageForm.find('> form tr.pmp-sub-form-post-id input'),
			progress: '<img src="' + vars.pluginUrl + '/client-s/images/tiny-progress-bar.gif" />'
		};
		if(subFormPostIdProps.$select.length) // Have select options?
			subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$select.val());
		else subFormPostIdProps.lastId = $.trim(subFormPostIdProps.$input.val());

		subFormPostIdProps.handler = function()
		{
			var $this = $(this), commentIdProps = {},
				requestVars = {}; // Initialize these vars.

			subFormPostIdProps.newId = $.trim($this.val());
			if(subFormPostIdProps.newId === subFormPostIdProps.lastId)
				return; // Nothing to do; i.e. no change, new post ID is the same.
			subFormPostIdProps.lastId = subFormPostIdProps.newId; // Update last ID.

			commentIdProps.$lastRow = $menuPageForm.find('> form tr.pmp-sub-form-comment-id'),
				commentIdProps.$lastChosenContainer = commentIdProps.$lastRow.find('.chosen-container'),
				commentIdProps.$lastInput = commentIdProps.$lastRow.find(':input');

			if(!commentIdProps.$lastRow.length || !commentIdProps.$lastInput.length)
				return; // Nothing we can do here; expecting a comment ID row.

			commentIdProps.$lastChosenContainer.remove(), // Loading indicator.
				commentIdProps.$lastInput.replaceWith($(subFormPostIdProps.progress));

			requestVars[namespace] = {sub_form_comment_id_row_via_ajax: {post_id: subFormPostIdProps.newId}},
				$.get(vars.ajaxEndpoint, requestVars, function(newCommentIdRowMarkup)
				{
					commentIdProps.$newRow = $(newCommentIdRowMarkup),
						commentIdProps.$lastRow.replaceWith(commentIdProps.$newRow),
						commentIdProps.$newRow.find('select').chosen(chosenOps);
				});
		};

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		var subFormUserIdProps = { // Initialize.
			$select  : $menuPageForm.find('> form tr.pmp-sub-form-user-id select'),
			$input   : $menuPageForm.find('> form tr.pmp-sub-form-user-id input'),
			$progress: $('<img src="' + vars.pluginUrl + '/client-s/images/tiny-progress-bar.gif" />')
		};
		if(subFormUserIdProps.$select.length) // Have select options?
			subFormUserIdProps.lastId = $.trim(subFormUserIdProps.$select.val());
		else subFormUserIdProps.lastId = $.trim(subFormUserIdProps.$input.val());

		subFormUserIdProps.handler = function()
		{
			var $this = $(this), $emailTh, $email, $fname, $lname, $ip,
				requestVars = {}; // Initialize these vars.

			subFormUserIdProps.newId = $.trim($this.val());
			if(subFormUserIdProps.newId === subFormUserIdProps.lastId)
				return; // Nothing to do; i.e. no change, new user ID is the same.
			subFormUserIdProps.lastId = subFormUserIdProps.newId; // Update last ID.

			$emailTh = $menuPageForm.find('> form tr.pmp-sub-form-email th'),
				$email = $menuPageForm.find('> form tr.pmp-sub-form-email input'),
				$fname = $menuPageForm.find('> form tr.pmp-sub-form-fname input'),
				$lname = $menuPageForm.find('> form tr.pmp-sub-form-lname input'),
				$ip = $menuPageForm.find('> form tr.pmp-sub-form-insertion-ip input');

			if(!$emailTh.length || ($email.length + $fname.length + $lname.length) < 1)
				return; // Not possible; expecting a table header; and at least one of these.

			subFormUserIdProps.$progress.remove(), $emailTh.append(subFormUserIdProps.$progress);

			requestVars[namespace] = {sub_form_user_id_info_via_ajax: {user_id: subFormUserIdProps.newId}},
				$.get(vars.ajaxEndpoint, requestVars, function(newUserInfo)
				{
					$email.val(newUserInfo.email), // Prefill these fields.
						$fname.val(newUserInfo.fname), $lname.val(newUserInfo.lname),
						$ip.val(newUserInfo.ip); // Normally this will be empty.

					subFormUserIdProps.$progress.remove();
				});
		};

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		subFormPostIdProps.$select.on('change', subFormPostIdProps.handler).chosen(chosenOps),
			subFormPostIdProps.$input.on('blur', subFormPostIdProps.handler);

		$menuPageForm.find('> form tr.pmp-sub-form-comment-id select').chosen(chosenOps);

		subFormUserIdProps.$select.on('change', subFormUserIdProps.handler).chosen(chosenOps),
			subFormUserIdProps.$input.on('blur', subFormUserIdProps.handler);

		$menuPageForm.find('> form tr.pmp-sub-form-status select').on('change', function()
		{
			var $this = $(this), status = $.trim($this.val()),
				$checkboxContainer = $this.siblings('.checkbox'),
				$checkbox = $checkboxContainer.find('input');

			if(status === 'unconfirmed') // Needs confirmation?
				$checkboxContainer.show(); // Display checkbox option.
			else $checkbox.prop('checked', false), $checkboxContainer.hide();

		}).trigger('change').chosen(chosenOps); // Fire immediately.

		$menuPageForm.find('> form tr.pmp-sub-form-deliver select').chosen(chosenOps);

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

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
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
	};
	plugin.loadScript('//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js');

	$document.ready(plugin.onReady); // DOM ready handler.
})(jQuery);