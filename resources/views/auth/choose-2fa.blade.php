<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Verifikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb'
                    }
                }
            }
        }
    </script>
</head>

<body class="flex min-h-screen items-center justify-center bg-gray-50 transition-colors duration-300 dark:bg-gray-900">

    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg transition duration-300 dark:bg-gray-800">
        <!-- Gambar -->
        <img src="{{ asset('assets/img/undraw_two-factor-authentication_8tds.svg') }}" alt="Two Factor Authentication"
            class="mx-auto mb-4 w-48">

        <!-- Judul -->
        <h2 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-gray-100">
            Pilih Metode Verifikasi
        </h2>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">
            Silakan pilih salah satu metode untuk verifikasi dua langkah Anda.
        </p>

        <!-- Form -->
        <form method="POST" action="{{ route('2fa.choose.post') }}">
            @csrf

            <!-- Pilihan dalam dua kolom -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Google -->
                <label
                    class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-6 transition hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:bg-gray-700">
                    <input type="radio" name="method" value="google" class="peer hidden" required>

                    <div class="flex flex-col items-center gap-3">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                            <!-- Google Authenticator Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.6" stroke="currentColor"
                                class="h-8 w-8 text-blue-600 dark:text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 2.25a9.75 9.75 0 1 1-9.75 9.75A9.75 9.75 0 0 1 12 2.25Zm0 4.5v4.5l3 1.5" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-800 sm:text-base dark:text-gray-200">
                            Google Authenticator
                        </span>
                    </div>
                    <div class="mt-3 hidden h-3 w-3 rounded-full bg-blue-600 peer-checked:block"></div>
                </label>

                <!-- Email -->
                <label
                    class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-6 transition hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:bg-gray-700">
                    <input type="radio" name="method" value="email" class="peer hidden" required>

                    <div class="flex flex-col items-center gap-3">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                            <!-- Email Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.6" stroke="currentColor"
                                class="h-8 w-8 text-blue-600 dark:text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0L12 12.75 2.25 6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5H4.5a2.25 2.25 0 0 0-2.25 2.25" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-800 sm:text-base dark:text-gray-200">
                            OTP via Email
                        </span>
                    </div>
                    <div class="mt-3 hidden h-3 w-3 rounded-full bg-blue-600 peer-checked:block"></div>
                </label>
            </div>

            @error('method')
                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror

            <!-- Tombol -->
            <div class="space-y-3">
                <button type="submit"
                    class="w-full rounded-lg bg-blue-600 py-3 font-semibold text-white transition duration-150 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    Lanjutkan
                </button>

                <a href="{{ route('logout') }}"
                    class="block w-full rounded-lg bg-gray-100 py-3 font-semibold text-gray-700 transition duration-150 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Batal
                </a>
            </div>
        </form>
    </div>

</body>

</html>
