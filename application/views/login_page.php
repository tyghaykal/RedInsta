<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form action="<?php echo base_url('process');?>" method="post" class="login100-form validate-form">
					<span class="login100-form-title p-b-34">
						Instagram Streams Login
					</span>
					
					<div class="wrap-input100 rs1-wrap-input100 validate-input m-b-20" data-validate="Type user name">
						<input id="first-name" class="input100" type="text" name="username" placeholder="User name">
						<span class="focus-input100"></span>
					</div>
					<div class="wrap-input100 rs2-wrap-input100 validate-input m-b-20" data-validate="Type password">
						<input class="input100" type="password" name="pass" placeholder="Password">
						<span class="focus-input100"></span>
					</div>
					
					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Sign in
						</button>
					</div>

					<?php
						if(isset($_SESSION['flash']['failed']) == true){
							$failed = $_SESSION['flash']['failed'];
							if(!empty($failed)){ ?>
							<div class="alert alert-danger m-t-10" role="alert">
								<ul>
									<li><?= $failed ?></li>
								</ul>
							</div>
							<?php
							}
						}
						
						
					?>

					
				</form>

				<div class="login100-more" style="background-image: url('assets/images/forest.jpg');"></div>
			</div>
		</div>
	</div>