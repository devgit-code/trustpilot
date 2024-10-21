@extends ("admin/layouts/app")
@section ("title", "Add Category")

@section ("main")

  <div class="pagetitle">
    <h1>Add Category</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Categories</li>
        <li class="breadcrumb-item active">Add</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body" style="padding-top: 20px;">

            <form onsubmit="addCategory()" id="form-add-category">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Category</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="category" required />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="description" required />
                </div>
              </div>

              <input type="submit" name="submit" class="btn btn-outline-primary" value="Add" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>

    async function addCategory() {
      event.preventDefault()

      const form = event.target
      const formData = new FormData(form)
      form.submit.setAttribute("disabled", "disabled")

      try {
        const response = await axios.post(
          baseUrl + "/api/admin/categories/add",
          formData,
          {
            headers: {
              Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
            }
          }
        )

        if (response.data.status == "success") {
          swal.fire("Add Category", response.data.message, "success")
        } else {
          swal.fire("Error", response.data.message, "error")
        }
      } catch (exp) {
        swal.fire("Error", exp.message, "error")
      } finally {
        form.submit.removeAttribute("disabled")
      }
    }
  </script>

@endsection