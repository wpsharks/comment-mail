(function($)
{
	'use strict';

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
			$menuPageStats = $('.' + namespaceSlug + '-menu-page-stats'),

			vars = window[namespace + '_vars'], i18n = window[namespace + '_i18n'],

			chosenOps = {
				search_contains         : true,
				disable_search_threshold: 10,
				allow_single_deselect   : true
			},

			codeMirrors = [], cmOptions = {
				lineNumbers  : false,
				matchBrackets: true,
				theme        : 'ambiance',
				tabSize      : 3, indentWithTabs: true,
				extraKeys    : {
					'F11': function(cm)
					{
						if(cm.getOption('fullScreen'))
							cm.setOption('fullScreen', false),
								$('#adminmenuwrap, #wpadminbar').show();

						else cm.setOption('fullScreen', true),
							$('#adminmenuwrap, #wpadminbar').hide();
					}
				}
			};
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for any menu page area of the dashboard.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPageArea.find('.pmp-tabs > a').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this),
				$tabs = $this.parent().find('> a'),
				$tabPanesDiv = $this.parent().next('.pmp-tab-panes'),
				$tabPanes = $tabPanesDiv.children(), // All tab panes.
				$targetTabPane = $tabPanesDiv.find('> ' + $this.data('target'));

			$tabs.add($tabPanes).removeClass('pmp-active'), $tabPanes.hide(),
				$this.add($targetTabPane).addClass('pmp-active'), $targetTabPane.show();
		});
		$menuPageArea.find('[data-pmp-action]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), data = $this.data();
			if(typeof data.pmpConfirmation !== 'string' || confirm(data.pmpConfirmation))
				location.href = data.pmpAction;
		});
		$menuPageArea.find('[data-toggle~="date-time-picker"]')
			.datetimepicker({
				                lang          : 'en',
				                lazyInit      : true,
				                validateOnBlur: false,
				                format        : 'M j, Y H:i',
				                i18n          : i18n.dateTimePickerI18n
			                });
		$menuPageArea.find('[data-toggle~="select-all"]').on('click', function()
		{
			$(this).select(); // jQuery makes this easy for us.
		});
		$menuPageArea.find('[data-toggle~="alert"]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), alertMarkup = $this.data('alert'),
				$modalDialogOverlay = $('<div class="pmp-modal-dialog-overlay"></div>'),
				$modalDialog = $('<div class="pmp-modal-dialog">' +
				                 '   <a class="pmp-modal-dialog-close"></a>' +
				                 '   ' + alertMarkup +
				                 '</div>');
			$this.after($modalDialogOverlay).after($modalDialog),
				$modalDialogOverlay.add($modalDialog.find('> .pmp-modal-dialog-close')).on('click', function(e)
				{
					e.preventDefault(), e.stopImmediatePropagation(),
						$menuPageArea.find('.pmp-modal-dialog').remove(),
						$menuPageArea.find('.pmp-modal-dialog-overlay').remove();
				});
		});
		/* ------------------------------------------------------------------------------------------------------------
		 JS for an actual/standard plugin menu page; e.g. options.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPage.find('[data-cm-mode]')
			.each(function() // CodeMirrors.
			      {
				      var $this = $(this),
					      cmMode = $this.data('cmMode'),
					      cmHeight = $this.data('cmHeight'),
					      $textarea = $this.find('textarea');

				      if($textarea.length !== 1) return; // Invalid markup.

				      window.CodeMirror = CodeMirror || {fromTextArea: function(){}};

				      $this.addClass('cm'), // See `menu-pages.css` to customize styles.
					      codeMirrors.push(CodeMirror.fromTextArea($textarea[0], $.extend({}, cmOptions, {mode: cmMode}))),
					      codeMirrors[codeMirrors.length - 1].setSize(null, cmHeight);
			      });
		var refreshCodeMirrors = function(/* Refresh CodeMirrors. */)
		{
			$.each(codeMirrors, function(i, codeMirror){ codeMirror.refresh(); });
		};
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('[data-toggle~="other"]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$($(this).data('other')).toggle(), // Toggle another.
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panels-open').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').addClass('open')
				.next('.pmp-panel-body').addClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panels-close').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').removeClass('open')
				.next('.pmp-panel-body').removeClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panel-heading').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$(this).toggleClass('open') // Toggle this panel now.
				.next('.pmp-panel-body').toggleClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-if-change').on('change', function()
		{
			var $this = $(this),
				thisValue = $.trim($this.val()),
				$thisPanel = $this.closest('.pmp-panel'),
				$thisNest = $this.closest('.pmp-if-nest'),
				$thisContainer = $thisPanel; // Default container.
			if($thisNest.length) $thisContainer = $thisNest;
			var matchValue = $this.hasClass('pmp-if-value-match');

			var enabled = thisValue !== '' && thisValue !== '0',
				disabled = !enabled; // The opposite.

			var ifEnabled = '.pmp-if-enabled',
				ifEnabledShow = '.pmp-if-enabled-show',

				ifDisabled = '.pmp-if-disabled',
				ifDisabledShow = '.pmp-if-disabled-show';

			var withinANest = function() // Prevents nest conflicts.
			{
				if($thisNest.length) return false; // Inside a nest already.
				// In this case, the set of matches has already been filtered above;
				// i.e. by restricting the context itself to the closest `.pmp-if-nest`.

				// If we are NOT inside a nest, do not include anything that is.
				return $(this).hasClass('pmp-in-if-nest'); // Class for optimization.
			};
			var valueMatches = function() // Matches current value; if applicable.
			{
				if(!matchValue) return true; // Not matching current value.
				// In this case we want to include everything; i.e. do not exclude.

				// Otherwise, we only include this if it has a matching value.
				return $(this).hasClass('pmp-if-value-' + thisValue);
			};
			if(enabled) // If enabled; show and enable all input fields.
				$thisContainer.find(ifEnabled + ',' + ifEnabledShow).not(withinANest).filter(valueMatches)
					.show().css('opacity', 1).find(':input').removeAttr('disabled');

			else // We use opacity to conceal; and hide if applicable.
			{
				$thisContainer.find(ifEnabled + ',' + ifEnabledShow).not(withinANest)
					.css('opacity', 0.2).find(':input').attr('disabled', 'disabled'),
					$thisContainer.find(ifEnabledShow).not(withinANest).hide();
			}
			if(disabled) // If disabled; show and enable all input fields.
				$thisContainer.find(ifDisabled + ',' + ifDisabledShow).not(withinANest)
					.show().css('opacity', 1).find(':input').removeAttr('disabled');

			else // We use opacity to conceal; and hide if applicable.
			{
				$thisContainer.find(ifDisabled + ',' + ifDisabledShow).not(withinANest)
					.css('opacity', 0.2).find(':input').attr('disabled', 'disabled'),
					$thisContainer.find(ifDisabledShow).not(withinANest).hide();
			}
			refreshCodeMirrors(); // Refresh CodeMirrors also.
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
				return confirm(i18n.bulkReconfirmConfirmation);

			else if(bulkTopVal === 'delete' || bulkBottomVal === 'delete')
				return confirm(i18n.bulkDeleteConfirmation);

			return true; // Default behavior.
		});
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for menu page forms that follow a WP standard, but need a few tweaks.
		 ------------------------------------------------------------------------------------------------------------ */

		var subFormPostIdProps = { // Initialize.
			$select : $menuPageForm.find('> form tr.pmp-sub-form-post-id select'),
			$input  : $menuPageForm.find('> form tr.pmp-sub-form-post-id input'),
			progress: // Loading animation; just a tiny progress bar to help convey loading sequence.
			'<img src="' + vars.pluginUrl + '/client-s/images/tiny-progress-bar.gif" class="pmp-progress" />'
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

			commentIdProps.$lastChosenContainer.remove(), // New progress bar.
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
			$progress: // Loading animation; just a tiny progress bar to help convey loading sequence.
				$('<img src="' + vars.pluginUrl + '/client-s/images/tiny-progress-bar.gif" class="pmp-progress" />')
		};
		if(subFormUserIdProps.$select.length) // Have select options?
			subFormUserIdProps.lastId = $.trim(subFormUserIdProps.$select.val());
		else subFormUserIdProps.lastId = $.trim(subFormUserIdProps.$input.val());

		subFormUserIdProps.handler = function()
		{
			var $this = $(this), $emailTh,
				$email, $fname, $lname,
				$ip, $region, $country,
				requestVars = {};

			subFormUserIdProps.newId = $.trim($this.val());
			if(subFormUserIdProps.newId === subFormUserIdProps.lastId)
				return; // Nothing to do; i.e. no change, new user ID is the same.
			subFormUserIdProps.lastId = subFormUserIdProps.newId; // Update last ID.

			$emailTh = $menuPageForm.find('> form tr.pmp-sub-form-email th'),

				$email = $menuPageForm.find('> form tr.pmp-sub-form-email input'),
				$fname = $menuPageForm.find('> form tr.pmp-sub-form-fname input'),
				$lname = $menuPageForm.find('> form tr.pmp-sub-form-lname input'),

				$ip = $menuPageForm.find('> form tr.pmp-sub-form-insertion-ip input'),
				$region = $menuPageForm.find('> form tr.pmp-sub-form-insertion-region input'),
				$country = $menuPageForm.find('> form tr.pmp-sub-form-insertion-country input');

			if(!$emailTh.length || ($email.length + $fname.length + $lname.length) < 1)
				return; // Not possible; expecting a table header; and at least one of these.

			subFormUserIdProps.$progress.remove(), // Ditch old progress bar if exists.
				$emailTh.append(subFormUserIdProps.$progress); // New progress bar.

			requestVars[namespace] = {sub_form_user_id_info_via_ajax: {user_id: subFormUserIdProps.newId}},
				$.get(vars.ajaxEndpoint, requestVars, function(newUserInfo)
				{
					$email.val(newUserInfo.email), $fname.val(newUserInfo.fname), $lname.val(newUserInfo.lname),
						$ip.val(newUserInfo.ip), $region.val(newUserInfo.region), $country.val(newUserInfo.country);

					subFormUserIdProps.$progress.remove(); // Complete.
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
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS that extends ChartJS by enhancing the existing bar chart implementation.
		 ------------------------------------------------------------------------------------------------------------ */

		Chart.types.Bar.extend // Extends Bar chart class.
		({
			 name      : 'BetterBar',
			 initialize: function(data)
			 {
				 Chart.types.Bar.prototype.initialize.apply(this, arguments);

				 $.each(this.datasets, function(i, dataset)
				 {
					 $.each(dataset.bars, function(j, bar)
					 {
						 if(data.datasets[i].percent instanceof Array)
							 bar.percent = data.datasets[i].percent[j];
					 });
				 });
			 }
		 }), Chart.BetterBar = Chart.BetterBar || function(){};

		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for menu page stats that follow a WP standard, but need a few tweaks.
		 ------------------------------------------------------------------------------------------------------------ */

		if($menuPageStats.length) // See: `postboxes.js` in WP core.
		{
			postboxes.save_state = postboxes.save_order = function(){};
			postboxes.add_postbox_toggles(window.pagenow);
		}
		var statsViewProps = {
			$selects: $menuPageStats.find('.pmp-stats-view select'),
			$buttons: $menuPageStats.find('.pmp-stats-view button'),
			progress: // Loading animation; just a tiny progress bar to help convey loading sequence.
			'<img src="' + vars.pluginUrl + '/client-s/images/tiny-progress-bar.gif" class="pmp-progress" />',
			chartOps: {responsive: true}, geoChartOps: {displayMode: 'regions'}
		};
		statsViewProps.handler = function()
		{
			var $this = $(this),
				$form = $this.closest('form'),
				$statsView = $this.closest('.pmp-stats-view'),

				$errors = $statsView.find('.pmp-note.pmp-error'),
				$progress = $statsView.find('.pmp-progress'),
				$canvas = $statsView.find('.pmp-canvas'),

				view = $statsView.data('view'), // View and type.
				viewType = $statsView.find('select[name$="\\[type\\]"]').val(),

				prevChart = $statsView.data('chart');

			if(prevChart && typeof prevChart.destroy === 'function')
				prevChart.destroy(); // Ditch previous.

			if(prevChart && typeof prevChart.clearChart === 'function')
				prevChart.clearChart(); // Ditch previous.

			$statsView.data('chart', null); // Ditch previous.
			$errors.remove(), $canvas.remove(); // Ditch previous.

			$progress.remove(), // Ditch old progress bar; add new.
				$statsView.append($progress = $(statsViewProps.progress));

			if(/(?:^|_)geo(?:_|$)/i.test(viewType)) // Geo visualization map?
			{
				$.get(vars.ajaxEndpoint, $form.serialize(), function(chartData)
				{
					if(!chartData) return; // Not possible.

					if(typeof chartData.errors === 'string')
					{
						$statsView.append($(chartData.errors)), // Append errors.
							$progress.remove(); // Complete; i.e. remove progress bar.

						return; // All done here.
					}
					$canvas = $('<div class="pmp-canvas"></div>'),
						$statsView.append($canvas); // Add canvas.

					var chartTable = new google.visualization.DataTable(),
						chartTableCols = chartData.data.shift(), // Shift first row off.
						chartOps = $.extend({}, statsViewProps.geoChartOps, chartData.options),
						chart = new google.visualization.GeoChart($canvas[0]);

					chartTable.addColumn('string', 'region', chartTableCols[0]);
					chartTable.addColumn('number', 'value', chartTableCols[1]);
					chartTable.addColumn({type: 'string', role: 'tooltip'});

					chartTable.addRows(chartData.data); // Remaining rows.

					chart.draw(chartTable, chartOps), // Draw geo chart/map.
						$statsView.data('chart', chart), // Save chart reference.
						$progress.remove(); // Complete; i.e. remove progress bar.
				});
			}
			else $.get(vars.ajaxEndpoint, $form.serialize(), function(chartData)
			{
				if(!chartData) return; // Not possible.

				if(typeof chartData.errors === 'string')
				{
					$statsView.append($(chartData.errors)), // Append errors.
						$progress.remove(); // Complete; i.e. remove progress bar.

					return; // All done here.
				}
				$canvas = $('<canvas class="pmp-canvas"></canvas>'),
					$statsView.append($canvas); // Add canvas tag.

				var chartContext = $canvas.get(0).getContext('2d'),
					chartOps = $.extend({}, statsViewProps.chartOps, chartData.options),
					chart = new Chart(chartContext).BetterBar(chartData.data, chartOps);

				$statsView.data('chart', chart), // Save chart reference.
					$progress.remove(); // Complete; i.e. remove progress bar.
			});
		};
		statsViewProps.$selects.chosen(chosenOps),
			statsViewProps.$buttons.on('click', statsViewProps.handler),
			statsViewProps.$buttons.filter('[data-auto-chart]').trigger('click');

		statsViewProps.$selects.filter('[name$="\\[type\\]"]')
			.on('change', function()
			    {
				    var $this = $(this), val = $this.val(),
					    $statsView = $this.closest('.pmp-stats-view'),
					    $byTr = $statsView.find('tr.pmp-stats-form-by');

				    $byTr.css({opacity: /(?:^|_)(?:popular_posts|geo)(?:_|$)/i.test(val) ? 0.2 : 1});
			    }).trigger('change');

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
	};
	$document.ready(plugin.onReady); // DOM ready handler.
})(jQuery);