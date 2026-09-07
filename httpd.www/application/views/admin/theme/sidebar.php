					<nav class="pcoded-navbar">
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="pcoded-navigatio-lavel">Navigation</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="<?php if($page_name == 'dashboard'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/dashboard">
										<span class="pcoded-micon"><i class="feather icon-home"></i></span>
										<span class="pcoded-mtext">Dashboard</span>
									</a>
								</li> 
								<!--
								<li class="pcoded-hasmenu <?php if($page_name == 'users' || $page_name == 'users'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-image"></i></span>
                                        <span class="pcoded-mtext">Users</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'users'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/users/Active">
                                                <span class="">Active Users</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'users'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/users/Inactive">
                                                <span class="">Inactive Users</span>
                                            </a>
                                        </li>
										
                                    </ul>
                                </li>
								-->
								
								<!--
								<li class="<?php if($page_name == 'categories'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/categories">
										<span class="pcoded-micon"><i class="fa fa-list"></i></span>
										<span class="pcoded-mtext">Categories</span>
									</a>
								</li> 
								 
								 
								<li class="<?php if($page_name == 'sub_categories'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/sub_categories">
										<span class="pcoded-micon"><i class="fa fa-list"></i></span>
										<span class="pcoded-mtext">Sub Categories</span>
									</a>
								</li> 
								 -->
								<li class="pcoded-hasmenu <?php if($page_name == 'categories' || $page_name == 'sub_categories' || $page_name == 'main_categories'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Categories Details</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'main_categories'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/main_categories">
                                                <span class="">Main Categories</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'categories'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/categories">
                                                <span class="">Categories</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'sub_categories'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/sub_categories">
                                                <span class="">Sub Categories</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
								
								<li class="pcoded-hasmenu <?php if($page_name == 'blogs' || $page_name == 'blogs_add'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-edit"></i></span>
                                        <span class="pcoded-mtext">Blogs</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'blogs_add'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/blogs_add">
                                                <span class="">Add New Blog</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'blogs'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/blogs">
                                                <span class="">Published BLogs</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'blogs'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/unpublished_blogs">
                                                <span class="">Un Published Blogs</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
								
								<li class="pcoded-hasmenu <?php if($page_name == 'shows' || $page_name == 'shows_add_ispring'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-image"></i></span>
                                        <span class="pcoded-mtext">Shows</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'shows_add_ispring'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/shows_add">
                                                <span class="">Add New Show</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'shows'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/shows">
                                                <span class=""> Shows Details</span>
                                            </a>
                                        </li>
										
                                    </ul>
                                </li>
								
								<li class="pcoded-hasmenu <?php if($page_name == 'fake_shows' || $page_name == 'fake_shows_add'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-ban"></i></span>
                                        <span class="pcoded-mtext">Fake Shows</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'fake_shows_add'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/fake_shows_add">
                                                <span class="">Add New Fake Show</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'fake_shows'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/fake_shows">
                                                <span class=""> Fake Shows Details</span>
                                            </a>
                                        </li>
										
                                    </ul>
                                </li>
								
								<li class="<?php if($page_name == 'users'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/users">
										<span class="pcoded-micon"><i class="fa fa-users"></i></span>
										<span class="pcoded-mtext">Customers</span>
									</a>
								</li> 
								
								<li class="pcoded-hasmenu <?php if($page_name == 'admin_email' || $page_name == 'create_admin_email'){ echo 'active pcoded-trigger'; } ?> ">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-edit"></i></span>
                                        <span class="pcoded-mtext">Admin Emails</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="<?php if($page_name == 'create_admin_email'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/create_admin_email">
                                                <span class="">Add New Email</span>
                                            </a>
                                        </li>
                                        <li class="<?php if($page_name == 'admin_email'){ echo 'active'; } ?>">
                                            <a href="<?php echo base_url(); ?>admin/admin_emails">
                                                <span class="">Manage Emails</span>
                                            </a>
                                        </li>
                                       
                                    </ul>
                                </li>
							
								
								<li class="<?php if($page_name == 'visitors'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/visitors">
										<span class="pcoded-micon"><i class="fa fa-users"></i></span>
										<span class="pcoded-mtext">Website Visitors</span>
									</a>
								</li> 
								
								<li class="<?php if($page_name == 'myprofile'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/myprofile">
										<span class="pcoded-micon"><i class="fa fa-user"></i></span>
										<span class="pcoded-mtext">My Profile</span>
									</a>
								</li>
								
								<li class="<?php if($page_name == 'system_setting'){ echo 'active'; } ?>">
									<a href="<?php echo base_url(); ?>admin/system_setting">
										<span class="pcoded-micon"><i class="fa fa-cogs"></i></span>
										<span class="pcoded-mtext">System Setting</span>
									</a>
								</li>
                            </ul>
                        </div>
                    </nav>