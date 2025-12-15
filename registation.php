<?php
	include('include/header.php');
?>


<!-- Title page -->
<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('img/background_banner_1.jpeg'); height='550' width='30%' ">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 text-center">
				<div class="breadcrumb_text">
					<h2>Registation</h2>
					<div class="breadcrumbs_option">
						<a href="index.php">Home</a>
						<span>Registation</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


	<!-- Content page -->
	<section class="bg0 p-t-104 p-b-116">
		<div class="container">
			<div class="flex-w flex-tr">
				<div class="size-210 bor10 p-lr-70 p-t-55 p-b-70 p-lr-15-lg w-full-md">
					 <form action="registation_process.php" method="post" class="bg-light p-5 contact-form">
                    	<div class="form-group">
                    	    <label for="c_fname" class="text-black">First Name</label>
                    	    <input type="text" class="form-control" name="fnm" placeholder="Your Name">
                    	</div>
						<div class="form-group">
                    	    <label for="c_fname" class="text-black">Middle Name</label>
                    	    <input type="text" class="form-control" name="mnm" placeholder="Your Name">
                    	</div>
						<div class="form-group">
                    	    <label for="c_fname" class="text-black">Last Name</label>
                    	    <input type="text" class="form-control" name="lnm" placeholder="Your Name">
                    	</div>
                    	<div class="form-group">
                    	    <label for="c_fname" class="text-black">Email </label>
                    	    <input type="email" class="form-control" name="email" placeholder="Email">
                    	</div>
                    	 <div class="form-group">
                    	    <label for="c_fname" class="text-black">Password </label>
                    	    <input type="password" class="form-control" name="pwd" placeholder="Password">
                    	</div>
                    	<div class="form-group">
                    	    <label for="c_fname" class="text-black">Re-type Password</label>
                    	    <input type="password" class="form-control" name="cpwd" placeholder="Re-type Password">
                    	</div>
                    	<div class="form-group">
                    	    <label for="c_fname" class="text-black">Mobile No </label>
                    	    <input type="text" class="form-control" name="mno" placeholder="Mobile No">
                    	</div>


                    	<div class="form-group">
                    	    <input type="submit" value="Submit" class="btn btn-primary py-3 px-5">
                    	</div>
                	</form>
				</div>

				<div class="size-210 bor10 flex-w flex-col-m p-lr-93 p-tb-30 p-lr-15-lg w-full-md">
					<div class="flex-w w-full p-b-42">
						<span class="fs-18 cl5 txt-center size-211">
							<span class="lnr lnr-map-marker"></span>
						</span>

						<div class="size-212 p-t-2">
							<span class="mtext-110 cl2">
								Address
							</span>

							<p class="stext-115 cl6 size-213 p-t-18">
								Coza Store Center 8th floor, 379 Hudson St, New York, NY 10018 US
							</p>
						</div>
					</div>

					<div class="flex-w w-full p-b-42">
						<span class="fs-18 cl5 txt-center size-211">
							<span class="lnr lnr-phone-handset"></span>
						</span>

						<div class="size-212 p-t-2">
							<span class="mtext-110 cl2">
								Lets Talk
							</span>

							<p class="stext-115 cl1 size-213 p-t-18">
								+1 800 1236879
							</p>
						</div>
					</div>

					<div class="flex-w w-full">
						<span class="fs-18 cl5 txt-center size-211">
							<span class="lnr lnr-envelope"></span>
						</span>

						<div class="size-212 p-t-2">
							<span class="mtext-110 cl2">
								Sale Support
							</span>

							<p class="stext-115 cl1 size-213 p-t-18">
								contact@example.com
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>	
	
	
	<!-- Map -->
	<div class="map">
		<div class="size-303" id="google_map" data-map-x="40.691446" data-map-y="-73.886787" data-pin="images/icons/pin.png" data-scrollwhell="0" data-draggable="1" data-zoom="11"></div>
	</div>

<?php
	include('include/footer.php')
?>