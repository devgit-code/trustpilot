@extends ("admin/layouts/app")
@section ("title", "Categories")

@section ("main")

	<div class="pagetitle">
    <div style="display: flex;">
      <h1>Categories</h1>
      <a href="{{ url('/admin/categories/add') }}" class="btn btn-outline-primary btn-sm"
        style="margin-left: 15px;">Add Category</a>
    </div>

    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Categories</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <table class="table table-bordered table-responsive">
          <thead>
            <tr>
              <th>Category</th>
              <th>Description</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            @foreach ($categories as $category)
              <tr data-id="{{ $category->id }}">
                <td>{{ $category->category }}</td>
                <td>{{ $category->description }}</td>
                <td>
                  <button type="button" class="btn btn-outline-danger" onclick="deleteCategory('{{ $category->id }}')">Delete</button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <script>
    function deleteCategory(id) {
      swal.fire({
        title: "Delete category: #" + id,
        text: "Are you sure you want to delete this category ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then(async function (result) {
        if (result.isConfirmed) {
          try {
            const formData = new FormData()
            formData.append("id", id)

            const response = await axios.post(
              baseUrl + "/api/admin/categories/delete",
              formData,
              {
                headers: {
                  Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                }
              }
            )

            if (response.data.status == "success") {
              document.querySelector("tr[data-id='" + id + "']")?.remove()
            } else {
              swal.fire("Error", response.data.message, "error")
            }
          } catch (exp) {
            swal.fire("Error", exp.message, "error")
          }
        }
      })
    }
  </script>

@endsection