// js for front end of ProGo Themes Ecommerce sites
var progo_homecycling, progo_timing;

function proGoTwitterCallback(twitters) {
  for (var i=0; i<twitters.length; i++){
    var status = twitters[i].text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
      return '<a href="'+url+'">'+url+'</a>';
    }).replace(/\B@([_a-z0-9]+)/ig, function(reply) {
      return  reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';
    });
    jQuery('.tweets p.last').before('<p>'+status+'<br /><a href="http://twitter.com/'+twitters[i].user.screen_name+'/status/'+twitters[i].id_str+'" target="_blank">'+relative_time(twitters[i].created_at)+'</a> via '+ twitters[i].source +'</p>');
  }
}

function relative_time(time_value) {
  var values = time_value.split(" ");
  time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
  var parsed_date = Date.parse(time_value);
  var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
  var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
  delta = delta + (relative_to.getTimezoneOffset() * 60);

  if (delta < 60) {
    return 'less than a minute ago';
  } else if(delta < 120) {
    return 'about a minute ago';
  } else if(delta < (60*60)) {
    return (parseInt(delta / 60)).toString() + ' minutes ago';
  } else if(delta < (120*60)) {
    return 'about an hour ago';
  } else if(delta < (24*60*60)) {
    return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
  } else if(delta < (48*60*60)) {
    return '1 day ago';
  } else {
    return (parseInt(delta / 86400)).toString() + ' days ago';
  }
}

function progo_homecycle( prev ) {
	clearTimeout('progo_homecycling');
	var onn = jQuery('#pagetop .slide.on');
	var nex = onn.next();
	var dir = '-=960px';
	if( prev == true ) {
		nex = onn.prev();
		if(nex.size() == 0) {
			nex = onn.nextAll('.ar').prev();
		}
		nex.css({'left':'-960px'});
		dir = '+=960px';
	} else {
		if(nex.hasClass('slide') == false) {
			nex = onn.prevAll('.slide:first-child');
		}
		nex.css({'left':'960px'});
	}
	onn.add(nex).animate({
		left: dir
	}, 600, function() {
		jQuery(this).toggleClass('on');
	});
	jQuery('#pagetop').animate({
		height: nex.outerHeight(true)
	}, 600 );
	
	if( progo_timing > 0 ) {
		progo_homecycling = setTimeout("progo_homecycle(false)",progo_timing); 
	}
	return false;
}

jQuery(function($) {
	var progo_ptop = $('#pagetop');
	if(progo_ptop.hasClass('slides')) {
		progo_ptop.children('div.ar').children('a:first').click(function() { return progo_homecycle(true); }).next().click(function() { return progo_homecycle(false); });
		progo_ptop.height(progo_ptop.children('.slide.on').height()).addClass('sliding');
		if(progo_timing > 0) {
			progo_homecycling = setTimeout("progo_homecycle(false)",progo_timing);
		}
	}
});