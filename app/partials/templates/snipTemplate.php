	<script id="snipTemplate" type="text/x-handlebars-template" async="async">
		<header>
			<a href="profile/{{ user.username }}">
				<img src="{{ user.profile_image }}" alt="Profile Picture" />
			</a>

			<span>
				<a href="profile/{{ user.username }}">{{ user.name }}</a>
				<div class="snip-info">
					<i class="icon-heart-2"></i><span>{{ number_likes }}</span>
					<i class="icon-comment"></i><span>{{ number_comments }}</span>
				</div>
			</span>

			<time>{{ parsed_time }}</time>
		</header>
		<section>

			<div class="snip_actions">
				{{#greaterThan globalUID 0}}
					<i class="icon-comment"></i><a data-action="comment_snip" onclick="require('main').document.commentAction({{ post_id }});">Comment</a>

					{{#if has_user_liked}}
						· <i class="icon-heart-2 liked"></i><a href="#" class="liked" data-action="unlike_snip">Liked</a>
					{{else}}
						· <i class="icon-heart"></i><a href="#" data-action="like_snip">Like</a>
					{{/if}}

				{{/greaterThan}}	

				{{#greaterThan number_comments 2}}
					 · <i class="icon-comments"></i><a href="#" data-all-comments="false" data-action="show_all_comments">View all</a>
				{{/greaterThan}}
			</div>
			
			{{#greaterThan globalUID 0}}
				<a href="#" class="modify_snip" data-toggle="false"><i class="icon-chevron-down"></i></a>
				<ul class="dropdown_menu">
				
				{{#condEqual globalUID user.user_id}}
					<li><a onclick="require('main').API.functions.snips.delete({{ post_id }});">Delete</a></li>
					<li><a onclick="require('main').document.editSnipAction({{ post_id }}, {{ user.user_id }});">Edit</a></li>
					<li><a onclick="require('main').API.functions.snips.diableComments({{ post_id }}, {{ user.user_id }});">Disable Comments</a></li>
					<li><a onclick="require('main').document.addToStackAction({{ post_id }})">Add to Stack...</a></li>
					{{#if part_of_stack}}
						<li><a data-action="remove_from_stack">Remove From Stack...</a></li>
					{{/if}}
				{{/condEqual}}

				{{#condNotEqual globalUID user.user_id}}
					<li><a onclick="require('main').API.functions.snips.report({{ post_id }}, {{ globalUID }});">Report</a></li>
				{{/condNotEqual}}

				</ul>
			{{/greaterThan}}

		</section>
		
		{{#if image}}
			<figure>
				<img src="{{ image }}" />
			</figure>
		{{/if}}
		
		{{#if video}}
			<figure>
				<video height="100%" width="100%" controls="controls" preload="none">
					<source src="{{ video.video_path }}">
					<object width="320" height="240" type="application/x-shockwave-flash" data="public/js/mejs/flashmediaelement.swf">
				        <param name="movie" value="public/js/mejs/flashmediaelement.swf" />
				        <param name="flashvars" value="controls=true&file={{ video.video_path }}" />
				    </object>
				</video>
			</figure>
		{{/if}}
		
		<p>{{{ text }}}</p>
	</script>