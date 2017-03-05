jQuery( document ).ready( function( $ ) {

	// Creates the data display element.
	$('body').prepend('<div id="dd-display"></div>');

	// Creates the element to store modal content.
	$('body').prepend('<div id="dd-modal" style="display: none;"></div>');

	// Adds WordPress elements to the discovery system.
	addWPelements();

	// Adds links to expand descovery info.
	$('.dd-definition').append('<a class="thmfdn-discovery-expand" href="#"></a>');

	// Populates the data display element.
	displayDataTree();

	/**
	 * Gets the tree of developer discoverable data
	 *
	 * @param element The HTML element from which to build the data tree.
	 */
	function calculateDataTree( element = $('#dd-data') ) {
		data = [];
		parentDefinition = '';

		// Gets discoverable elements in current tree.
		element.parents('.dd-definition').each(function( index ) {
			// alert($(this).attr('data-discovery'));
			definition = $(this).data('discovery');
			// alert($(this).attr('data-discovery'));

			// Is there any data for the definition table?
			if (definition['table']) {

				// Is the current template a parent?
				if (definition['table']['Parent']) {
					// Store the parent for insertion at a later time.
					parentDefinition = definition;
				} else {

					// Is there a parent waiting to be inserted?
					if (parentDefinition) {
						definition['table']['Extends'] = parentDefinition['table']['Path'];
						data.unshift(definition);
						parentDefinition['table']['Extended By'] = definition['table']['Path'];
						data.unshift(parentDefinition);
						parentDefinition = '';
					} else {
						data.unshift(definition);
					}
				}
			} else {
				data.unshift(definition);
			}
		});

		// Adds the base templates
		baseTemplates = JSON.parse($('#dd-data').attr('data-discovery'));

		extended = false;

		if (baseTemplates.length > 1) {
			extension = true;
		} else {
			extension = false;
		}
		$.each(baseTemplates, function( index, template ) {
			className = '';

			if ( extended ) {
				// alert('Is extended: ' + template['handle']);
				template['table']['Extended By'] = baseTemplates[index-1]['handle'];
			}
			if ( extension ) {
				if (baseTemplates.length -2 == index) {
					// alert(template['handle'] + ' creates cuttoff');
					extension = false;
				}
				template['table']['Extends'] = baseTemplates[index+1]['handle'];
				// alert('Is an extension: ' + template['handle']);
				extended = true;
			}

			// display += '<a href="#" class="' + className + '" data-discovery-popup=\'' + JSON.stringify(template) + '\'>' + template['handle'] + '</a>';
			// $.each(part, function( title, value ) {
				// Just in case deeper details are needed here?
			// });
			data.unshift(template);
		});

		return data;
	}

	/**
	 * Displays the developer discovery data
	 *
	 * @param data An array of discoverable elements.
	 */
	function displayDataTree( data = calculateDataTree() ) {
		display = '';

		// alert(JSON.stringify(data));

		// Create link to reset data tree display
		if (data.length > baseTemplates.length) {
			display += '<a href="#" class="dd-reset">x</a>';
		}

		// Loops through discoverable elements in current tree.
		$.each(data, function( index, part ) {
			className = '';
			// alert(index);
			// alert(JSON.stringify(part));
			if ( part['table'] ) {
				if ( part['table']['Extended By'] ) {
					className += ' extended';
				}
				if ( part['table']['Extends'] ) {
					className += ' extension';
				}
			}
			display += '<a href="#" class="' + className + '" data-discovery-popup=\'' + JSON.stringify(part) + '\'><span>' + part['handle'] + '</span></a>';
			// $.each(part, function( title, value ) {
				// Just in case deeper details are needed here?
			// });
		});

		if (data.length > baseTemplates.length) {
			$('#dd-display').addClass('expanded');
		} else {
			$('#dd-display').removeClass('expanded');
		}

		// Displays the new tree.
		$('#dd-display').html(display);

		// Resets the listener
		modalListener();
	}

	/**
	 * Sets the modal listener
	 *
	 * This code is placed within a function because it has to be called
	 * each time the contents of the #dd-display element changes.
	 */
	function modalListener() {

		// Generates and displays the discovery modal.
		$('#dd-display > a').click(function(e) {
			e.preventDefault();
			$(this).css('outline', 'none');

			if (!$(this).hasClass('dd-reset')) {
				$('#dd-data').html(getModalContent($(this).attr('data-discovery-popup')));
				$('#dd-data').modal();
			}
		});

		// Resets the #dd-display element.
		$('.dd-reset').click(function(e) {
			e.preventDefault();
			$('#dd-display').fadeOut('fast', function() {
				displayDataTree();
			});
			$('#dd-display').fadeIn();
		});
	}


	// Generates the discovery modal content.
	function getModalContent(json) {

		data = JSON.parse(json);

		output = '';
		// output += json;
		output += '<h5>' + data['handle'] + '</h5>';
		if (data['description']) {
			output += '<p>' + data['description'] + '</p>';
		}
		output += '<table>';

		$.each(data['table'], function( index, value ) {
			// alert(index);
			output += '<tr><th>'+index+'</th><td>'+value+'</td></tr>';
			// $.each(part, function( title, value ) {
				// Just in case deeper details are needed here?
			// });
		});
		output += '</table>';

		return output;

	}

	$('.dd-definition > a').click(function(e) {
		e.preventDefault();
		// $('.dd-selected').removeClass('dd-selected');
		// $('.dd-current').addClass('dd-selected');
		// alert('test');
		$(this).css('outline', 'none');
		// $('#dd-display').fadeOut('fast', function() {
			displayDataTree(calculateDataTree($(this)));
		// });
		// $('#dd-display').fadeIn();
	});

	keys = {
		d:      68,
		h:      72,
		esc:    27,
	};

	window.onkeyup = function(e) {

		// Was a standard modifier key pressed?
		if (e.altKey || e.ctrlKey) {
			return true;
		}

		if ($('.dd-current').length) {
			switch(e.keyCode) {
					case keys.d: {
						currentDataTree = calculateDataTree($('.dd-current > a'));
						displayDataTree(currentDataTree);
						break;
					}
					case keys.h: {
						$('body').removeClass('dd-highlight');
						break;
					}
					default: {
						// alert('a different key was pressed');
					}
			}

			e.stopPropagation();
		} else {
			return true;
		}

	}


	window.onkeydown = function(e) {

		// Was a standard modifier key pressed?
		if (e.altKey || e.ctrlKey) {
			return true;
		}

		if ($('.dd-current').length) {


			switch(e.keyCode) {

					case keys.h: {
						$('body').addClass('dd-highlight');
						break;
					}

					default: {
						// alert('a different key was pressed');
					}
			}

			e.stopPropagation();
		} else {
			return true;
		}
	}



	function addWPelements() {

		details = {};
		details.handle ='the_post_navigation()';
		details.table = {
			'Type': 'WordPress function',
			'Docs': '<a href="https://developer.wordpress.org/reference/functions/the_post_navigation/">https://developer.wordpress.org/reference/functions/the_post_navigation/</a>'
		};
		detailsJSON = JSON.stringify(details);
		// alert(doo);
		$('.post-navigation').wrap('<div class="dd-definition" data-discovery=\'' + detailsJSON + '\'></div>');
		// $('.post-navigation').each(function() {
		// 	// test = $(this).data('discovery');
		// 	// alert(test['handle']);
		// });



	}



















		$('.dd-definition').on('mouseover', function (event) {

			// Prevent event from bubbling up to parent elements.
			event.stopPropagation();

			// Remove any previously "current" particles.
			$('.dd-current').removeClass('dd-current');

			// Mark the current particle.
			$(this).addClass('dd-current');


			// detailsJSON = $(this).find('.thmfdn-discovery-expand').attr('data-details');
			// dataDetails = JSON.parse(detailsJSON);
			// $('#thmfdn-discovery-box').html('<a href="#" class="thmfdn-footnoter" data-details=\'' + detailsJSON + '\'>' + dataDetails['path'] + '</a>');



			// console.log('Capture for ' + event.type + ' target is ' + overID);
		});

	$(function () {
		$('.thmfdn-discovery').on('mouseout', function (event) {
			event.stopPropagation();

			// $('.thmfdn-discovery-expand').hide();
			$(this).removeClass('dd-current');
			// $(this).children('.thmfdn-discovery-expand').hide( 200 );


			outID = $(this).children('.thmfdn-discovery-define').attr('id');
			// if (overID == outID) {
			// 	// alert(overID);
			// } else {

			// }
			// console.log('Capture for ' + event.type + ' target is ' + outID);
		});
	});


	function thmfdnPopulateModal(json) {
		data = JSON.parse(json);

		output = '';

		output += '<div id="thmfdn-modal-details" style="display: none;">';
		output += '<h5>' + data['title'] + '</h5>';
		output += '</div>';


		return output;

	}





	// var expandingLocations  = new Object();


	// $('.thmfdn-discovery').hover(function() {
	// 	currentID = $(this).children('.thmfdn-discovery-define').attr('id');
	// 	currentTop = $(this).offset().top;
	// 	// alert(currentTop);
	// 	expandingLocations[currentID] = currentTop;

	// 	// alert($(this).find('.thmfdn-discovery-define').attr('id'));
	// 	// alert(currentID);

	// 	// alert(thisLocation);
	// 	// alert(expandingLocations);
	// 	$.each(expandingLocations, function( index, value ) {
	// 		if ( index != currentID ) {
	// 			if ( value == currentTop ) {
	// 				// $(this).find('.thmfdn-discovery-expand').css('z-index', '10000000');
	// 				// alert( index + ": " + value + " matches " + currentID + ": " + currentTop );
	// 				// alert($('#'+index).siblings('.thmfdn-discovery-expand').attr('href'));
	// 				newZ = $('#'+currentID).siblings('.thmfdn-discovery-expand').css('z-index') - 10;
	// 				$('#'+currentID).siblings('.thmfdn-discovery-expand').css({'width': '64px', 'height': '64px', 'border-bottom-right-radius': '80px', 'z-index': newZ});
	// 				// $('#'+index).find('.thmfdn-discovery-expand').hide(1000);
	// 			} else {
	// 				// alert( index + " no match " + value );
	// 			}
	// 		}
	// 	});
	// 	$(this).children('.thmfdn-discovery-expand').show( 200 );

	// 	// alert(JSON.stringify(expandingLocations));
	// 	// test[test.length] = $(this).find('.thmfdn-discovery-expand').offset().left;
	// 	// alert( $(this).find('.thmfdn-discovery-expand').offset().top );

	// },function(){
	// 	$(this).find('.thmfdn-discovery-expand').hide( 200 );
	// 	formerID = $(this).children('.thmfdn-discovery-define').attr('id');
	// 	delete expandingLocations[formerID];
	// 	// alert(JSON.stringify(expandingLocations));

	// });

});
