@extends ("layouts/app")
@section ("title", "Claim Company")

@section ("main")

	<input type="hidden" id="id" value="{{ $id }}" />

	<div style="display: contents;" id="claimCompanyApp">
		<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
			<div class="row" v-if="isLoading" style="padding: 25px;">
				<div class="col-md-12" style="text-align: center;">
					<div class="spinner-border">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>

			<template v-if="company != null">
				<div class="row">
					<div class="col-md-12">
						<nav style="--bs-breadcrumb-divider: '>';">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="/">Home</a></li>
								<li class="breadcrumb-item">Company</li>
								<li class="breadcrumb-item">Claim</li>
								<li class="breadcrumb-item" v-text="company.title || company.domain"></li>
							</ol>
						</nav>
					</div>
				</div>

				<div class="row" style="margin-top: 20px;">
					<div class="offset-md-4 col-md-3">
						<form v-on:submit.prevent="claimWebsite">
							<div class="form-group">
								<label>Website</label>
								<input type="text" name="domain" class="form-control" readonly v-bind:value="company.domain" />
							</div>

							<div class="form-group">
								<label>Verification code</label>
								<input type="text" name="code" class="form-control" required />
							</div>

							<input type="submit" class="btn btn-success btn-block"
								v-bind:disabled="claiming"
								v-bind:value="claiming ? 'Claiming...' : 'Claim'" />
						</form>
					</div>
				</div>
			</template>
		</div>
	</div>

	<script>
		const id = document.getElementById("id").value
		let claimCompanyApp = null

		function initApp() {
			claimCompanyApp = Vue.createApp({
				data() {
					return {
						isLoading: false,
						company: null,
						id: id,
						claiming: false
					}
				},

				methods: {
					async claimWebsite() {
						const self = this
						this.claiming = true
						try {
							const form = event.target
							const formData = new FormData(form)
							formData.append("id", this.id)
							const response = await axios.post(
								baseUrl + "/api/companies/verify-claim",
								formData,
								{
									headers: {
										Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
									}
								}
							)

							if (response.data.status == "success") {
								swal.fire("Claim Website", response.data.message, "success")
									.then(function () {
										window.location.href = baseUrl + "/company/" + self.id
									})
							} else {
								swal.fire("Error", response.data.message, "error")
							}
						} catch (exp) {
							swal.fire("Error", exp.message, "error")
						} finally {
							this.claiming = false
						}
					}
				},

				async mounted() {
					this.isLoading = true
					try {
						const formData = new FormData()
						formData.append("domain", id)

						const response = await axios.post(
							baseUrl + "/api/companies/find",
							formData,
							{
								headers: {
									Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
								}
							}
						)

						if (response.data.status == "success") {
							this.company = response.data.company
						} else {
							swal.fire("Error", response.data.message, "error")
						}
					} catch (exp) {
						swal.fire("Error", exp.message, "error")
					} finally {
						this.isLoading = false
					}
				},
			}).mount("#claimCompanyApp")
		}
	</script>

@endsection