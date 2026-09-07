
<style>
.tbl_img{width:40px;}
</style>

<div class="pcoded-content">
	<div class="pcoded-inner-content">
		<div class="main-body">
			<div class="page-wrapper">
				<!-- Page-header start -->
				<div class="page-header">
					<div class="row align-items-end">
						<div class="col-lg-8">
							<div class="page-header-title">
								<div class="d-inline">
									<h4><?php echo $page_title; ?></h4>
									<span><?php echo $page_sub_title; ?></span>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="page-header-breadcrumb">
								<ul class="breadcrumb-title">
									<li class="breadcrumb-item">
										<a href="#"> <i class="fa fa-cogs"></i> </a>
									</li>
									<li class="breadcrumb-item"><a href="#!">Admin</a>
									</li>
									<li class="breadcrumb-item"><a href="#"> Users</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- Page-header end -->
				
				
				<div class="page-body">
					<div class="row"  id="print_div">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Website Visitor</h5>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="feather icon-maximize full-card print_hide"></i></li>
										<li><i class="feather icon-minus minimize-card print_hide"></i></li>
										<li><i class="feather icon-trash-2 close-card print_hide"></i></li>
									</ul>
								</div>
								
								<hr>

							</div>
							<div class="card-block">
								<div class="dt-responsive table-responsive">
									
									<canvas id="visitorChart" width="400" height="200"></canvas>
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script>
        var ctx = document.getElementById('visitorChart').getContext('2d');
        var visitorChart = new Chart(ctx, {
            type: 'line', // Change to 'bar' for a bar graph
            data: {
                labels: <?php echo json_encode($dates); ?>, // Dates array
                datasets: [{
                    label: 'Number of Visitors',
                    data: <?php echo json_encode($counts); ?>, // Visitor counts
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
