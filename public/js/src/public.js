(function ( $ ) {
	"use strict";
	console.log( "running it" );
	var $TABLE = $('table.data-set-edit');
	var $BTN = $('.export-data');
	var $EXPORT = $('#exported-data');

	$('.add-row').click(function () {
	  var $clone = $TABLE.find('tr.hidden').clone(true).removeClass('hidden table-line').attr( "hidden", false );
	  $TABLE.append($clone);
	});

	$TABLE.on( "click", ".remove-row", function() {
		console.log( "removing row!" );
		$(this).parents('tr').remove();
	});

	// $('.table-up').click(function () {
	//   var $row = $(this).parents('tr');
	//   if ($row.index() === 1) return; // Don't go above the header
	//   $row.prev().before($row.get(0));
	// });

	// $('.table-down').click(function () {
	//   var $row = $(this).parents('tr');
	//   $row.next().after($row.get(0));
	// });

	// A few jQuery helpers for exporting only
	jQuery.fn.pop = [].pop;
	jQuery.fn.shift = [].shift;

	$BTN.click(function () {
	  var $rows = $TABLE.find('tr:not(:hidden)');
	  var headers = [];
	  var data = [];

	  // Get the headers (add special header logic here)
	  $($rows.shift()).find('th:not(:empty)').each(function () {
	    headers.push($(this).text().toLowerCase());
	  });

	  // Turn all existing rows into a loopable array
	  $rows.each(function () {
	    var $td = $(this).find('td:not(.row-actions)');
	    var h = {};

	    // Use the headers from earlier to name our hash keys
	    headers.forEach(function (header, i) {
	      h[header] = $td.eq(i).text();
	    });

	    data.push(h);
	  });

	  // Output the result
	  var content = JSON.stringify(data);
	  $EXPORT.text( content );
	  var post_url = CARES_Spreadsheets_Edit.root + 'wp/v2/cares_data_set/' + $( "#post-id" ).val();
	  console.log( post_url );
	  // Submit post data
	  $.ajax( {
		    url: post_url,
		    method: 'POST',
		    beforeSend: function ( xhr ) {
		        xhr.setRequestHeader( 'X-WP-Nonce', CARES_Spreadsheets_Edit.nonce );
		    },
		    data:{
		        'content' : content,
		    }
		} ).done( function ( response ) {
		    console.log( response );
		} );

	});

	// $( "#add-editor" ).keyup( function () {
	//   typewatch( function () {
	//     // executed only 500 ms after the last keyup event.
	//     fetch_possible_editors();
	//   }, 500 );
	// });

	// var typewatch = ( function() {
	//   var timer = 0;
	//   return function( callback, ms ) {
	//     clearTimeout( timer );
	//     timer = setTimeout( callback, ms );
	//   };
	// })();

	var timer = 0;
	$( "#add-editor" ).keyup( function () {
		clearTimeout( timer );
		timer = setTimeout( fetch_possible_editors, 500 );
	});

	function fetch_possible_editors() {
		console.log( "fetch editors!" );

		// If there's no search string, don't send request.
		if ( ! $("#add-editor").val() ) {
			return;
		}

		// Disable the text box while the request is running.
		$( "#add-editor" ).attr( "disabled", true );

		$.ajax( {
		    url: CARES_Spreadsheets_Edit.ajax_url,
		    method: 'POST',
		    data:{
		        'action' : 'cds_search_possible_editors',
		        'search' : $("#add-editor").val(),
		        'post_id' : $( "#post-id" ).val(),
		        '_wpnonce' : CARES_Spreadsheets_Edit.nonce
		    }
		} ).done( function ( response ) {
		    console.log( response );
    		$( "#add-editor" ).attr( "disabled", false );
		} );

	}

	function add_editors() {
		return update_editors( "add" );
	}
	// Adding and removing editors.
	function update_editors( operation ) {
		  $.ajax( {
		    url: CARES_Spreadsheets_Edit.ajax_url,
		    method: 'POST',
		    data:{
		        'action' : 'cds_update_allowed_editors',
		        'operation' : operation,
		        'editor_id' : 2,
		        'post_id' : $( "#post-id" ).val(),
		        '_wpnonce' : CARES_Spreadsheets_Edit.nonce
		    }
		} ).done( function ( response ) {
		    console.log( response );
		} );
	}
}(jQuery));