<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}"> 
        <title>@yield("title", "Eniyi.co")</title>

        <!-- <link rel="stylesheet" href="{{ asset('/css/all.css') }}" /> -->
        <link rel="stylesheet" href="{{ asset('/css/bootstrap.css') }}" />
        <!-- <link rel="stylesheet" href="{{ asset('/css/fontawesome.css') }}" /> -->
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}" />
		<link rel="icon" type="image/ico" href="{{ asset('/img/favicon.ico') }}">		
        <script src="{{ asset('/js/jquery.js') }}"></script>
        <script src="{{ asset('/js/bootstrap.js') }}"></script>
        <script src="{{ asset('/ckeditor/ckeditor.js') }}"></script>
        <script src="{{ asset('/js/vue.global.js') }}"></script>

        <script src="{{ asset('/js/react.development.js') }}"></script>
        <script src="{{ asset('/js/react-dom.development.js') }}"></script>
        <script src="{{ asset('/js/babel.min.js') }}"></script>
        <script src="{{ asset('/js/axios.min.js') }}"></script>
        <script src="{{ asset('/js/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('/js/fontawesome.js') }}"></script>
    </head>

    <body>
    	<input type="hidden" id="baseUrl" value="{{ url('/') }}" />
    	<input type="hidden" id="appName" value="{{ config('config.app_name') }}" />

    	<script>
    		const baseUrl = document.getElementById("baseUrl").value
    		const appName = document.getElementById("appName").value
    	</script>
        
        <script src="{{ asset('/js/script.js?v=' . time()) }}"></script>

        <nav class="navbar navbar-expand-lg navbar-light navbar-inverse" id="headerApp">
          <div class="container-fluid">
		  
            <a class="navbar-brand d-flex align-items-center " href="{{ url('/') }}"><img alt="Eniyi.co logo" width="151" height="35" src="{{ asset('/img/eniyi-logo-b.png')}}"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"
                style="background-color: white;">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto mb-2 mb-lg-0 pull-right">
                <li class="nav-item" v-if="user == null">
                    <a v-bind:class="'nav-link' + (path == '/login' ? ' active' : '')" href="{{ url('/login') }}">Login</a>
                  </li>
        
                  <li class="nav-item" v-if="user == null">
                    <a v-bind:class="'nav-link' + (path == '/register' ? ' active' : '')" href="{{ url('/register') }}">Register</a>
                  </li>

                  <li class="nav-item">
                      <ul>
                          <li class="nav-item dropdown" v-if="user != null">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img v-bind:src="user.profile_image ? user.profile_image : 'img/default-user.png'" style="width: 40px; height: 40px;
                                    object-fit: cover; border-radius: 50%; margin-right: 10px;" />

                                <!-- <span v-text="user.name"></span> -->
                            </a>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="/profile">Profile</a></li>
                              <li><a class="dropdown-item" v-on:click="doLogout" href="javascript:void(0);">Logout</a></li>
                            </ul>
                          </li>
                      </ul>
                  </li>
                  
                    
              </ul>
            </div>
          </div>
        </nav>

        <script>
            let headerApp = null
            function initHeaderApp() {
                headerApp = Vue.createApp({

                    data() {
                      return {
                        user: window.user,
                        path: window.location.href
                      }
                    },

                    methods: {

                      async doLogout () {
                            const response = await axios.post(
                                baseUrl + "/api/logout",
                                null,
                                {
                                  headers: {
                                    Authorization: "Bearer " + localStorage.getItem(accessTokenKey)
                                  }
                                }
                            )

                            if (response.data.status == "success") {
                                // remove access token from local storage
                                localStorage.removeItem(accessTokenKey)

                                globalState.setState({
                                    user: null
                                })
                                window.location.href = "/"
                                // window.location.reload()
                            } else {
                                swal.fire("Error", response.data.message, "error")
                            }
                        }
                    },
                }).mount("#headerApp")
            }
        </script>

        <style>
            @media only screen and (max-width: 767px) {
                .margin-top-mobile-20 {
                    margin-top: 20px;
                }
                .margin-top-mobile {
                    margin-top: 50px;
                }
            }
            .dropdown-toggle:hover {
                text-decoration: none !important;
            }
            #headerApp .nav-link,
            #headerApp .navbar-brand {
                color: white !important;
            }
            #headerApp .navbar-brand {
                padding: 0;
            }
            /* for mobile */
            @media only screen and (max-width: 767px) {
                #navbarSupportedContent ul {
                    margin-left: auto !important;
                }
            }
            /* for desktop */
            @media only screen and (min-width: 768px) {
                #headerApp {
                    padding-left: 150px;
                    padding-right: 150px;
                }
            }
            #headerApp {
                background-color: #1c1c1c !important;
                border-radius: 0px;
                margin-bottom: 0px;
            }
            html, body { overflow-x: hidden; }
            /*.container {
                padding-left: 100px !important;
                padding-right: 100px !important;
                margin-left: 0px !important;
                margin-right: 0px !important;
            }*/
        </style>
        
        @yield("main")

        <footer>
            <div class="container" style="padding-top: 50px; padding-bottom: 50px;">
                <div class="row">
                    <div class="col-md-12">
                        <a class="brand" href="/">Eniyi.co</a>
                    </div>
                </div>

                <div class="row" style="margin-top: 50px; margin-bottom: 20px;">
                    <div class="col-md-3 col-xs-6">
                        <p class="title">About</p>

                        <ul>
                            <li><a href="/about">About us</a></li>
                            <li><a href="/jobs">Jobs</a></li>
                            <li><a href="/contact">Contact</a></li>
                            <li><a href="/blog">Blog</a></li>
                            <li><a href="/how-we-work">How Eniyi.co works</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3 col-xs-6">
                        <p class="title">Community</p>

                        <ul>
                            <li><a href="/trust">Trust in reviews</a></li>
                            <li><a href="/help-centre">Help centre</a></li>
                            <li><a href="/login">Login</a></li>
                            <li><a href="/register">Sign up</a></li>
                            <li class="social-media">
                                <a href="https://www.facebook.com/" target="_blank"><i class="fa fa-facebook"></i></a>
                                <a href="https://www.twitter.com/" target="_blank"><i class="fa fa-twitter"></i></a>
                                <a href="https://www.instagram.com/" target="_blank"><i class="fa fa-instagram"></i></a>
                                <a href="https://www.linkedin.com/" target="_blank"><i class="fa fa-linkedin"></i></a>
                                <a href="https://www.youtube.com/" target="_blank"><i class="fa fa-youtube"></i></a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-3 col-xs-6 margin-top-mobile">
                        <p class="title">Businesses</p>

                        <ul>
                            <li><a href="/products">Products</a></li>
                            <li><a href="/pricing">Plans &amp; pricing</a></li>
                            <li><a href="/business-blog">Blog for business</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3 col-xs-6 margin-top-mobile">
                        <p class="title" style="margin-bottom: 20px;">Choose language</p>

                        <!-- <link rel="stylesheet" href="//cdn.tutorialjinni.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
                        <link rel="stylesheet" href="//cdn.tutorialjinni.com/bootstrap-select/1.12.4/css/bootstrap-select.min.css" />
                        <link rel="stylesheet" href="//g.tutorialjinni.com/mojoaxel/bootstrap-select-country/dist/css/bootstrap-select-country.min.css" /> -->

                        <link rel="stylesheet" href="{{ asset('/countrypicker/bootstrap.min.css') }}" />
                        <link rel="stylesheet" href="{{ asset('/countrypicker/bootstrap-select.min.css') }}" />
                        <link rel="stylesheet" href="{{ asset('/countrypicker/bootstrap-select-country.min.css') }}" />

                        <select class="selectpicker countrypicker form-control" data-flag="true"></select>

                        <script src="{{ asset('/countrypicker/jquery.min.js') }}"></script>
                        <script src="{{ asset('/countrypicker/bootstrap.min.js') }}"></script>
                        <script src="{{ asset('/countrypicker/bootstrap-select.min.js') }}"></script>
                        <script src="{{ asset('/countrypicker/bootstrap-select-country.min.js') }}"></script>

                        <!-- <script src="//cdn.tutorialjinni.com/jquery/3.6.1/jquery.min.js"></script>
                        <script src="//cdn.tutorialjinni.com/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
                        <script src="//cdn.tutorialjinni.com/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
                        <script src="//g.tutorialjinni.com/mojoaxel/bootstrap-select-country/dist/js/bootstrap-select-country.min.js"></script> -->
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="links-legal">
                            <li><a href="{{ url('legal') }}">Legal</a></li>
                            <li><a href="/privacy-policy">Privacy policy</a></li>
                            <li><a href="/terms-conditions">Terms &amp; conditions</a></li>
                            <li><a href="/reviewers-guidelines">Guidelines for reviewers</a></li>
                            <li><a href="/cookie-preference">Cookie preference</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row" style="margin-top: 50px;">
                    <div class="col-md-12">
                        <p style="color: #9fa0a0;">&copy; {{ now()->year }} Eniyi.co, Tüm hakları saklıdır.</p>
                    </div>
                </div>
            </div>
        </footer>

        <style>
            @media only screen and (min-width: 768px) {
                footer .links-legal li {
                    display: inline-block;
                }
            }
            footer ul {
                list-style-type: none;
            }
            footer .links-legal li {
                margin-right: 50px;
            }
            footer .countrypicker ul li {
                margin-top: 10px !important;
            }
            footer .countrypicker a {
                color: black !important;
            }
            footer .social-media a {
                margin-right: 10px;
                font-size: 20px;
            }
            footer ul li {
                margin-top: 20px;
            }
            footer ul {
                padding-left: 0px;
            }
            footer .title {
                color: #9fa0a0;
                margin-bottom: 0px;
            }
            footer .brand:hover {
                color: initial;
            }
            footer .brand {
                font-size: 30px;
            }
            footer a:hover {
                text-decoration: underline;
            }
            footer a {
                color: white !important;
                text-decoration: none;
            }
            footer {
                background-color: #1c1c1c;
            }
        </style>

        <div id="chat-app"></div>
        <script type="text/babel" src="{{ asset('/components/Chat.js?v=' . time()) }}"></script>
        <link rel="stylesheet" href="{{ asset('/css/chat.css') }}" />

        <script defer>
            async function onInit() {
                const accessToken = localStorage.getItem(accessTokenKey)
                if (accessToken) {
                    try {
                        const response = await axios.post(
                            baseUrl + "/api/me",
                            null,
                            {
                                headers: {
                                    Authorization: "Bearer " + accessToken,
                                }
                            }
                        )

                        if (response.data.status == "success") {
                            window.user = response.data.user

                            globalState.setState({
                                user: window.user || globalState.user
                            })

                            const newMessages = response.data.new_messages

                //             if (newMessages > 0) {
                // console.log("here====", document.getElementById("message-notification-badge"))
                //                 document.getElementById("message-notification-badge").innerHTML = newMessages
                //             }

                            if (typeof initApp !== "undefined") {
                                initApp()
                            }

                            if (typeof claimCompanyApp !== "undefined") {
                                claimCompanyApp.user = user
                            }

                            if (typeof companyDetailApp !== "undefined") {
                                companyDetailApp.user = user
                            }

                            if (typeof evaluateCompanyApp !== "undefined") {
                                evaluateCompanyApp.user = user
                            }

                            if (typeof reviewDetailApp !== "undefined") {
                                reviewDetailApp.user = user
                            }
                        } else {
                            swal.fire("Error", response.data.message, "error")
                        }
                    } catch (exp) {
                        console.log(exp)
                        // swal.fire("Error", exp.message, "error")
                    }
                    console.log('me+++++');

                }

                initHeaderApp()
            }

            onInit()
        </script>
    </body>
</html>