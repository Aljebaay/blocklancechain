$(document).ready(function(){
	var base_url = $("#custom-js").data("base-url");
	var seller_id = $("#custom-js").data("logged-id");
	var enable_sound = $("#custom-js").data("enable-sound");
	var enable_notifications = $("#custom-js").data("enable-notifications");
	var disable_messages = $("#custom-js").data("disable-messages");
	var parseJSONSafe = function(data){
		if(typeof data === "object" && data !== null){
			return data;
		}
		if(typeof data !== "string"){
			return null;
		}
		try{
			return $.parseJSON(data);
		}catch(error){
			if(window.console && typeof window.console.warn === "function"){
				window.console.warn("Invalid JSON response received.", data.substring(0, 80));
			}
			return null;
		}
	};

	// Langauge
	$("#languageSelect").change(function(){
   	var url = $("#languageSelect option:selected").data("url");
   	window.location.href = url;
  	});
  	$("#currencySelect").change(function(){
   	var url = $("#currencySelect option:selected").data("url");
   	window.location.href = url;
  	});
  	$("#currencySelect2").change(function(){
   	var url = $("#currencySelect2 option:selected").data("url");
   	window.location.href = url;
  	});
  	$("#languageSelect").msDropdown({visibleRows:4});
  	$("#currencySelect").msDropdown({visibleRows:4});
  	$("#currencySelect2").msDropdown({visibleRows:4});

  	/// Announcement Bar

	$("#announcement_bar .close-icon").click(function(){
		$("#announcement_bar").slideUp();
		$("#announcement_bar_margin").slideUp();
	});

	var a_height = $("#announcement_bar").height()+25;

	$("#announcement_bar_margin").css({'margin-bottom': a_height+'px' });

	// cookies alert
	$(".cookies_footer .btn").click(function(){
		$.ajax({
		method: "POST",
		url: base_url+"/includes/close_cookies_footer.php",
		data: {close : 'close_cookies'}
		}).done(function(data){
		  $(".cookies_footer").fadeOut();
		});
	});

	$("#announcement_bar .close-icon").click(function(){
		time = $("#announcement_bar .time").text();
		$.ajax({
		method: "POST",
		url: base_url+"/includes/close_cookies_footer.php",
		data: {close: 'close_announcement',time:time}
		}).done(function(data){
		  $("#announcement_bar").fadeOut();
		});
	});

	// Timezone
	var timezone_offset_minutes = new Date().getTimezoneOffset();
	timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
	// Timezone difference in minutes such as 330 or -360 or 0
	$("input[name='timezone']").val(timezone_offset_minutes);

	$('.my-navbar-toggler').click(function(){
		$('#order-status-bar').toggle();
	});
	    
	$(".home-featured-carousel").owlCarousel({
		items:1,
		margin:0,
		autoplay:false,
		loop:false,
		rtl:false,
		nav:true,
		autoplaySpeed:1000,
		responsiveClass:true,

	});

	$('[data-toggle="tooltip"]').tooltip();

	$(document).on('click','.dropdown-menu',function(event){
		event.stopPropagation();
	});

	$(".dropdown-menu .dropdown-item.dropdown-toggle").click(function(){
		$('.collapse.dropdown-submenu').collapse('hide');
	});

	$(".home-cards-carousel").owlCarousel({
		items:5,
		margin:18,
		autoplay:false,
		nav:true,
		autoplaySpeed:1000,
		responsiveClass:true,
		responsive:{
		0:{
		items:2,
		margin:14,
		autoWidth:true,
		},
		480:{
		items:2	
		},
		768:{
			items:3
		},
		900:{
			items:4
		},
		1140:{
			items:5
		}
		}
	});

	$(".user-home-featured-carousel").owlCarousel({		
		items:3,
		margin:30,
		stagePadding:20,
		autoplay:true,
		autoplaySpeed:1000,
		responsive:{
			0:{
				items:1,
			},
			480:{
				items:1,
			},
			600:{
				items:2
			},
			1000:{
				items:3
			}
		}
	});

	$("#register-modal input[name='u_name']").keypress(function (e) {
		if (!(e.which != 8 && e.which != 0 &&  ((e.which >= 45 && e.which <= 45)  || (e.which >= 48 && e.which <= 57)  || (e.which >= 65 && e.which <= 90) || (e.which >= 95 && e.which <= 95) || (e.which >= 97 && e.which <= 122) ))) {
				event.preventDefault();
			}
		}).keyup(function (e) {
			if (!(e.which != 8 && e.which != 0 &&  ((e.which >= 45 && e.which <= 45)  || (e.which >= 48 && e.which <= 57)  || (e.which >= 65 && e.which <= 90) || (e.which >= 95 && e.which <= 95) || (e.which >= 97 && e.which <= 122) ))) {
				event.preventDefault();
			}
		}).keypress(function (e) {
			if (!(e.which != 8 && e.which != 0 &&  ((e.which >= 45 && e.which <= 45)  || (e.which >= 48 && e.which <= 57)  || (e.which >= 65 && e.which <= 90) || (e.which >= 95 && e.which <= 95) || (e.which >= 97 && e.which <= 122) ))) {
				event.preventDefault();
			}
	});

	// Search autocomplete with debounce to prevent excessive requests
	var searchTimer = null;
	var searchXhr = null;
	$("#search-query").keyup(function(e){
		var val = $(this).val();
		if(searchTimer){ clearTimeout(searchTimer); }
		if(searchXhr){ searchXhr.abort(); searchXhr = null; }
		if(val != "" && val.length >= 2){
			$('.search-bar-panel').removeClass('d-none');
			searchTimer = setTimeout(function(){
				searchXhr = $.ajax({
					type: "POST",
					url: base_url+"/includes/comp/search-auto",
					data: {seller_id:seller_id, search:val},
					success: function(data){
						searchXhr = null;
						result = parseJSONSafe(data);
						if(!result){
							$('.search-bar-panel').addClass('d-none');
							return;
						}
			      	proposals = result.proposals;
			      	sellers = result.sellers;
						var html = "";
						if(result.count_proposals > 0){
						 	html += "<aside><li> <i class='fa fa-paint-brush'></i> Services </li><ul>";
							for(i in proposals){
								html += "<li><a href='"+proposals[i].url+"'>"+proposals[i].title+"</a></li>";
							}
							html += "</ul></aside>";
						}
						if(result.count_sellers > 0){
							html += "<aside><li> <i class='fa fa-user'></i> Users </li><ul>";
							for(i in sellers){
								html += "<li><a href='"+sellers[i].url+"'>"+sellers[i].name+"</a></li>";
							}
							html += "</ul></aside>";
						}
						if(result.count_proposals == 0 & result.count_sellers == 0){
							var html = "<li class='text-center'><b>"+result.no_results+"</b></li>";
						}
						$('.search-bar-panel').html(html);
					}
				});
			}, 400); // 400ms debounce
		}else{
			$('.search-bar-panel').addClass('d-none');
		}
	});

	if(seller_id != 0){
		// === OPTIMIZED POLLING INTERVALS ===
		// Reduced frequency to prevent server overload (was 30s/45s, now 90s/120s)
		var ACTIVITY_INTERVAL = 120000;    // 2 min (was 1 min)
		var LIGHT_POLL_INTERVAL = 90000;   // 90 sec (was 30 sec)
		var POPUP_POLL_INTERVAL = 120000;  // 2 min (was 45 sec)
		var BACKOFF_MULTIPLIER = 1.5;      // Increase delay when no new data
		var MAX_POLL_INTERVAL = 300000;    // 5 min max backoff

		// Track current intervals for adaptive backoff
		var currentIntervals = {
			favorites: LIGHT_POLL_INTERVAL,
			messagesHeader: LIGHT_POLL_INTERVAL,
			notificationsHeader: LIGHT_POLL_INTERVAL,
			messagesBody: LIGHT_POLL_INTERVAL,
			notificationsBody: LIGHT_POLL_INTERVAL,
			messagePopup: POPUP_POLL_INTERVAL,
			notificationsPopup: POPUP_POLL_INTERVAL
		};

		// Reset intervals back to normal when tab becomes visible
		document.addEventListener("visibilitychange", function(){
			if(!document.hidden){
				for(var key in currentIntervals){
					if(key === "messagePopup" || key === "notificationsPopup"){
						currentIntervals[key] = POPUP_POLL_INTERVAL;
					} else {
						currentIntervals[key] = LIGHT_POLL_INTERVAL;
					}
				}
			}
		});

		// Helper: get next interval with backoff (increases when no changes detected)
		var getBackoffInterval = function(key, hasChanges){
			if(hasChanges){
				// Reset to base interval when there are changes
				if(key === "messagePopup" || key === "notificationsPopup"){
					currentIntervals[key] = POPUP_POLL_INTERVAL;
				} else {
					currentIntervals[key] = LIGHT_POLL_INTERVAL;
				}
			} else {
				// Gradually increase interval when idle (no new data)
				currentIntervals[key] = Math.min(
					Math.round(currentIntervals[key] * BACKOFF_MULTIPLIER),
					MAX_POLL_INTERVAL
				);
			}
			return currentIntervals[key];
		};

		setInterval(function(){
			if(document.hidden){ return; }
	    	update_last_activity();
	    }, ACTIVITY_INTERVAL);

		function update_last_activity(){
			$.ajax({
				url:base_url+"/includes/update_activity",
				success:function(){}
			});
    	}

		$(document).on("click", ".proposal-favorite", function(event){
			var proposal_id = $(this).attr("data-id");
			$.ajax({
				type: "POST",
				url: base_url+"/includes/add_delete_favorite",
				data:{seller_id:seller_id, proposal_id:proposal_id, favorite:"add_favorite"},
				success: function(){
					$('i[data-id="'+proposal_id+'"]').attr({ class:"proposal-unfavorite fa fa-heart"});
				}
			});
		});

		$(document).on("click", ".proposal-unfavorite", function(event){
			var proposal_id = $(this).attr("data-id");
			$.ajax({
			type:"POST",
			url:base_url+"/includes/add_delete_favorite",
			data:{seller_id:seller_id,proposal_id:proposal_id,favorite:"delete_favorite"},
			success: function(){
				$('i[data-id="'+proposal_id+'"]').attr({class:"proposal-favorite fa fa-heart"});
			}
			});
		});

		$(".proposal-offer").click(function(){
			var proposal_id = $(this).attr("data-id");
			$.ajax({
			method: "POST",
			url: base_url+"/referral_modal",
			data: {proposal_id: proposal_id }
			}).done(function(data){
				$(".append-modal").html("");
				$(".append-modal").html(data);
			});
		});

		$(document).on("click", ".closePopup", function(event){
			event.preventDefault();
			$(this).parent().fadeOut();
		});

		//// Ajax Requests Code Starts ////
		play = new Audio(base_url+"/images/sound.mp3");
		play.volume = 0.1;
		var stop_audio = function(){
			play.pause();
		}

		// scroll down height
		var height = 0;
		$(".col-md-8 .messages .inboxMsg").each(function(i, value){
			height += parseInt($(this).height());
		});
		height += 2000;

		// REMOVED: messages-bells setInterval (redundant with c_messages_header)
		// The c_messages_header poll already checks for new messages and updates the count.
		// Sound notification is now handled inside c_messages_header instead.
		var lastMessageCount = -1;

		var c_favorites = function(){
			if(document.hidden){
				setTimeout(c_favorites, currentIntervals.favorites);
				return;
			}
			$.ajax({
				method: "POST",
				url: base_url+"/includes/comp/c-favorites",
				data: {seller_id: seller_id}
			}).done(function(data){
				data = parseInt(data);
				var hasChanges = false;
				if(data > 0){
					hasChanges = ($(".c-favorites").html() != "" + data);
					$(".c-favorites").html(data);
				}else{ 
					$(".c-favorites").html(""); 
				}
				setTimeout(c_favorites, getBackoffInterval("favorites", hasChanges));
			}).fail(function(){
				setTimeout(c_favorites, getBackoffInterval("favorites", false));
			});
		}
		c_favorites();

		// c_messages_header (also handles sound notification, replacing messages-bells)
		var c_messages_header = function(){
			if(document.hidden){
				setTimeout(c_messages_header, currentIntervals.messagesHeader);
				return;
			}
			$.ajax({
			method: "POST",
			url: base_url+"/includes/comp/c-messages-header",
			data: {seller_id: seller_id}
			}).done(function(data){
				var count = parseInt(data) || 0;
				var hasChanges = false;
				if(count > 0){
					$(".c-messages-header").html(count);
					// Play sound if new messages detected (replaces messages-bells)
					if(lastMessageCount >= 0 && count > lastMessageCount && enable_sound == "yes"){
						play.play();
						setTimeout(stop_audio, 2000);
					}
					hasChanges = (lastMessageCount !== count);
				}else{ 
					$(".c-messages-header").html(""); 
				}
				lastMessageCount = count;
				setTimeout(c_messages_header, getBackoffInterval("messagesHeader", hasChanges));
			}).fail(function(){
				setTimeout(c_messages_header, getBackoffInterval("messagesHeader", false));
			});
		}
		c_messages_header();

		// c_messages_body - loads on demand when dropdown is opened, then polls at reduced rate
		var messagesBodyLoaded = false;
		var loadMessagesBody = function(){
			$.ajax({
			method: "POST",
			url: base_url+"/includes/comp/c-messages-body",
			data: {seller_id: seller_id}
			}).done(function(data){
				result = parseJSONSafe(data);
				if(!result){ return; }
				messages = result.messages;
				html = "<h3 class='dropdown-header'> "+result['lang'].inbox+" ("+result.count_all_inbox_sellers+") <a class='float-right make-black' href='"+base_url+"/conversations/inbox' style='color:black;'>"+result['lang'].view_inbox+"</a></h3>";
				if(parseInt(result.count_all_inbox_sellers) == 0){
					html += "<h6 class='text-center mt-3'>"+result['lang'].no_inbox+"</h6>";
				}
				for(i in messages){
					html += "<div class='"+messages[i].class+"'><a href='"+base_url+"/conversations/inbox?single_message_id="+messages[i].message_group_id+"'><img src='"+messages[i].sender_image+"' width='50' height='50' class='rounded-circle'><strong class='heading'>"+messages[i]['sender_user_name']+"</strong><p class='message text-truncate'>"+messages[i].desc+"</p><p class='date text-muted'>"+messages[i].date+"</p></a></div>";
				}
				if(parseInt(result.count_all_inbox_sellers) > 0){
				html += "<div class='mt-2'><center class='pl-2 pr-2'><a href='"+base_url+"/conversations/inbox' class='ml-0 btn btn-success btn-block'>"+result.see_all+"</a></center></div>";
				}
				$('.messages-dropdown').html(html);
				messagesBodyLoaded = true;
			});
		};
		// Load messages body initially once, then refresh on dropdown open
		loadMessagesBody();
		$(document).on("click", ".c-messages-header, [data-target='.messages-dropdown'], .messages-dropdown-toggle", function(){
			loadMessagesBody();
		});
		// Also refresh body periodically but at a much lower rate
		var c_messages_body = function(){
			if(document.hidden){
				setTimeout(c_messages_body, currentIntervals.messagesBody);
				return;
			}
			loadMessagesBody();
			setTimeout(c_messages_body, getBackoffInterval("messagesBody", false));
		};
		setTimeout(c_messages_body, LIGHT_POLL_INTERVAL);

		var c_notifications_header = function(){
			if(document.hidden){
				setTimeout(c_notifications_header, currentIntervals.notificationsHeader);
				return;
			}
			$.ajax({
			method: "POST",
			url: base_url+"/includes/comp/c-notifications-header",
			data: {seller_id: seller_id}
			}).done(function(data){
				var count = parseInt(data) || 0;
				var hasChanges = ($(".c-notifications-header").html() != "" + count);
				if(count > 0){
					$(".c-notifications-header").html(count);
				}else{ 
					$(".c-notifications-header").html(""); 
				}
				setTimeout(c_notifications_header, getBackoffInterval("notificationsHeader", hasChanges));
			}).fail(function(){
				setTimeout(c_notifications_header, getBackoffInterval("notificationsHeader", false));
			});
		}
		c_notifications_header();

		// c_notifications_body - loads on demand when dropdown is opened
		var loadNotificationsBody = function(){
			$.ajax({
			method: "POST",
			url: base_url+"/includes/comp/c-notifications-body",
			data: {seller_id: seller_id}
			}).done(function(data){
				result = parseJSONSafe(data);
				if(!result){ return; }
				notifications = result.notifications;
				html = "<h3 class='dropdown-header'> "+result['lang'].notifications+" ("+result.count_all_notifications+") <a class='float-right make-black' href='"+base_url+"/notifications' style='color:black;'>"+result['lang'].view_notifications+"</a></h3>";
				if(parseInt(result.count_all_notifications) == 0){
					html += "<h6 class='text-center mt-3'>"+result['lang'].no_notifications+"</h6>";
				}
				for(i in notifications){
					html += "<div class='"+notifications[i].class+"'><a href='"+base_url+"/dashboard?n_id="+notifications[i].id+"'><img src='"+notifications[i].sender_image+"' width='50' height='50' class='rounded-circle'><strong class='heading'>"+notifications[i]['sender_user_name']+"</strong><p class='message text-truncate'>"+notifications[i].message+"</p><p class='date text-muted'>"+notifications[i].date+"</p></a></div>";
				}
				if(parseInt(result.count_all_notifications) > 0){
					html += "<div class='mt-2'><center class='pl-2 pr-2'><a href='"+base_url+"/notifications' class='ml-0 btn btn-success btn-block'>"+result.see_all+"</a></center></div>";
				}
				$('.notifications-dropdown').html(html);
			});
		};
		// Load notifications body initially once, then refresh on dropdown open
		loadNotificationsBody();
		$(document).on("click", ".c-notifications-header, [data-target='.notifications-dropdown'], .notifications-dropdown-toggle", function(){
			loadNotificationsBody();
		});
		// Also refresh body periodically but at a much lower rate
		var c_notifications_body = function(){
			if(document.hidden){
				setTimeout(c_notifications_body, currentIntervals.notificationsBody);
				return;
			}
			loadNotificationsBody();
			setTimeout(c_notifications_body, getBackoffInterval("notificationsBody", false));
		};
		setTimeout(c_notifications_body, LIGHT_POLL_INTERVAL);
			
		// messagePopup
		var messagePopup = function(){
			if(document.hidden){
				setTimeout(messagePopup, currentIntervals.messagePopup);
				return;
			}
			$.ajax({
			method: "POST",
			url: base_url+"/includes/messagePopup",
			data: {seller_id: seller_id}
			}).done(function(data){
				var hasChanges = false;
				if(enable_notifications == 1 && disable_messages == 0){
					result = parseJSONSafe(data) || [];
					html = '';
					for(i in result){
						hasChanges = true;
						html += "<div class='header-message-div'><a class='float-left' href='"+base_url+"/conversations/inbox?single_message_id="+result[i].message_group_id+"'><img src='"+result[i].sender_image+"' width='50' height='50' class='rounded-circle'><strong class='heading'>"+result[i].sender_user_name+"</strong><p class='message'>"+result[i].desc+"</p><p class='date text-muted'>"+result[i].date+"</p></a><a href='#' class='float-right close closePopup btn btn-sm pl-lg-5 pt-0'><i class='fa fa-times'></i></a></div>";
					}
					$('.messagePopup').prepend(html);
				}
				setTimeout(messagePopup, getBackoffInterval("messagePopup", hasChanges));
			}).fail(function(){
				setTimeout(messagePopup, getBackoffInterval("messagePopup", false));
			});
		}
		messagePopup();
	
		var notificationsPopup = function(){
			if(document.hidden){
				setTimeout(notificationsPopup, currentIntervals.notificationsPopup);
				return;
			}
			$.ajax({
			method: "POST",
			url: base_url+"/includes/notificationsPopup",
			data: {seller_id: seller_id, enable_sound: enable_sound}
			}).done(function(data){
				var hasChanges = false;
				if(enable_notifications == 1){
					result = parseJSONSafe(data) || [];
					html = '';
					for(i in result){
						hasChanges = true;
						html += "<div class='header-message-div'><a class='float-left' href='"+base_url+"/dashboard?n_id="+result[i].notification_id+"'><img src='"+result[i].sender_image+"' width='50' height='50' class='rounded-circle'><strong class='heading'>"+result[i].sender_user_name+"</strong><p class='message'>"+result[i].message+"</p><p class='date text-muted'>"+result[i].date+"</p></a><a href='#' class='float-right close closePopup btn btn-sm pl-lg-5 pt-0'><i class='fa fa-times'></i></a>"+result[i].more+"</div>";
						if(enable_sound == "yes"){ 
							play.play();
						}
					}
					$('.messagePopup').prepend(html);
				}
				setTimeout(notificationsPopup, getBackoffInterval("notificationsPopup", hasChanges));
				setTimeout(stop_audio, 2000);
			}).fail(function(){
				setTimeout(notificationsPopup, getBackoffInterval("notificationsPopup", false));
			});
		}
		notificationsPopup();
		// Ajax Requests Code Ends ////

	}

	// Footer
	if($(window).width() < 767.98) {
		// do something for small screens
		$("footer .collapse.show").removeClass("show");
	}else if ($(window).width() >= 767.98 &&  $(window).width() <= 991.98) {
		// do something for medium screens
	}else if ($(window).width() > 992 &&  $(window).width() <= 1199.98) {
		// do something for big screens
		$(".footer .collapse.show").removeClass("collapse");
	}else{
		// do something for huge screens
		$("footer h3").removeAttr("data-toggle","data-target");
	}

});
