$('#dateTime').click(function(){
		if ($("#calOne").css("display")=='none') {
			$('#calOne').slideDown();
			var width=208;
			$('.month').css('width', width).find('.monthName, .monthYear').css('width', ((width / 2) - 4 ));
		}else{
			$('#calOne').hide();
		}
		
	});

	$('#calOne').jCal({
		day:			new Date(),
		days:			7,
		showMonths:		2,
		monthSelect:	true,
		dCheck:			function (day) {
			
				if ( day.getTime() == (new Date('8/7/2008')).getTime() ) return false;
				return (day.getDate() != 3);

				// if (day.getTime()) {};
			},
		callback:		function (day, days) {
				$('#calOneDays').val( days );
				$(this._target).find('.dInfo').remove();
				var dCursor = new Date( day.getTime()-86400 );
				for (var di=0; di < days; di++) {
					var currDay = $(this._target).find('[id*=d_' + ( dCursor.getMonth() + 1 ) + '_' + dCursor.getDate() + '_' + dCursor.getFullYear() + ']');
					// if (currDay.length) currDay.append('<div class="dInfo"><span style="color:#ccc">'+dCursor.getDate()+'</span></div>');
					dCursor.setDate( dCursor.getDate() + 1 );
					// console.log(dCursor.getDate());
				}
				$('#dateTime').val(day.getFullYear()+ '/' +( day.getMonth() + 1 )+ '/' + day.getDate()+' - '+dCursor.getFullYear()+ '/' +( dCursor.getMonth() + 1 )+ '/' + dCursor.getDate());
				
				
				// if ( typeof $(this._target).data('day') == 'object' &&
				// 	 $(this._target).data('day').getTime() == day.getTime() &&
				// 	 $(this._target).data('days') == days ) {
					
				// 	$('#calOneResult').append('<div style="clear:both; font-size:7pt;">' + days + ' days starting ' +
				// 		( day.getMonth() + 1 ) + '/' + day.getDate() + '/' + day.getFullYear() + ' RECLICKED</div>');
				// } else {
				// 	$('#calOneResult').append('<div style="clear:both; font-size:7pt;">' + days + ' days starting ' +
				// 		( day.getMonth() + 1 ) + '/' + day.getDate() + '/' + day.getFullYear() + '</div>');
				// }
				return true;
			}
		});
$('#calOne').delegate('.day','click',function(){
	$('#calOne').hide();		  			
});