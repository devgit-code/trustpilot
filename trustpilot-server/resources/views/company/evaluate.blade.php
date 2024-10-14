@extends ("layouts/app")
@section ("title", "Evaluate Company")

@section ("main")

    <input type="hidden" id="id" value="{{ $id }}" />
    <input type="hidden" id="stars" value="{{ $stars }}" />

    <div style="display: block;" id="evaluateCompanyApp">
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
                    <div class="offset-md-3 col-md-1">
                        <img v-bind:src="company.screenshot" class="screenshot" style="width: 100%; object-fit: cover" />
                    </div>

                    <div class="col-md-5">
                        <p v-text="company.title || company.domain" style="font-weight: bold; margin-bottom: 0px;"></p>
                        <p v-text="company.domain"></p>
                    </div>
                </div>
            </template>
        </div>

        <div v-if="company != null" style="padding-top: 20px; padding-bottom: 50px; background-color: #fcfbf3;">
            <div class="container" style="">
                <div class="row">
                    <div class="offset-md-3 col-md-6">
                        <div class="card card-body" style="padding: 0px 25px;">
                            <h3>Rate your experience</h3>
                            <div class="stars">
                                <div v-for="i in 5" class="rating-stars"
                                    v-on:mouseenter="onclickStar(i)"
                                    v-on:click="onclickStar(i)"
                                    style="display: inline-block; cursor: pointer;">
                                    <i class="fa fa-star star"
                                        style="font-size: 30px;"></i>
                                </div>
                            </div>

                            <h4 style="margin-top: 30px;">Tell us more about your experience</h4>
                            <textarea v-model="review" class="form-control" rows="10"></textarea>

                            <h4 style="margin-top: 30px;">Give your review a title</h4>
                            <input type="text" style="padding-top: 25px; padding-bottom: 25px;" v-model="title" class="form-control" />

                            <button type="button" class="btn btn-primary btn-block"
                                id="btn-submit-review"
                                v-on:click="submitReview"
                                v-bind:disabled="submitting"
                                v-text="submitting ? 'Submitting...' : 'Submit review'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const id = document.getElementById("id").value
        const stars = parseInt(document.getElementById("stars").value)
        let evaluateCompanyApp = null

        function initEvaluateCompanyApp() {
            evaluateCompanyApp = Vue.createApp({

                data() {
                    return {
                        isLoading: false,
                        company: null,
                        profileImage: "",
                        user: null,
                        baseUrl: baseUrl,
                        onmouseenterStar: onmouseenterStar,
                        selectedStar: stars,
                        review: "",
                        title: "",
                        submitting: false
                    }
                },

                methods: {

                    async submitReview() {
                        this.submitting = true
                        try {
                            const formData = new FormData()
                            formData.append("id", id)
                            formData.append("ratings", this.selectedStar)
                            formData.append("title", this.title)
                            formData.append("review", this.review)
                            const response = await axios.post(
                                baseUrl + "/api/companies/review",
                                formData,
                                {
                                    headers: {
                                        "Authorization": "Bearer " + localStorage.getItem(accessTokenKey)
                                    }
                                }
                            )

                            if (response.data.status == "success") {
                                // swal.fire("Review posted", response.data.message, "success")
                                window.location.href = baseUrl + "/review/" + response.data.review_id
                            } else {
                                swal.fire("Error", response.data.message, "error")
                            }
                        } catch (exp) {
                            swal.fire("Error", exp.message, "error")
                        } finally {
                            this.isLoading = false
                        }
                        this.submitting = false
                    },

                    onclickStar(star) {
                        onmouseenterStar(star)
                        this.selectedStar = star
                    },

                    onProfileError() {
                        this.profileImage = '/public/img/user-placeholder.png'
                    },
                },

                async mounted() {
                    const self = this
                    setTimeout(function () {
                        self.onmouseenterStar(self.selectedStar)
                    }, 100)
                    this.isLoading = true

                    try {
                        const formData = new FormData()
                        formData.append("domain", id)
                        const response = await axios.post(
                            baseUrl + "/api/companies/find",
                            formData
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

                watch: {
                    user(to, from) {
                        if (to != null) {
                            this.profileImage = to.profileImage
                        }
                    }
                }
            }).mount("#evaluateCompanyApp")
        }

        initEvaluateCompanyApp()
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
        }
        #btn-submit-review:hover {
            color: black;
            background-color: #c2d5f7;
        }
        #btn-submit-review {
            margin-top: 20px; margin-bottom: 20px;
            border-radius: 50px;
            background-color: #205cd4;
            color: white;
            font-weight: bold;
            border: none;
            padding: 15px;
            font-size: 16px;
        }
    </style>

@endsection