@extends ("layouts/app")
@section ("title", "Category " . $category)

@section ("main")

	<input type="hidden" id="title" value="{{ $category }}" />

	<div style="display: contents;" id="categoryApp">
		<div class="container" style="margin-bottom: 50px;">
			<div class="row" style="margin-top: 20px;">
				<div class="col-md-12">
					<nav style="--bs-breadcrumb-divider: '>';">
						<ol class="breadcrumb" style="background-color: white !important;">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item" v-if="title">Category</li>
							<li class="breadcrumb-item active" v-text="title || 'Companies'"></li>
						</ol>
					</nav>
				</div>
			</div>

			<div class="row" v-if="title">
				<div class="col-md-12">
					<h1 class="text-center" style="font-weight: bold;">
						Best in
						<span v-text="title"></span>
					</h1>
				</div>
			</div>

			<div class="row" v-if="loading">
				<div class="col-md-12 text-center">
					<div class="spinner-border" style="width: 5rem; height: 5rem;">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>

			<div class="row" v-else-if="companies.length == 0" style="margin-top: 50px;">
				<div class="col-md-12">
					<p class="text-center">No results found.</p>
				</div>
			</div>

			<div class="row single-company" style="margin-top: 20px;" v-for="company in companies" v-bind:key="'company-' + company._id"
				v-on:click="gotoCompany(company.domain)">
				<div class="col-md-12">
					<div class="card card-body">
						<div class="row">
							<div class="col-md-3">
								<img v-bind:src="company.screenshot" style="width: 100px; height: 100px; object-fit: cover;" />
							</div>

							<div class="col-md-9">
								<p v-text="company.title || company.domain" style="font-weight: bold; font-size: 20px;"></p>
								<div class="stars">
									<i v-for="i in 5" v-bind:class="'fa fa-star star ' + (i > company.ratings ? 'initial' : company.starColor)"></i>
									TrustScore <span v-text="company.ratings"></span>
									|
									<span v-text="company.reviews"></span> reviews
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		const title = document.getElementById("title").value

		Vue.createApp({
			data() {
				return {
					loading: false,
					title: title,
					companies: []
				}
			},

			methods: {
				gotoCompany(domain) {
					window.location.href = baseUrl + "/company/" + domain
				}
			},

			async mounted() {
				this.loading = true
				try {
					const formData = new FormData()
					formData.append("category", title)

					const response = await axios.post(
						baseUrl + "/api/companies/fetch-by-category",
						formData
					)

					if (response.data.status == "success") {
						this.companies = response.data.companies
					} else {
						swal.fire("Error", response.data.message, "error")
					}
				} catch (exp) {
					console.log(exp)
				} finally {
					this.loading = false
				}
			}
		}).mount("#categoryApp")
	</script>

	<style>
		@keyframes hover-category {
			from {
				top: 0px;
			}
			to {
				top: 5px;
			}
		}
		.single-company .card:hover {
			background-color: #fafafc;
			position: relative;
			cursor: pointer;
			box-shadow: 5px 5px 10px #bcbcbc;
			animation-name: hover-category;
			animation-duration: 0.5s;
			animation-fill-mode: forwards;
		}
	</style>

@endsection