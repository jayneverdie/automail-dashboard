<?php $user_data = getUserData(); ?>

<aside class="main-sidebar">

	<section class="sidebar">
		<?php if ( $user_data["result"] === true ): ?>
		<form action="#" method="get" class="sidebar-form">
			<div class="input-group">
				<input type="text" name="q" class="form-control" placeholder="Search...">
				<span class="input-group-btn">
					<button type="submit" name="search" id="search-btn" class="btn btn-flat">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
		</form>
		<?php endif; ?>
			
		<?php if ( $user_data["result"] === true ): ?>
			<?php if(userCan('admin_panel')) $this->insert("includes/admin-sidebar"); ?>
			<?php echo getSidebarMenu("Menu"); ?>
		<?php else: ?>
		<!-- login / register -->
		<ul class="sidebar-menu" data-widget="tree">
			<li>
				<a href="/login">
					<i class="fa fa-sign-in" aria-hidden="true"></i> <span>Login</span>
				</a>
			</li>
			<!-- <li>
				<a href="/register">
					<i class="fa fa-user-plus" aria-hidden="true"></i> <span>Register</span>
				</a>
			</li> -->
		</ul>
		<?php endif; ?>
		<!-- /.sidebar-menu -->
	</section>
	<!-- /.sidebar -->
</aside>