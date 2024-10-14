@extends ("layouts/app")
@section ("title", "Edit Company")

@section ("main")

    <input type="hidden" id="id" value="{{ $id }}" />

    <div style="display: contents;" id="editCompanyApp">
        <div class="container" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="row" v-if="isLoading" style="padding: 25px;">
                <div class="col-md-12" style="text-align: center;">
                    <div class="spinner-border">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <template v-if="company != null">
                <div class="row">
                    <div class="offset-md-3 col-md-6">

                        <h3 v-text="company.title"></h3>
                        <p style="color: gray;" v-text="company.domain"></p>

                        <h2 style="margin-top: 20px; margin-bottom: 20px;">Edit company</h2>

                        <form v-on:submit.prevent="editCompany" enctype="multipart/form-data" id="form-edit-company">
                            <div class="form-group">
                                <label class="form-label">Screenshot</label>
                                <input type="file" accept="image/*" name="screenshot" />
                                <img v-bind:src="company.screenshot" style="width: 200px; margin-top: 20px;" />
                            </div>

                            <div class="form-group">
                                <label class="form-label">About us</label>
                                <textarea class="form-control" id="aboutUs"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="description"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact us</label>
                                <input type="text" class="form-control" name="contact_us" />
                            </div>

                            <input type="submit" name="submit" class="btn btn-primary btn-sm" v-bind:disabled="editing" v-bind:value="editing ? 'Editing...' : 'Edit'" />
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        const id = document.getElementById("id").value
        
        function initApp() {
            Vue.createApp({

                data() {
                    return {
                        isLoading: false,
                        company: null,
                        profileImage: "",
                        user: null,
                        baseUrl: baseUrl,
                        review: "",
                        title: "",
                        editing: false,
                        isMyClaimed: false
                    }
                },

                methods: {

                    async editCompany() {
                        this.editing = true
                        try {
                            const form = event.target
                            const formData = new FormData(form)
                            formData.append("id", id)

                            const aboutUs = CKEDITOR.instances.aboutUs.getData()
                            const description = CKEDITOR.instances.description.getData()

                            formData.append("about_us", aboutUs)
                            formData.append("description", description)

                            const response = await axios.post(
                                baseUrl + "/api/companies/update",
                                formData,
                                {
                                    headers: {
                                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                                    }
                                }
                            )

                            if (response.data.status == "success") {
                                swal.fire("Updated", response.data.message, "success")
                            } else {
                                swal.fire("Error", response.data.message, "error")
                            }
                        } catch (exp) {
                            swal.fire("Error", exp.message, "error")
                        }
                        this.editing = false
                    },

                    onProfileError() {
                        this.profileImage = '/public/img/user-placeholder.png'
                    },
                },

                async mounted() {
                    const self = this
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
                            this.isMyClaimed = response.data.is_my_claimed

                            setTimeout(function () {
                                const form = document.getElementById("form-edit-company")
                                form.aboutUs.value = self.company.about_us || ""
                                form.description.value = self.company.description || ""
                                form.contact_us.value = self.company.contact_us || ""

                                // $("textarea").richText()
                                CKEDITOR.replace('aboutUs')
                                CKEDITOR.replace('description')
                            }, 500)
                        } else {
                            swal.fire("Error", response.data.message, "error")
                        }
                    } catch (exp) {
                        console.log(exp)
                        swal.fire("Error", exp.message, "error")
                    } finally {
                        this.isLoading = false
                    }
                },

                watch: {
                    user(to, from) {
                        if (to != null) {
                            this.profileImage = to.profileImage
                        }
                    }
                }
            }).mount("#editCompanyApp")
        }
    </script>

@endsection