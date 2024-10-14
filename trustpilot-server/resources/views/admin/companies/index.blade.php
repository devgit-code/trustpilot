@extends ("admin/layouts/app")
@section ("title", "Companies")

@section ("main")

	<div class="content">
	    <div class="container">
	        <div class="page-title">
	            <h3>Companies</h3>
	        </div>
	        <div class="box box-primary">
	            <div class="box-body">

	                <a href="{{ url('/admin/companies/add') }}" class="btn btn-sm btn-outline-primary float-right"><i class="fas fa-user-shield"></i> Add</a>

	                <table width="100%" class="table table-bordered" id="dataTables-companies" style="margin-top: 50px;">
	                    <thead>
	                        <tr>
	                            <th>Domain</th>
	                            <th>Title</th>
	                            <th>Screenshot</th>
	                            <th>Ratings</th>
	                            <th>Reviews</th>
	                            <th>Actions</th>
	                        </tr>
	                    </thead>

	                    <tbody>
	                    	@foreach ($companies as $company)
	                    		<tr data-id="{{ $company->id }}">
	                                <td>
	                                	<a href="{{ url('/company/' . $company->domain) }}">
	                                		{{ $company->domain }}
		                                </a>
		                            </td>

	                                <td>{{ $company->title }}</td>

	                                <td>
	                                	<img src="{{ $company->screenshot }}" class='img-fluid'
	                                    	style="width: 50px;" />
	                                </td>
	                                
	                                <td>{{ $company->ratings }}</td>
	                                <td>{{ $company->reviews }}</td>
	                                
	                                <td>
	                                    <a class="btn btn-warning btn-sm" href="{{ url('/admin/companies/edit/' . $company->id) }}">Edit</a>
	                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteCompany('{{ $company->id }}')">Delete</button>
	                                </td>
	                            </tr>
	                    	@endforeach
	                    </tbody>
	                </table>

	                <nav>
	                    <ul id="pagination" class="pagination"></ul>
	                </nav>
	            </div>
	        </div>
	    </div>
	</div>

	<script>

	    const urlSearchParams = new URLSearchParams(window.location.search)
	    let page = urlSearchParams.get("page") || 1

	    function deleteCompany(id) {
	        const node = event.target

	        swal.fire({
				title: "Delete Company",
				text: "All its reviews and ratings will be deleted as well",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Yes, delete it!"
			}).then(async function (result) {
				if (result.isConfirmed) {
					const formData = new FormData()
	            	formData.append("id", id)

	            	node.setAttribute("disabled", "disabled")

	            	try {
						const response = await axios.post(
							baseUrl + "/api/admin/companies/delete",
							formData,
							{
								headers: {
									Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
								}
							}
						)

						if (response.data.status == "success") {
							document.querySelector("tr[data-id='" + id + "']").remove()
						} else {
							swal.fire("Error", response.data.message, "error")
						}
					} catch (exp) {
						swal.fire("Error", exp.message, "error")
					} finally {
						node.removeAttribute("disabled")
					}
				}
			})
	    }
	</script>

@endsection