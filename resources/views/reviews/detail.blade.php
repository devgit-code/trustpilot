@extends ("layouts/app")
@section ("title", "Review Detail")

@section ("main")

	<input type="hidden" id="review" value="{{ json_encode($review) }}" />

	<div style="padding-top: 50px; padding-bottom: 50px; background-color: #fcfbf3;" id="reviewDetailApp">
		<div class="container">
			<div class="row" v-if="isLoading" style="padding: 25px;">
				<div class="col-md-12" style="text-align: center;">
					<div class="spinner-border">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>

			<div class="row" v-if="review == null">
				<div class="col-md-12">
					<p class="text-center" style="font-size: 20px;
						font-weight: bold;">Review not found.</p>
				</div>
			</div>

			<template v-if="review != null">
				<div class="row">
					<div class="offset-md-3 col-md-6">
						<h2 style="font-weight: bold;">Thanks for your review!</h2>

						<div class="card card-body" style="margin-bottom: 30px;">
							<div class="row">
								<div class="col-md-2">
									<img v-bind:src="baseUrl + '/companies/fetch-image/' + review.company_id" class="screenshot" style="width: 100%; object-fit: cover" />
								</div>

								<div class="col-md-10">
									<p v-text="review.company.title || review.company.domain" style="font-weight: bold; margin-bottom: 0px;
										position: relative;
									    top: 50%;
									    transform: translateY(-50%);"></p>
								</div>
							</div>
						</div>

						<div class="card card-body" style="padding-left: 0px;">
							<div class="container-fluid">
								<div class="row">
									<div class="col-md-2">
										<img v-bind:src="review.user.profile_image"
											style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;" />
									</div>

									<div class="col-md-10 review">
										<p style="margin-bottom: 0px; font-weight: bold;" v-text="review.user.name"></p>
										<p style="color: gray;">
											<span v-text="review.user.reviews"></span>&nbsp;
											reviews

											<span style="margin-left: 10px;">
												<i class="fa fa-map-marker"></i>
												<span v-text="review.user.location"></span>
											</span>
										</p>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<hr style="background-color: #b7b7b7;" />

										<div class="stars">
											<i v-for="i in 5" v-bind:class="'fa fa-star star ' + (i > review.ratings ? 'initial' : getStarColor(review.ratings))"
												style="font-size: 16px;"></i>
										</div>

										<h3 v-text="review.title"></h3>
										<p v-text="review.review"></p>
										<p>
											<b>Date of experience: </b>
											<span v-text="new Date(review.created_at + ' UTC')"></span>
										</p>

										<template v-if="user != null && review.user.id == user.id">
											<hr style="background-color: #b7b7b7;" />

											<button type="button" id="btn-delete" v-on:click="deleteReview">
												<i class="fa fa-trash"></i>
												<span>Delete</span>
											</button>
										</template>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</div>
	</div>

	<script>
		let review = document.getElementById("review").value
		review = JSON.parse(review)
		let reviewDetailApp = null

		function initReviewDetailApp() {
			reviewDetailApp = Vue.createApp({

				data() {
					return {
						isLoading: false,
						review: review,
						profileImage: "",
						user: null,
						baseUrl: baseUrl,
						getStarColor: getStarColor
					}
				},

				methods: {

					deleteReview() {
						const self = this
						const button = event.currentTarget

						swal.fire({
							title: "Are you sure you want to delete this review ?",
							showDenyButton: true,
							confirmButtonText: "Delete Review",
							denyButtonText: `Don't Delete`
						}).then(async function (result) {
							if (result.isConfirmed) {

								button.setAttribute("disabled", "disabled")
								const originalHtml = button.querySelector("span").innerHTML
								button.querySelector("span").innerHTML = "Loading..."

								try {
									const formData = new FormData()
									formData.append("id", self.review.id)
									const response = await axios.post(
										baseUrl + "/api/reviews/delete",
										formData,
										{
											headers: {
												Authorization: "Bearer " + localStorage.getItem(accessTokenKey) 
											}
										}
									)

									if (response.data.status == "success") {
										self.review = null
									} else {
										swal.fire("Error", response.data.message, "error")
									}
								} catch (exp) {
									swal.fire("Error", exp.message, "error")
								} finally {
									button.removeAttribute("disabled")
									button.querySelector("span").innerHTML = originalHtml
								}
							}
						})
					},

					onProfileError() {
						this.profileImage = '/public/img/user-placeholder.png'
					}
				},

				watch: {
					user(to, from) {
						if (to != null) {
							this.profileImage = to.profileImage
						}
					}
				}
			}).mount("#reviewDetailApp")
		}

		initReviewDetailApp()
	</script>

	<style>
		/* for mobile  */
		@media only screen and (max-width: 767px) {
			.screenshot {
				height: 200px;
			}
		}
		/* for desktop  */
		@media only screen and (min-width: 768px) {
			.screenshot {
				height: 50px;
			}
			.review {
				padding-left: 0px;
			}
		}
		#btn-delete span {
			margin-left: 5px;
		}
		#btn-delete {
			color: gray;
			text-decoration: none;
			border: none;
			background: none;
		}
	</style>

@endsection