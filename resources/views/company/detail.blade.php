@extends ("layouts/app")
@section ("title", "Company Detail")

@section ("main")

    <input type="hidden" id="domain" value="{{ $domain }}" />

    <div style="display: contents;" id="companyDetailApp">
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
                                <li class="breadcrumb-item active" v-text="company?.title || company?.domain"></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <img v-bind:src="company.screenshot" style="width: 100%; height: 200px; object-fit: cover" />
                    </div>

                    <div class="col-md-5 margin-top-mobile-20">
                        <h1 v-text="company.title || company.domain" style="margin-top: 0px;"></h1>
                        <p style="color: gray;">
                            Reviews <span v-text="company.reviews"></span>
                            <template v-if="relativeReview(company.ratings) != ''">
                                <span style="margin-right: 10px;
                                    font-size: 20px;
                                    position: relative;
                                    bottom: 2px;">.</span>
                                <span v-text="relativeReview(company.ratings)"></span>
                            </template>
                        </p>

                        <div class="stars">
                            <i v-for="i in 5" v-bind:class="'fa fa-star star ' + (i > company.ratings ? 'initial' : company.starColor)"
                                style="font-size: 30px;"></i>

                            <span v-if="company.ratings > 0" v-text="company.ratings" style="font-size: 20px;
                                margin-left: 10px;
                                position: relative;
                                bottom: 5px;
                                color: gray;"></span>
                        </div>
                    </div>

                    <div class="col-md-4 margin-top-mobile-20">
                        <a v-bind:href="company.domain_with_server" target="_blank" class="link-visit">
                            <div class="row">
                                <div class="col-md-10 col-xs-10">
                                    <i class="fa fa-external-link"></i> &nbsp;
                                    <span class="domain" v-text="company.domain"></span>
                                    <p>Visit this website</p>
                                </div>

                                <div class="col-md-1 col-xs-1">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <div v-if="company != null" style="padding-top: 20px; padding-bottom: 50px; background-color: #fcfbf3;">
            <div class="container" style="">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row" v-if="!hasReviewed">
                            <div class="col-md-12">
                                <div class="card card-body border-radius">
                                    <div class="row write-review">
                                        <div class="col-md-1 col-xs-3">
                                            <img v-bind:src="profileImage" v-on:error="onProfileError"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;" />
                                        </div>

                                        <div class="col-md-5 col-xs-9">
                                            <template v-if="user != null">
                                                <p v-text="user.name" class="user"></p>
                                                <a v-bind:href="baseUrl + '/evaluate/' + company.id" class="link-review">
                                                    Write a review
                                                </a>
                                            </template>

                                            <template v-else>
                                                <p style="position: relative;
                                                    top: 50%;
                                                    transform: translateY(-50%);">Please login to post a review.</p>
                                            </template>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="stars">
                                                <div v-for="i in 5" v-bind:key="i"
                                                    class="rating-stars"
                                                    v-on:mouseenter="onmouseenterStar(i)"
                                                    v-on:click="onclickStar(i)">
                                                    <i class="fa fa-star star"
                                                        v-on:click="onclickStar(i)"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template v-for="review in reviews">
                            <review-component v-bind:review="review" 
                                v-bind:is-my-claimed="isMyClaimed"
                                v-bind:is-company-detail-page="true" />
                        </template>

                        <button v-if="showLoadMoreBtn"
                            style="margin-top: 10px;" 
                            class="btn btn-primary btn-block"
                            v-on:click="loadMoreReviews"
                            v-bind:disabled="loadingMore"
                            v-text="loadingMore ? 'Loading...' : 'Load more'"></button>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-body" style="margin-top: 10px;">
                            <p style="font-weight: bold; font-size: 20px;">Company activity</p>
                            <p v-if="company.is_claimed">
                                <i class="fa fa-check"></i>&nbsp;
                                Claimed profile

                                <a v-if="isMyClaimed" v-bind:href="baseUrl + '/company/edit/' + company.id">Edit</a>
                            </p>

                            <template v-else>
                                <p>
                                    <i class="fa fa-exclamation-triangle"></i>&nbsp;
                                    Unclaimed profile
                                </p>

                                <button type="button" class="btn btn-info"
                                    data-toggle="modal" data-target="#claimModal"
                                    style="margin-top: 10px;">Claim</button>
                            </template>
                        </div>

                        <div class="card card-body" v-if="company.description" style="margin-top: 10px;">
                            <p style="font-weight: bold; font-size: 20px;">Description</p>

                            <div v-html="company.description"></div>
                        </div>

                        <div class="card card-body" style="margin-top: 10px;" v-if="company && isMyClaimed">
                            <p style="font-weight: bold; font-size: 20px;">TrustPilot Widget</p>

                            <div style="border: 1px solid gray;
                                border-radius: 5px;
                                background-color: #eaeaea;
                                padding: 5px 8px;
                                word-break: break-all;">

                                &lt;div id="trustpilot-root">&lt;/div><br /><br />

                                &lt;script><br />
                                    (function () {<br />
                                        const script = document.createElement("script")<br />
                                        script.src = "<span v-text="webURL"></span>/companies/render-widget/<span v-text="company._id"></span>"<br />
                                        document.body.appendChild(script)<br />
                                    })()<br />
                                &lt;/script>
                            </div>
                        </div>

                        <div class="card card-body" v-if="company.aboutUs" style="margin-top: 10px;">
                            <p style="font-weight: bold; font-size: 20px;">About Us</p>

                            <div v-html="company.aboutUs" id="about-us-container"></div>
                        </div>

                        <div class="card card-body" v-if="company.contactUs" style="margin-top: 10px;">
                            <h2 style="margin-top: 0px;">Contact Us</h2>

                            <div v-html="company.contactUs"></div>
                        </div>

                        <div class="card card-body" v-if="company.contactEmail || company.contactPhone || company.contactAddress" style="margin-top: 10px;">
                            <p style="font-weight: bold; font-size: 20px;">Contact Us</p>

                            <p v-if="company.contactEmail">Email: <span v-text="company.contactEmail"></span></p>
                            <p v-if="company.contactPhone">Phone: <span v-text="company.contactPhone"></span></p>
                            <p v-if="company.contactAddress">Address: <span v-text="company.contactAddress"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="claimModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="justify-content: left;">
                        <h5 class="modal-title">Claim <span v-text="domain"></span></h5>
                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-12">
                                <p>Enter your email where you want to receive the verification code. You must have access to any of <span v-text="domain"></span> email address.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5 input-email">
                                <input type="text" class="form-control" placeholder="info" v-model="email" />
                            </div>

                            <div class="col-md-1 at-icon">
                                <span style="font-size: 20px;">@</span>
                            </div>

                            <div class="col-md-5 input-domain">
                                <input type="text" class="form-control" v-bind:value="domain" disabled />
                            </div>
                        </div>

                        <div class="row" v-if="email != ''" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <p>You will receive verification code at <b v-text="email + '@' + domain"></b></p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary"
                            v-on:click="claimWebsite"
                            v-bind:disabled="claiming"
                            v-text="claiming ? 'Loading...' : 'Claim website'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const domain = document.getElementById("domain").value
        let companyDetailApp = null

        function initCompanyDetailApp() {
            companyDetailApp = Vue.createApp({

                components: {
                    ReviewComponent
                },

                data() {
                    return {
                        isLoading: false,
                        company: null,
                        reviews: [],
                        profileImage: "",
                        baseUrl: baseUrl,
                        relativeReview: relativeReview,
                        onmouseenterStar: onmouseenterStar,
                        hasReviewed: false,
                        page: 1,
                        user: null,
                        loadingMore: false,
                        showLoadMoreBtn: false,
                        domain: domain,
                        email: "",
                        claiming: false,
                        isMyClaimed: false
                    }
                },

                methods: {

                    async claimWebsite() {
                        if (this.email == "") {
                            swal.fire("Error", "Please enter email.", "error")
                            return
                        }

                        if (this.company == null) {
                            swal.fire("Error", "Company not exists.", "error")
                            return
                        }

                        const self = this
                        this.claiming = true

                        try {
                            const formData = new FormData()
                            formData.append("id", this.company.id)
                            formData.append("email", this.email + "@" + this.domain)

                            const response = await axios.post(
                                baseUrl + "/api/companies/claim",
                                formData,
                                {
                                    headers: {
                                        Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                                    }
                                }
                            )

                            if (response.data.status == "success") {
                                swal.fire("Claim", response.data.message, "success")
                                    .then(function () {
                                        window.location.href = baseUrl + "/company/claim/" + self.company.id
                                    })
                            } else {
                                swal.fire("Error", response.data.message, "error")
                            }
                        } catch (exp) {
                            swal.fire("Error", exp.message, "error")
                        } finally {
                            this.claiming = false
                        }
                    },

                    async loadMoreReviews() {
                        this.page++
                        this.loadingMore = true

                        try {
                            const formData = new FormData()
                            formData.append("domain", domain)
                            formData.append("page", this.page)
                            const response = await axios.post(
                                baseUrl + "/api/companies/reviews",
                                formData
                            )

                            if (response.data.status == "success") {
                                const reviews = response.data.reviews
                                for (let a = 0; a < reviews.length; a++) {
                                    this.reviews.push(reviews[a])
                                }
                                if (reviews.length <= 0) {
                                    this.showLoadMoreBtn = false
                                }
                            } else {
                                swal.fire("Error", response.data.message, "error")
                            }
                        } catch (exp) {
                            swal.fire("Error", exp.message, "error")
                        } finally {
                            this.loadingMore = false
                        }
                    },

                    onclickStar(star) {
                        console.log(this.company)
                        
                        if (this.company != null) {
                            window.location.href = baseUrl + "/evaluate/" + this.company.id + "/" + star
                        }
                    },

                    onProfileError() {
                        this.profileImage = '/public/img/user-placeholder.png'
                    }
                },

                async mounted() {
                    this.isLoading = true

                    try {
                        const formData = new FormData()
                        formData.append("domain", domain)
                        formData.append("page", this.page)
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
                            this.reviews = response.data.reviews
                            this.hasReviewed = response.data.has_reviewed
                            this.isMyClaimed = response.data.is_my_claimed

                            if (this.reviews.length > 0) {
                                this.showLoadMoreBtn = true
                            }
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
            }).mount("#companyDetailApp")
        }

        initCompanyDetailApp()
    </script>

    <style>
        @media only screen and (max-width: 767px) {
            #claimModal .at-icon {
                text-align: center;
            }
        }
        @media only screen and (min-width: 768px) {
            #claimModal .input-domain {
                padding-left: 0px;
            }
            #claimModal .at-icon span {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
            }
            #claimModal .at-icon {
                padding-left: 12px;
                padding-right: 0px;
            }
            #claimModal .input-email {
                padding-right: 0px;
            }
        }
        /*#claimModal .btn-primary:hover {
            color: #286090;
            background-color: #fdfdfd;
        }*/
        #claimModal .btn-secondary:hover {
            background-color: #fdfdfd !important;
        }
        .write-review .stars {
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            float: right;
        }
        .rating-stars .star {
            font-size: 20px;
        }
        .rating-stars {
            display: inline-block;
        }
        .write-review .star {
            cursor: pointer;
        }
        .write-review .link-review {
            color: #205cd4;
        }
        .write-review .user {
            margin-bottom: 0px;
            font-weight: bold;
        }
        .border-radius {
            border-radius: 10px;
        }
        .link-visit .domain {
            font-weight: bold;
            font-size: 18px;
        }
        .link-visit p {
            margin-top: 10px;
            margin-bottom: 0px;
            color: gray;
        }
        .link-visit:hover p {
            color: black;
        }
        .link-visit:hover .fa-external-link,
        .link-visit:hover .domain,
        .link-visit:hover .fa-arrow-right {
            color: black;
        }
        .link-visit .fa-arrow-up-right-from-square,
        .link-visit .domain,
        .link-visit .fa-arrow-right {
            color: #205cd4;
        }
        .link-visit:hover {
            background-color: #c2d5f7;
            border-color: #c2d5f7;
        }
        .link-visit {
            display: block;
            border: 1px solid #205cd4;
            padding: 15px 35px;
            border-radius: 10px;
        }
        .breadcrumb a {
            color: black;
        }
        .breadcrumb {
            background: none !important;
        }
    </style>

@endsection