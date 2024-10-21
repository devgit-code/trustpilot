@extends ("admin/layouts/app")
@section ("title", "Add Company")

@section ("main")

	<div class="pagetitle">
	    <h1>Add Company</h1>
	    <nav>
	      <ol class="breadcrumb">
	        <li class="breadcrumb-item">Companies</li>
	        <li class="breadcrumb-item active">Add</li>
	      </ol>
	    </nav>
	</div>

	<section class="section">
	    <div class="row">
			<div class="col-12">
				<div class="card">
		            <div class="card-body" style="padding-top: 20px;">
		                <form enctype="multipart/form-data" onsubmit="addCompany()" id="form-add">
		                    <div class="form-group">
		                        <label class="form-label">Logo</label>
		                        <input type="file" name="logo" class="form-control" required />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Title</label>
		                        <input type="text" name="title" class="form-control" required />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Description</label>
		                        <textarea name="description" class="form-control" required></textarea>
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Categories</label>
		                        <select name="categories" class="form-control" multiple>
		                        	@foreach ($categories as $category)
		                        		<option value="{{ $category->category }}">{{ $category->category }}</option>
		                        	@endforeach
		                        </select>
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Website (domain only)</label>
		                        <input type="text" name="domain" class="form-control" />
		                    </div>

		                    <h2>Contact information</h2>

		                    <div class="form-group">
		                        <label class="form-label">Phone</label>
		                        <input type="text" name="contact_phone" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Email</label>
		                        <input type="text" name="contact_email" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">City</label>
		                        <input type="text" name="contact_city" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Country</label>
		                        <input type="text" name="contact_country" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Address</label>
		                        <textarea name="contact_address" class="form-control"></textarea>
		                    </div>

		                    <h2>Social media</h2>

		                    <div class="form-group">
		                        <label class="form-label">Facebook</label>
		                        <input type="text" name="facebook" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Twitter</label>
		                        <input type="text" name="twitter" class="form-control" />
		                    </div>

		                    <div class="form-group">
		                        <label class="form-label">Instagram</label>
		                        <input type="text" name="instagram" class="form-control" />
		                    </div>

		                    <input type="submit" name="submit" class="btn btn-outline-primary btn-sm" value="Add"
		                    	style="margin-top: 20px;" />
		                </form>
		            </div>
		        </div>
			</div>
	  	</div>
	</section>

	<script>

	    // document.getElementById("loader").style.display = "none"

	    async function addCompany() {
	        event.preventDefault()

	        // const form = event.target
	        const form = document.getElementById("form-add")
	        form.submit.setAttribute("disabled", "disabled")

	        var formData = new FormData(form);
	        const selectedCategories = []
	        const options = form.categories.options
	        for (let a = 0; a < options.length; a++) {
	            if (options[a].selected) {
	                selectedCategories.push(options[a].getAttribute("value"))
	            }
	        }
	        formData.append("selected_categories", JSON.stringify(selectedCategories))

	        try {
				const response = await axios.post(
					baseUrl + "/api/admin/companies/add",
					formData,
					{
						headers: {
							Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
						}
					}
				)

				if (response.data.status == "success") {
					swal.fire("Add company", response.data.message, "success")
                    form.reset()
                    form.logo.value = null
				} else {
					swal.fire("Error", response.data.message, "error")
				}
			} catch (exp) {
				swal.fire("Error", exp.message, "error")
			} finally {
				form.submit.removeAttribute("disabled")
			}
	    }

	    window.addEventListener("load", function () {
	        $("textarea[name='description']").richText()
	    })
	</script>

@endsection