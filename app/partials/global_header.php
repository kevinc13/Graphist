	<div id="head_wrapper" role="menubar">
		<ul>
			<h1>
				<a href="<?php echo DOCUMENT_ROOT; ?>">Graphist</a>
			</h1>
			<li <?php print ($viewName == "Home")? 'class="active_page"' : "";?>>
				<a href="home"><i class="icon-home"></i></a>
				<span>Home</span>
			</li>
			<li <?php print ($viewName == "Stacks")? 'class="active_page"' : "";?>>
				<a href="stacks"><i class="icon-stack"></i></a>
				<span>Stacks</span>
			</li>
			<li <?php print ($viewName == "Hubs")? 'class="active_page"' : "";?>>
				<a href="hubs"><i class="icon-hubs"></i></a>
				<span>Hubs</span>
			</li>
			<li <?php print ($viewName == "Explore")? 'class="active_page"' : "";?>>
				<a href="explore"><i class="icon-explore"></i></a>
				<span>Explore</span>
			</li>
			<!--
<li <?php print ($viewName == "Events")? 'class="active_page"' : "";?>>
				<a href="events"><i class="icon-calendar"></i></a>
				<span>Events</span>
			</li>
-->
			<li>
				<a href="profile/<?php echo $_SESSION['username'];?>"><i class="icon-user"></i></a>
				<span>Profile</span>
			</li>
			<div class="nav-right">
				<form action="search" method="get" id="menu_search">
					<input type="search" name="q" id="query_str" placeholder="Search Graphist" autocomplete="off" />
					<i class="glass_search_icon icon-search">
						<!-- <img src="public/img/ic_search.png" height="14" width="14"> -->
					</i>
					<span class="caret">
						<span class="inner"></span>
					</span>
				</form>
				<!--
<li>
					<a role="button" class="btn" id="conversations_trigger" data-sidebar-opened="false">
						<img src="public/img/ic_messaging.png" height="14" width="14" alt="Messaging">
					</a>
				</li>
-->
				<li data-role="notification_dropdown">
					<a class="notification_trigger" data-sidebar-opened="false">
						<?php print $numNotifications; ?>
					</a>
					<span class="caret">
						<span class="inner"></span>
					</span>
				</li>
				<li>
				    <a id="global_post_snip_trig">
				        <i class="icon-pencil"></i>
				    </a>
				</li>
				<li data-role="user_menu_dropdown">
					<a class="user_menu_trigger" data-toggle="false">
						<img src="<?php echo $_SESSION["profile_info"]["profile_image"];?>" class="profile_img" alt="Profile Image">
						<span class="down-caret"></i>
					</a>
					<span class="caret">
						<span class="inner"></span>
					</span>	
					<ul class="user_menu_dropdown pull_right">
						<li>
							<a href="profile/<?php echo $_SESSION['username'];?>">Profile</a>
						</li>
						<li>
							<a href="settings">Settings</a>
						</li>
						<li class="dropdown_divider"></li>
						<li>
							<a href="logout">Sign Out</a>
						</li>
					</ul>
				</li>
			</div>
		</ul>
	</div>
	<!-- End Head Wrapper -->