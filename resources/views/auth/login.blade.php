@extends ("layouts/app")
@section ("title", "Login")

@section ("main")

    <div class="container" style="margin-top: 50px; margin-bottom: 50px;">
        <div class="row">
            <div class="offset-md-4 col-md-4">

                <h2 style="margin-bottom: 30px;">Login</h2>

                <form method="POST" enctype="multipart/form-data" onsubmit="doLogin()">
                    @csrf
                    <div class="form-group" style="margin-top: 20px; margin-bottom: 20px;">
                        <label>Enter email</label>
                        <input type="email" name="email" class="form-control" required />
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Enter password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">
                        Login
                        <i class="fa fa-spinner" id="loader" style="display: none;"></i>
                    </button>
                </form>

                <p style="margin-top: 10px;">
                    <a href="{{ url('/forgot-password') }}">Forgot password ?</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        async function doLogin() {
            event.preventDefault()

            const form = event.target
            const formData = new FormData(form)
            
            form.submit.setAttribute("disabled", "disabled")
            $("#loader").show()

            const response = await axios.post(
                baseUrl + "/api/login",
                formData
            )

            form.submit.removeAttribute("disabled")
            $("#loader").hide()

            if (response.data.status == "success") {
                // swal.fire("Login", response.data.message, "success")
                localStorage.setItem(accessTokenKey, response.data.access_token)
                window.location.href = baseUrl
                
                console.log('login+++')
            } else {
                swal.fire("Error", response.data.message, "error")
            }
        }
    </script>

@endsection