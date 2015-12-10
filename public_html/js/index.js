$('input[type="submit"]').mousedown(function(){
  $(this).css('background', '#2ecc71');
});
$('input[type="submit"]').mouseup(function(){
  $(this).css('background', '#1abc9c');
});

$('#loginform').click(function(){
  $('.login').fadeToggle('slow');
  $(this).toggleClass('green');
});


$(document).mouseup(function (e)
{
    var container = $(".login");

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.hide();
        $('#loginform').removeClass('green');
    }
});

var extraFilter = 0;
$(document).ready(function() {
	$('button[name="addfilter"]').click(function(){
		extraFilter++;
		$('#dynamicFilter').animate({height: '+=36'},'fast');
		$('#dynamicFilter').append(
		'<div id="filter"><select class="filterStyle"><option value="" disabled selected><b>Please select a filter</b></option><optgroup label="Filter"><option value="author">Author</option><option value="title">Title</option><option value="type">Type</option><option value="status">Status</option><option value="keyword">Keyword</option></optgroup></select><select class="filterStyle"><optgroup><option value="exact">Exact</option><option value="contain">Contain</option></optgroup></select><input class="filterInput" type="text" name="filter"></div>');
	});
});

$(document).ready(function() {		
	$('button[name="filterremove"]').click(function(){
		if (extraFilter == 0) return;
		extraFilter--;
		$('#dynamicFilter').animate({height: '-=36'},'fast');
		$('#dynamicFilter #filter:last-child').remove();
	});
});