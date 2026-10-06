<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->form->validate();

        $this->form->authenticate();

        Session::regenerate();

        Session::flash('just_logged_in', true);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col lg:flex-row bg-white overflow-x-hidden font-sans">
    
    <!-- LEFT SIDE: Form & Branding -->
    <div class="w-full lg:w-[52%] xl:w-[50%] min-h-screen flex flex-col justify-between px-6 sm:px-12 md:px-16 lg:px-16 xl:px-24 py-8 sm:py-10">
        
        <div></div>

        <!-- Center Form Container -->
        <div class="max-w-md w-full mx-auto my-auto py-8 space-y-6">
            
            <!-- Logo Icon Badge & Titles -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-terracotta-500 shadow-md shadow-terracotta-500/20 mb-3">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Cressco" class="h-8 w-auto object-contain brightness-0 invert">
                </div>
                
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                    Welcome back to <span class="text-terracotta-500">Cressco</span>
                </h1>
                
                <p class="text-xs sm:text-sm text-gray-500">
                    Login with your email and password
                </p>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="mb-2" :status="session('status')" />

            <!-- Login Form -->
            <form wire:submit="login" method="POST" x-data="{ showPassword: false }" class="space-y-4">
                @csrf
                
                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Email
                    </label>
                    <input wire:model="form.email"
                           id="email"
                           type="email"
                           name="email"
                           required
                           autofocus
                           autocomplete="username"
                           placeholder="Enter your email"
                           class="w-full h-11 px-3.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
                </div>

                <!-- Password with Show/Hide Toggle -->
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Password
                    </label>

                    <div class="relative">
                        <input wire:model="form.password"
                               x-ref="pwdInput"
                               id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="Enter your password"
                               class="w-full h-11 pl-3.5 pr-10 rounded-xl border border-gray-200 bg-white text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs" />

                        <button type="button"
                                @click="showPassword = !showPassword; $refs.pwdInput.type = showPassword ? 'text' : 'password'"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-hidden cursor-pointer"
                                tabindex="-1"
                                aria-label="Toggle password visibility">
                            <!-- Eye open -->
                            <svg x-show="!showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye slash -->
                            <svg x-show="showPassword" x-cloak class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none text-gray-600 hover:text-gray-900">
                        <input wire:model="form.remember"
                               id="remember"
                               type="checkbox"
                               class="w-4 h-4 rounded-md border-gray-300 text-terracotta-500 focus:ring-terracotta-500 cursor-pointer shadow-2xs"
                               name="remember">
                        <span>Keep me sign-in to Cressco</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="font-semibold text-gray-900 hover:text-terracotta-600 underline transition"
                           wire:navigate>
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full h-11 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 active:bg-terracotta-700 text-white font-semibold text-sm transition duration-150 shadow-xs hover:shadow-md flex items-center justify-center gap-2 cursor-pointer">
                        <span>Login</span>
                    </button>
                </div>
            </form>

            <!-- Social Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200/80"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-gray-400 font-medium">Or continue with</span>
                </div>
            </div>

            <!-- Social Buttons (Google & Apple) -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button"
                        class="h-11 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-gray-700 flex items-center justify-center gap-2.5 text-xs font-semibold shadow-2xs transition cursor-pointer">
                    <!-- Google SVG -->
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                        <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.1s.7 5.4 1.9 7.8l3.7-2.9c0-.4 0-.8 0-1.2z"/>
                        <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z"/>
                    </svg>
                    <span>Google</span>
                </button>

                <button type="button"
                        class="h-11 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-gray-700 flex items-center justify-center gap-2.5 text-xs font-semibold shadow-2xs transition cursor-pointer">
                    <!-- Apple SVG -->
                    <svg class="w-4 h-4 fill-current text-gray-900" viewBox="0 0 24 24">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.61-.75 1.04-1.8 0.92-2.85-.92.04-2.02.62-2.67 1.37-.56.65-.96 1.72-.83 2.74 1.03.08 2.06-.52 2.58-1.26z"/>
                    </svg>
                    <span>Apple</span>
                </button>
            </div>

            <!-- Create Account Link -->
            <p class="text-xs text-gray-500 text-center pt-2">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-bold text-gray-900 hover:text-terracotta-600 underline transition" wire:navigate>
                    Create account
                </a>
            </p>
        </div>

        <!-- Bottom Page Footer -->
        <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 gap-2">
            <p>&copy; {{ date('Y') }} Cressco</p>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-gray-600 transition">Privacy Policy</a>
                <a href="#" class="hover:text-gray-600 transition">Support</a>
            </div>
        </div>

    </div>

    <!-- RIGHT SIDE: Modern Tutoring Hero Banner -->
    <div class="hidden lg:flex lg:w-[48%] xl:w-[50%] p-4 lg:p-6 sticky top-0 h-screen">
        <div class="relative h-full w-full rounded-3xl overflow-hidden shadow-2xl bg-gray-950 flex flex-col justify-end p-8 xl:p-12 text-white">
            
            <!-- Hero Background Image -->
            <img src="{{ asset('images/auth-hero.jpg') }}"
                 alt="Cressco Modern Tutoring Center"
                 class="absolute inset-0 h-full w-full object-cover">
            
            <!-- Smooth Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950/95 via-gray-950/40 to-transparent"></div>

            <!-- Copywriting Content (Single Image, No Slider Indicator) -->
            <div class="relative z-10 space-y-3 max-w-xl">
                <h2 class="text-2xl xl:text-3xl font-bold tracking-tight text-white leading-snug">
                    Kelola Operasional Bimbel Lebih Cerdas & Terintegrasi
                </h2>
                <p class="text-xs xl:text-sm text-gray-200/90 leading-relaxed">
                    Platform all-in-one terpercaya untuk manajemen siswa, presensi kelas, jadwal mengajar tutor, dan pencatatan keuangan bimbingan belajar modern.
                </p>
            </div>

        </div>
    </div>

</div>
