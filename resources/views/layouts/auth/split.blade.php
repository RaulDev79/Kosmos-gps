<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        @php
            $loginBackground = asset('storage/img/unidades-de-carga-con-rastreo-satelital-kosmos.jpg');
            $whiteLogo = asset('storage/img/kosmos-logo-blanco.png');
        @endphp

        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col overflow-hidden p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div
                    class="absolute inset-0 bg-cover bg-center"
                    style="background-image: url('{{ $loginBackground }}');"
                ></div>
                <div class="absolute inset-0 bg-neutral-950/70"></div>
                <div class="absolute inset-0 bg-linear-to-t from-neutral-950 via-neutral-950/45 to-neutral-950/20"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center" wire:navigate>
                    <img
                        src="{{ $whiteLogo }}"
                        alt="Kosmos GPS"
                        class="h-14 w-auto object-contain"
                    >
                </a>

                <div class="relative z-20 mt-auto max-w-xl space-y-4">
                    <flux:heading size="xl" class="text-white">
                        Control de flota con visibilidad en tiempo real.
                    </flux:heading>
                    <p class="max-w-lg text-sm leading-6 text-white/80">
                        Administra vehículos, conductores, viajes, combustible y mantenimientos
                        desde una sola plataforma.
                    </p>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex justify-center lg:hidden" wire:navigate>
                        <img
                            src="{{ $whiteLogo }}"
                            alt="Kosmos GPS"
                            class="h-12 w-auto rounded-xl bg-neutral-950 px-4 py-2 object-contain shadow-sm"
                        >
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
