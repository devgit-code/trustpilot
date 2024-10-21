@extends ("admin/layouts/app")
@section ("title", "Edit Company")

@section ("main")

    <div class="pagetitle">
        <h1>Edit Company</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item">Companies</li>
            <li class="breadcrumb-item">Edit</li>
            <li class="breadcrumb-item active">{{ $company->id }}</li>
          </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="padding-top: 20px;">
                        <form enctype="multipart/form-data" onsubmit="editCompany()" id="form-edit">
                            <input type="hidden" name="id" value="{{ $company->id }}" />

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Logo</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="logo" />
                                </div>
                            </div>

                            @if ($company->screenshot)
                                <div class="row mb-3">
                                    <div class="offset-2 col-10">
                                        <img src="{{ $company->screenshot }}" style="width: 100px;" />
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Title</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="title" value="{{ $company->title }}" required />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Description</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="description" required>{{ $company->description }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Categories</label>
                                <div class="col-sm-10">
                                    <select name="categories" class="form-control" multiple>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->category }}" {{ in_array($category->category, $company->categories) ? "selected" : "" }}>{{ $category->category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Website (domain only)</label>
                                <div class="col-sm-10">
                                    <input type="text" name="domain" class="form-control" value="{{ $company->domain }}" />
                                </div>
                            </div>

                            <h2>Contact information</h2>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Phone</label>
                                <div class="col-sm-10">
                                    <input type="text" name="contact_phone" value="{{ $company->contact_phone }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">E-mail</label>
                                <div class="col-sm-10">
                                    <input type="text" name="contact_email" value="{{ $company->contact_email }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">City</label>
                                <div class="col-sm-10">
                                    <input type="text" name="contact_city" value="{{ $company->contact_city }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Country</label>
                                <div class="col-sm-10">
                                    <input type="text" name="contact_country" value="{{ $company->contact_country }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Address</label>
                                <div class="col-sm-10">
                                    <input type="text" name="contact_address" value="{{ $company->contact_address }}" class="form-control" />
                                </div>
                            </div>

                            <h2>Social media</h2>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Facebook</label>
                                <div class="col-sm-10">
                                    <input type="text" name="facebook" value="{{ $company->facebook }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Twitter</label>
                                <div class="col-sm-10">
                                    <input type="text" name="twitter" value="{{ $company->twitter }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Instagram</label>
                                <div class="col-sm-10">
                                    <input type="text" name="instagram" value="{{ $company->instagram }}" class="form-control" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="offset-sm-2 col-sm-10">
                                    <input type="submit" name="submit" class="btn btn-outline-warning btn-sm" value="Update" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>

        // document.getElementById("loader").style.display = "none"

        async function editCompany() {
            event.preventDefault()

            // const form = event.target
            const form = document.getElementById("form-edit")
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
                    baseUrl + "/api/admin/companies/update",
                    formData,
                    {
                        headers: {
                            Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                        }
                    }
                )

                if (response.data.status == "success") {
                    swal.fire("Edit company", response.data.message, "success")
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