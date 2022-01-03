<?php $user_data = getUserData(); ?>

<!-- Main Header -->
<header class="main-header">
	<!-- Logo -->
	<a href="<?php echo $this->e($home_url); ?>" class="logo">
		<span class="logo-mini"><img class="logo-lg center" src="/assets/images/logo.png" alt="" width="30"></span>
		<span class="logo-lg"><img class="logo-lg center" src="/assets/images/logo.png" alt="" width="30"></span>
	</a>

	<!-- Header Navbar -->
	<nav class="navbar navbar-static-top" role="navigation">
		<!-- Sidebar toggle button-->
		<a href="javascript:void(0);" class="sidebar-toggle" data-toggle="push-menu" role="button">
			<span class="sr-only">Toggle navigation</span>
		</a> 
		<?php if ( $user_data["result"] === true ): ?>
		<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<!-- <li><a href="#"><i class="fa fa-cog" aria-hidden="true"></i> Setting</a></li> -->
				<li class="dropdown user user-menu">
					<!-- Menu Toggle Button -->
					<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
						<!-- The user image in the navbar-->
						<img src="/assets/images/avatar.png" class="user-image" alt="User Image">
						
						
						<!-- hidden-xs hides the username on small devices so only the image appears. -->
						<span class="hidden-xs"><?php echo $user_data['data']['user_data']->username; ?></span>
					</a>
					<ul class="dropdown-menu">
						<!-- The user image in the menu -->
						<li class="user-header">

							

							<p>
								<?php echo $user_data['data']['user_data']->fullname; ?>
							</p>
						</li>
						<!-- Menu Footer-->
						<li class="user-footer">
							<a href="/user/profile" class="btn btn-default btn-block btn-flat"><i class="fa fa-address-card" aria-hidden="true"></i> Profile</a>
							<a href="/user/change_password" class="btn btn-default btn-block btn-flat"><i class="fa fa-key" aria-hidden="true"></i> Change Password</a>
							<a href="/logout" class="btn btn-default btn-block btn-flat"><i class="fa fa-sign-out" aria-hidden="true"></i> Sign out</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<?php endif; ?>
	</nav>
</header>