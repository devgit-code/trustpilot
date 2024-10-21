@extends ("layouts/app")
@section ("title", "Home")

@section ("main")

    <div style="display: contents;" id="indexApp">
        <div style="background-color: #4ecca3; height: 400px;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12" style="margin-top:70px; text-align: center;"     >
                        <h2 style="font-weight: bold;
                            margin-top: 50px;
                            margin-bottom: 20px;
                            font-size: 30px;">Firmaları, Markaları, ürünleri incele, <br />Puan ver, Yorum yaz, Değerlendir <br />
                            En iyi ve en güvenilir markaları belirle.</h2>

                        <div class="input-group mb-3" style="margin-top: 50px; margin-bottom: 0px !important;">
                            <input type="text" v-bind:class="'form-control ' + (searchResuls.length > 0 ? ' round-top-right' : '')"
                                placeholder="Domain" id="search-input"
                                v-on:keyup="onkeyupSearchCompany" v-model="search"
                                autocomplete="off" />
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary input-group-text"
                                    id="btn-search" 
                                    v-on:click="doSearch">Search</button>
                            </div>
                        </div>

                        <div v-if="searchResuls.length > 0">
                            <div class="row" v-for="d in searchResuls">
                                <div class="col-md-12">
                                    <div class="card card-body">
                                        <div class="row">
                                            <a v-bind:href="baseUrl + '/company/' + d.domain" style="display: contents; color: initial;">
                                                <div class="col-md-2">
                                                    <img v-bind:src="d.screenshot" style="width: 50px; height: 50px; object-fit: cover" />
                                                </div>

                                                <div class="col-md-6">
                                                    <p v-text="d.title" style="margin-bottom: 0px; font-weight: bold;"></p>
                                                    <p style="margin-bottom: 0px;
                                                        position: relative;">
                                                        <span v-text="d.domain" style="margin-right: 10px;"></span>
                                                        <span style="margin-right: 10px;
                                                            font-size: 20px;
                                                            position: relative;
                                                            bottom: 2px;">.</span>
                                                        <span v-text="d.reviews"></span> reviews
                                                    </p>
                                                </div>

                                                <div class="col-md-4" v-if="d.ratings > 0">
                                                    <div class="stars">
                                                        <i v-for="i in 5" v-bind:class="'fa fa-star star ' + (i > d.ratings ? 'initial' : d.starColor)"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg">
            <div class="container">
                <div class="row">
                    <div class="col-md-6" >
                        <h2 style="font-weight: bold;">Tüm Kategoriler</h2>
                    </div>

                    <div class="col-md-6">
                        <a href="./categories" class="btn btn-primary btn-sm" id="btn-view-all-categories">View all</a>
                    </div>
                </div>
            </div>

            <div class="container">

                <div class="row" id="row-categories">
                    @foreach ($categories as $category)
                        <div class="col-md-3" style="margin-bottom: 20px;">
                            <a href="{{ url('/categories/' . $category->category) }}">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="{{ $category->icon }}"></i>
                                        <span>{{ $category->category }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                @if (count($reviews) > 0)
                    <div class="row" style="margin-bottom: 50px;">
                        <div class="col-md-12">
                            <h3 class="text-center" style="font-weight: bold;">Recent Reviews</h3>
                        </div>
                    </div>
                @endif

            </div>

            @if (count($reviews) > 0)
                <div class="container" style="padding-left: 10px !important;
                    padding-right: 20px !important;
                    max-width: 100%;">
                    <div class="row" id="row-reviews">

                        @foreach ($reviews as $review)
                            <div class="col-md-3" style="margin-bottom: 20px;">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-3">
                                                <img src="{{ $review->user->profile_image }}" />
                                            </div>

                                            <div class="col-9">
                                                <div class="stars">
                                                    @php
                                                        $color = $review->color;
                                                    @endphp

                                                    @for ($a = 1; $a <= 5; $a++)
                                                        @if ($a > $review->ratings)
                                                            @php
                                                                $color = "initial";
                                                            @endphp
                                                        @endif

                                                        <i class="fa fa-star star {{ $color }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row review">
                                            <div class="col-md-12">
                                                <p class="user">
                                                    <span>{{ $review->user->name }}</span>
                                                    reviewed
                                                    <span style="cursor: pointer;"
                                                        onclick="window.location.href = baseUrl + '/company/{{ $review->company->domain }}'">{{ $review->company->domain }}</span>
                                                </p>

                                                <p class="review-content">
                                                    "{{ $review->review }}"
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endif
        </div>

        <div id="section-about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2 style="font-weight: bold;">En güvenilir firma ve marka rehberi<i class="fa fa-star"></i></h2>

                        <p>Eniyi.co sadece firma rehberi değildir. Firmaların ve markalarının, ürünlerinin puanlandığı ve kullanıcı yorumlarıyla değerlendirildiği bir özgür platformdur. Siz de başkalarının daha iyi seçimler yapmasına yardımcı olmak ve markaları daha iyi performans göstermeye teşvik etmek için deneyimlerinizi paylaşın.</p>

                        <a href="/about" class="btn btn-info about">Nasıl Yapıyoruz?</a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $review = count($reviews) == 0 ? null : $reviews[0];
        @endphp

        @if ($review != null)
            <div class="bg" id="your-stories">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h4 style="color: #696a6a; font-weight: bold;">Your stories</h4>
                            <h2 style="font-weight: bold;">Each review has a personal story</h2>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 50px;">
                        <div class="col-6 review" style="background-color: white; margin-top: 0px;">
                            <div class="stars">
                                @php
                                    $color = $review->color;
                                @endphp

                                @for ($a = 1; $a <= 5; $a++)
                                    @if ($a > $review->ratings)
                                        @php
                                            $color = "initial";
                                        @endphp
                                    @endif

                                    <i class="fa fa-star star {{ $color }}" style="font-size: 20px;"></i>
                                @endfor
                            </div>

                            <h2 style="font-weight: bold; margin-top: 20px; margin-bottom: 20px;">{{ $review->review }}</h2>

                            <p class="user" style="margin-bottom: 0px;">
                                <span>{{ $review->user->name }}</span>
                                experienced
                                <span style="cursor: pointer;" onclick="window.location.href = baseUrl + '/company/{{ $review->company->domain }}'">{{ $review->company->domain }}</span>
                            </p>
                        </div>

                        <div class="col-6 img">
                            <img src="{{ $review->user->profile_image }}"
                                style="width: 100%; height: 400px; object-fit: cover;" />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        Vue.createApp({
            data() {
                return {
                    callbackSearchCompany: null,
                    search: "",
                    searchResuls: [],
                    baseUrl: baseUrl
                }
            },

            methods: {

                onkeyupSearchCompany() {
                    const self = this
                    clearInterval(this.callbackSearchCompany)

                    this.callbackSearchCompany = setTimeout(async function () {
                        if (self.search != "") {
                            const formData = new FormData()
                            formData.append("q", self.search)

                            const response = await axios.post(
                                baseUrl + "/api/companies/search",
                                formData
                            )

                            if (response.data.status == "success") {
                                self.searchResuls = response.data.companies
                            }
                        } else {
                            self.searchResuls = []
                        }
                    }, 500)
                },

                doSearch() {
                    const button = event.target
                    const query = document.getElementById("search-input").value
                    window.location.href = baseUrl + "/company/" + query
                }
            }
        }).mount("#indexApp")       
    </script>

    <style>
        #btn-search:hover {
            background-color: #c2d5f7;
            color: black;
            border-color: #c2d5f7;
        }
        #btn-search {
            border-radius: 50px;
            padding: 10px 40px;
            position: absolute;
            right: 7px;
            font-size: 12px;
            font-weight: bold;
            top: 3px;
            z-index: 1000;
            background-color: #205cd4;
            color: white;
        }
        .round-top-right {
            border-top-left-radius: 30px !important;
            border-top-right-radius: 30px !important;
            border-bottom-left-radius: 0px !important;
            border-bottom-right-radius: 0px !important;
        }
        #search-input:focus {
            border-bottom: none !important;
            box-shadow: none !important;
        }
        #search-input {
            padding: 23px 20px;
            border-radius: 50px;
            border: none;
        }
        #section-about .about:hover {
            background-color: #009760;
            border-color: #009760;
        }
        #section-about .about {
            background-color: transparent;
            border-color: white;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: bold;
        }
        #section-about .fa-star {
            color: #04da8d;
            position: relative;
            left: 5px;
            top: 10px;
            transform: rotate(45deg);
        }
        #section-about h2,
        #section-about p,
        #section-about a {
            color: white;
        }
        #section-about p {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        #your-stories div.img {
            padding-left: 0px;
            padding-right: 0px;
        }
        /* for mobile */
        @media only screen and (max-width: 767px) {
            #your-stories div.review {
                padding-top: 50px;
            }
        }
        /* for desktop */
        @media only screen and (min-width: 768px) {
            #section-about .container {
                padding-left: 200px !important;
                padding-right: 200px !important;
            }
            #your-stories div.review {
                padding: 50px;
            }
        }
        #section-about {
            background-color: #022a1c;
            padding-top: 50px;
            padding-bottom: 50px;
        }
        #row-reviews .stars {
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }
        .review .user span {
            color: black;
        }
        .review .user {
            font-weight: bold;
            color: gray;
            margin-bottom: 10px;
        }
        .review p {
            margin-bottom: 0px;
        }
        .review {
            margin-top: 20px;
        }
        #row-reviews img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .bg {
            background-color: #f1f1e8 !important;
            padding-top: 50px;
            padding-bottom: 50px;
        }
        @keyframes hover-category {
            from {
                top: 0px;
            }
            to {
                top: 5px;
            }
        }
        #row-categories .card:hover {
            background-color: #fafafc;
            position: relative;
            cursor: pointer;
            box-shadow: 5px 5px 10px #bcbcbc;
            animation-name: hover-category;
            animation-duration: 0.5s;
            animation-fill-mode: forwards;
        }
        #row-categories a span {
            margin-left: 20px;
            font-size: 15px;
        }
        #row-categories a {
            color: black;
            text-decoration: none;
        }
        #row-categories .card {
            padding: 10px 0px;
            border-radius: 5px;
        }
        #row-categories {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        #btn-view-all-categories {
            float: right;
            background-color: #d8e4fa;
            color: #205cd4;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
        }
    </style>

@endsection