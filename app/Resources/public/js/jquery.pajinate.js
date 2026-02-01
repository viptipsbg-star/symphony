;
(function($) { /*******************************************************************************************/
	// jquery.pajinate.js - version 0.4
	// A jQuery plugin for paginating through any number of DOM elements
	// 
	// Copyright (c) 2010, Wes Nolte (http://wesnolte.com)
	// Licensed under the MIT License (MIT-LICENSE.txt)
	// http://www.opensource.org/licenses/mit-license.php
	// Created: 2010-04-16 | Updated: 2010-04-26
	//
	/*******************************************************************************************/


    var hash = window.location.hash;
    var current_page_num = 0;
    if (hash.indexOf('page-') > -1) {
        current_page_num = parseInt(hash.substr(hash.indexOf('page-') + 5, 1), 10) - 1;
    }

	$.fn.pajinate = function(options) {
		// Set some state information
		var current_page = 'current_page';
		var items_per_page = 'items_per_page';

//		var meta;

		// Setup default option values
		var defaults = {
			item_container_id: '.content',
			item_id: '',
			items_per_page: 10,
			nav_panel_id: '.page_navigation',
			nav_info_id: '.info_text',
			num_page_links_to_display: 20,
			start_page: current_page_num,
			wrap_around: false,
			nav_label_first: 'First',
			nav_label_prev: 'Prev',
			nav_label_next: 'Next',
			nav_label_last: 'Last',
			//nav_order: ["first", "prev", "num", "next", "last"],
			nav_order: ["num"],
			nav_label_info: 'Showing {0}-{1} of {2} results',
			show_first_last: true,
			abort_on_small_lists: true,
			jquery_ui: false,
			jquery_ui_active: "ui-state-highlight",
			jquery_ui_default: "ui-state-default",
			jquery_ui_disabled: "ui-state-disabled"
		};

		var options = $.extend(defaults, options);
		var $item_container;
		var $page_container;
		var $items;
		var $nav_panels;
		var total_page_no_links;
		var jquery_ui_default_class = options.jquery_ui ? options.jquery_ui_default : '';
		var jquery_ui_active_class = options.jquery_ui ? options.jquery_ui_active : '';
		var jquery_ui_disabled_class = options.jquery_ui ? options.jquery_ui_disabled : '';

		return this.each(function() {
			$page_container = $(this);
            var $this = $(this);
			$item_container = $(this).find(options.item_container_id);
			$items = $page_container.find(options.item_container_id).children(options.item_id);

			//if (options.abort_on_small_lists && options.items_per_page >= $items.size()) return $page_container;

			var meta = $page_container;

			// Initialize meta data
			meta.data(current_page, 0);
			meta.data(items_per_page, options.items_per_page);

			// Get the total number of items
			var total_items = $item_container.children(options.item_id).size();

			// Calculate the number of pages needed
			var number_of_pages = Math.ceil(total_items / options.items_per_page);

			// Construct the nav bar
			var more = '<span class="ellipse more">...</span>';
			var less = '<span class="ellipse less">...</span>';
			var first = !options.show_first_last ? '' : '<li><a class="first_link ' + jquery_ui_default_class + '" href="">' + options.nav_label_first + '</a></li>';
			var last = !options.show_first_last ? '' : '<li><a class="last_link ' + jquery_ui_default_class + '" href="">' + options.nav_label_last + '</a></li>';

			var navigation_html = "";
            navigation_html += "<ul>";

			if (!options.abort_on_small_lists || options.items_per_page < $items.size()) {

                for (var i = 0; i < options.nav_order.length; i++) {
                    switch (options.nav_order[i]) {
                    case "first":
                        navigation_html += first;
                        break;
                    case "last":
                        navigation_html += last;
                        break;
                    case "next":
                        navigation_html += '<li><a class="next_link ' + jquery_ui_default_class + '" href="#">' + options.nav_label_next + '</a></li>';
                        break;
                    case "prev":
                        navigation_html += '<li><a class="previous_link ' + jquery_ui_default_class + '" href="#">' + options.nav_label_prev + '</a></li>';
                        break;
                    case "num":
                        var current_link = 0;
                        while (number_of_pages > current_link) {
                            navigation_html += '<li class="page_link" data-pagenum="'+current_link+'"><a class="' + jquery_ui_default_class + '" href="#">' + (current_link + 1) + '</a></li>';
                            current_link++;
                        }
                        break;
                    default:
                        break;
                    }

                }
            }
            navigation_html += "</ul>";

			// And add it to the appropriate area of the DOM	
			$nav_panels = $page_container.find(options.nav_panel_id);
			$nav_panels.html(navigation_html).each(function() {

				$(this).find('.page_link:first').addClass('first');
				$(this).find('.page_link:last').addClass('last');

			});

			// Hide the more/less indicators
			$nav_panels.children('.ellipse').hide();

			// Set the active page link styling
			$nav_panels.find('.previous_link').next().next().addClass('active ' + jquery_ui_active_class);

			/* Setup Page Display */
			// And hide all pages
			$items.hide();
			// Show the first page			
			$items.slice(0, meta.data(items_per_page)).show();

			/* Setup Nav Menu Display */
			// Page number slices
			total_page_no_links = $page_container.find(options.nav_panel_id + ':first').children('.page_link').size();
			options.num_page_links_to_display_inner = Math.min(options.num_page_links_to_display, total_page_no_links);

			$nav_panels.children('.page_link').hide(); // Hide all the page links
			// And only show the number we should be seeing
			$nav_panels.each(function() {
				$(this).children('.page_link').slice(0, options.num_page_links_to_display_inner).show();
			});

			/* Bind the actions to their respective links */

			// Event handler for 'First' link
			$page_container.find('.first_link').click(function(e) {
				e.preventDefault();

				movePageNumbersRight($(this), 0);
				gotopage($this, 0);
			});

			// Event handler for 'Last' link
			$page_container.find('.last_link').click(function(e) {
				e.preventDefault();
				var lastPage = total_page_no_links - 1;
				movePageNumbersLeft($(this), lastPage);
				gotopage($this, lastPage);
			});

			// Event handler for 'Prev' link
			$page_container.find('.previous_link').click(function(e) {
				e.preventDefault();
				showPrevPage($(this));
			});


			// Event handler for 'Next' link
			$page_container.find('.next_link').click(function(e) {
				e.preventDefault();
				showNextPage($(this));
			});

			// Event handler for each 'Page' link
			$page_container.find('.page_link').click(function(e) {
				e.preventDefault();
				gotopage($this, $(this).data('pagenum'));
			});

            var start_page = (options.start_page < number_of_pages) ? options.start_page : (number_of_pages - 1);
            if (start_page < 0) {
                start_page = 0;
            }

			// Goto the required page
			gotopage($this, parseInt(start_page));
			toggleMoreLess($nav_panels);
			if (!options.wrap_around) tagNextPrev($nav_panels);

            /*
            $(window).on('hashchange', function(e){
                var hash = window.location.hash;
                var current_page_num = 0;
                if (hash.indexOf('page-') > -1) {
                    current_page_num = parseInt(hash.substr(hash.indexOf('page-') + 5, 1), 10) - 1;
                }
			    var now_page = meta.data(current_page);
                if (now_page != current_page_num) {
			        gotopage($this, parseInt(current_page_num));
                }
            });
           */

		function showPrevPage(e) {
			new_page = parseInt(meta.data(current_page)) - 1;

			// Check that we aren't on a boundary link
			if ($(e).siblings('.active').prev('.page_link').length == true) {
				movePageNumbersRight(e, new_page);
				gotopage($page_container, new_page);
			}
			else if (options.wrap_around) {
				gotopage($page_container, total_page_no_links - 1);
			}

		};

		function showNextPage(e) {
			new_page = parseInt(meta.data(current_page)) + 1;

			// Check that we aren't on a boundary link
			if ($(e).siblings('.active').next('.page_link').length == true) {
				movePageNumbersLeft(e, new_page);
				gotopage($page_container, new_page);
			}
			else if (options.wrap_around) {
				gotopage($page_container, 0);
			}

		};

		function gotopage($page_container, page_num) {
            var total_items = $page_container.find(options.item_container_id).children(options.item_id).size();
            // Calculate the number of pages needed
            var $number_of_pages = Math.ceil(total_items / options.items_per_page);

            if (!page_num && page_num !== 0) {
                page_num = options.start_page;
            }

            page_num = (0 > page_num) ? 0 : page_num;
            page_num = ($number_of_pages <= page_num) ? ($number_of_pages - 1) : page_num;

            if ($page_container.is(":visible")) {
                page_num = parseInt(page_num, 10)

                /*
                var hash = window.location.hash;

                if (hash.indexOf('page-') > -1) {
                    hash = hash.substr(0, hash.indexOf('page-'));
                }
                else if (hash != '') {
                    hash = hash + "-";
                }
                hash = hash + "page-" + (page_num + 1);
                window.location.hash = hash;
               */
            }

			var ipp = parseInt(meta.data(items_per_page));

			// Find the start of the next slice
			start_from = page_num * ipp;

			// Find the end of the next slice
			end_on = start_from + ipp;
			// Hide the current page	
            var allitems = $page_container.find(options.item_container_id).children(options.item_id);
			var items = allitems.hide().slice(start_from, end_on);

			items.show();

			// Reassign the active class
			$page_container.find(options.nav_panel_id).find('ul').children().eq(page_num).addClass('active ' + jquery_ui_active_class).siblings('.active').removeClass('active ' + jquery_ui_active_class);

			// Set the current page meta data							
			meta.data(current_page, page_num);
			/*########## Ajout de l'option page courante + nombre de pages*/
	            	var $current_page = parseInt(meta.data(current_page)+1);
	            	// Get the total number of items

            		/*##################################################################*/
			$page_container.find(options.nav_info_id).html(options.nav_label_info.replace("{0}", start_from + 1).
			replace("{1}", start_from + items.length).replace("{2}", $items.length).replace("{3}", $current_page).replace("{4}", $number_of_pages));

			// Hide the more and/or less indicators
			toggleMoreLess($page_container.find(options.nav_panel_id));

			// Add a class to the next or prev links if there are no more pages next or previous to the active page
			tagNextPrev($page_container.find(options.nav_panel_id));

			// check if the onPage callback is available and call it
			if (typeof(options.onPageDisplayed) !== "undefined" ) {
				options.onPageDisplayed.call(this, page_num + 1)
			}

		}

		// Methods to shift the diplayed index of page numbers to the left or right


		function movePageNumbersLeft(e, new_p) {
			var new_page = new_p;

			var $current_active_link = $(e).siblings('.active');

			if ($current_active_link.siblings('.page_link[data-pagenum=' + new_page + ']').css('display') == 'none') {

				$nav_panels.each(function() {
					$(this).children('.page_link').hide() // Hide all the page links
					.slice(parseInt(new_page - options.num_page_links_to_display_inner + 1), new_page + 1).show();
				});
			}

		}

		function movePageNumbersRight(e, new_p) {
			var new_page = new_p;

			var $current_active_link = $(e).siblings('.active');

			if ($current_active_link.siblings('.page_link[data-pagenum=' + new_page + ']').css('display') == 'none') {

				$nav_panels.each(function() {
					$(this).children('.page_link').hide() // Hide all the page links
					.slice(new_page, new_page + parseInt(options.num_page_links_to_display_inner)).show();
				});
			}
		}

		// Show or remove the ellipses that indicate that more page numbers exist in the page index than are currently shown


		function toggleMoreLess($nav_panels) {

            if ($nav_panels.children('.page_link.last').css('display') == 'none') {
				$nav_panels.children('.more').show();
			}
			else {
				$nav_panels.children('.more').hide();
			}

            if ($nav_panels.children('.page_link.first').css('display') == 'none') {
				$nav_panels.children('.less').show();
			}
			else {
				$nav_panels.children('.less').hide();
			}
		}

		/* Add the style class ".no_more" to the first/prev and last/next links to allow custom styling */

		function tagNextPrev($nav_panels) {
			if ($nav_panels.children('.last').hasClass('active')) {
				$nav_panels.children('.next_link').add('.last_link').addClass('no_more ' + jquery_ui_disabled_class);
			}
			else {
				$nav_panels.children('.next_link').add('.last_link').removeClass('no_more ' + jquery_ui_disabled_class);
			}

			if ($nav_panels.children('.first').hasClass('active')) {
				$nav_panels.children('.previous_link').add('.first_link').addClass('no_more ' + jquery_ui_disabled_class);
			}
			else {
				$nav_panels.children('.previous_link').add('.first_link').removeClass('no_more ' + jquery_ui_disabled_class);
			}
		}
		});

	};

})(jQuery);
