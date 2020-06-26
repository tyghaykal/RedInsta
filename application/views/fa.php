<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form action="<?php echo base_url('process_fa');?>" method="post" class="login100-form validate-form">
					<span class="login100-form-title p-b-34">
						2-Factor Auth
					</span>
					
					<div class="wrap-input100 rs3-wrap-input100 validate-input m-b-20" data-validate="Type user name">
						<input id="first-name" class="input100" type="text" name="twofa-security-code" placeholder="2-Factor Auth">
						<span class="focus-input100"></span>
					</div>
					<div class="wrap-input100 rs3-wrap-input100 validate-input m-b-20" data-validate="Type password">
						<input class="input100 d-none" type="password" name="2faid" value="<?= $_SESSION['faid'] ?>" readonly>
						<span class="focus-input100"></span>
					</div>
					
					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Send FA Code
						</button>
					</div>

					<div class="container-login100-form-btn">
						<a href="<?php echo base_url('login');?>" class="login100-form-btn">
							Cancel
						</a>
					</div>

					
				</form>

				<div class="login100-more" style="background-image: url('assets/images/forest.jpg');"></div>
			</div>
		</div>
	</div>