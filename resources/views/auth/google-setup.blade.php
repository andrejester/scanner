<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Authenticator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .google2fa_token-input {
            width: 3rem;
            height: 3rem;
            font-size: 1.25rem;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.2s;
        }

        .google2fa_token-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>

<body class="flex min-h-screen items-center justify-center bg-gray-50">

    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">
        <!-- Image -->
        <img src="{{ asset('assets/img/undraw_access-account_aydp.svg') }}" alt="Google Authenticator"
            class="mx-auto mb-4 w-32">

        <!-- Title -->
        <h2 class="mb-2 text-2xl font-semibold text-gray-800">Google Authenticator</h2>
        <p class="mb-6 text-sm text-gray-500">
            One-Time Password sent to your registered email and phone number.
        </p>

        {{-- ⏳ Countdown Timer --}}
        <div class="mb-6 text-sm text-gray-700">
            Google will expire in
            <span id="countdown" class="font-semibold text-blue-600">02:00</span>
        </div>

        <!-- Google Form -->
        <form method="POST" action="{{ route('2fa.google.verify') }}">
            @csrf

            {{-- Google Inputs --}}
            <div class="mb-6 flex justify-center gap-3">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" maxlength="1"
                        class="google2fa_token-input h-12 w-12 rounded-lg border border-gray-300 text-center text-lg transition focus:border-blue-600 focus:ring focus:ring-blue-200"
                        name="google2fa_token_digits[]" required>
                @endfor
            </div>

            @error('google2fa_token')
                <p class="mb-2 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Resend Link --}}
            <p class="mb-6 text-sm text-gray-600">
                Didn’t receive the Google?
                <a href="#" class="font-medium text-blue-600 hover:underline" id="resend-link">
                    Resend Google
                </a>
                {{-- {{ route('google2fa_token.resend') }} --}}
            </p>

            {{-- Submit + Back --}}
            <div class="space-y-3">
                <button type="submit"
                    class="w-full rounded-lg bg-blue-600 py-3 font-semibold text-white transition duration-150 hover:bg-blue-700">
                    VERIFY
                </button>

                <a href="{{ route('log-adm') }}"
                    class="block w-full rounded-lg bg-gray-100 py-3 font-semibold text-gray-700 transition duration-150 hover:bg-gray-200">
                    Login Ulang
                </a>
            </div>
        </form>
    </div>

    <!-- JS: Auto focus logic -->
    <script>
        const inputs = document.querySelectorAll(".google2fa_token-input");

        inputs.forEach((input, index) => {
            input.addEventListener("input", (e) => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Gabungkan input Google menjadi satu sebelum submit
        document.querySelector("form").addEventListener("submit", function(e) {
            const google2fa_token = Array.from(inputs).map(i => i.value).join("");
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "google2fa_token";
            hidden.value = google2fa_token;
            this.appendChild(hidden);
        });

        // Countdown Timer (2 minutes)
        let countdown = 5 * 60; // 5 menit = 300 detik
        const countdownEl = document.getElementById("countdown");
        const verifyBtn = document.getElementById("verify-btn");
        const resendLink = document.getElementById("resend-link");

        const timer = setInterval(() => {
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (countdown <= 0) {
                clearInterval(timer);
                countdownEl.textContent = "Expired";
                verifyBtn.disabled = true;
                verifyBtn.classList.add("opacity-50", "cursor-not-allowed");
                resendLink.classList.remove("pointer-events-none", "text-gray-400");
            }

            countdown--;
        }, 1000);

        // Disable resend until expired
        resendLink.classList.add("pointer-events-none", "text-gray-400");
    </script>

</body>

</html>
