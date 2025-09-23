<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <link rel="icon" href="{{ asset('images/favicon.ico') }}">

    @routes

    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])

    @inertiaHead

</head>

<body translate="no">
    @inertia
</body>

</html>

<!-- Custom Styles -->
<style>
    <?php
        function hexToRgba($hex, $alpha = 1.0) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) === 3) {
                $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
            }
            else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            return "rgba($r, $g, $b, $alpha)";
        }

        function lighten($hex, $percent) {
            $hex = str_replace('#', '', $hex);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            $r = round($r + ($percent / 100) * (255 - $r));
            $g = round($g + ($percent / 100) * (255 - $g));
            $b = round($b + ($percent / 100) * (255 - $b));

            return sprintf("#%02x%02x%02x", $r, $g, $b);
        }

        function darken($hex, $percent) {
            $hex = str_replace('#', '', $hex);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            $r = round($r * (1 - $percent / 100));
            $g = round($g * (1 - $percent / 100));
            $b = round($b * (1 - $percent / 100));

            return sprintf("#%02x%02x%02x", $r, $g, $b);
        }

        $uniqueColor = \App\Helpers\ConfigHelper::get('unique_color');
        $styleColor  = \App\Helpers\ConfigHelper::get('style_color');
        $textColor   = \App\Helpers\ConfigHelper::get('text_color');
        $uniqueColorRgba = hexToRgba($uniqueColor, 0.5);

        $uniqueLighten3  = lighten($uniqueColor, 3);
        $uniqueLighten5  = lighten($uniqueColor, 5);
        $uniqueLighten10 = lighten($uniqueColor, 10);
        $uniqueLighten20 = lighten($uniqueColor, 20);
        $uniqueLighten45 = lighten($uniqueColor, 45);
        $uniqueLighten50 = lighten($uniqueColor, 50);
        $uniqueLighten60 = lighten($uniqueColor, 60);

        $uniqueDarken3  = darken($uniqueColor, 3);
        $uniqueDarken5  = darken($uniqueColor, 5);
        $uniqueDarken10 = darken($uniqueColor, 10);
        $uniqueDarken20 = darken($uniqueColor, 20);
        $uniqueDarken45 = darken($uniqueColor, 45);
        $uniqueDarken50 = darken($uniqueColor, 50);
        $uniqueDarken60 = darken($uniqueColor, 60);

        $styleLighten3  = lighten($styleColor, 3);
        $styleLighten5  = lighten($styleColor, 5);
        $styleLighten10 = lighten($styleColor, 10);
        $styleLighten20 = lighten($styleColor, 20);
        $styleLighten45 = lighten($styleColor, 45);
        $styleLighten50 = lighten($styleColor, 50);
        $styleLighten60 = lighten($styleColor, 60);

        $styleDarken3  = darken($styleColor, 3);
        $styleDarken5  = darken($styleColor, 5);
        $styleDarken10 = darken($styleColor, 10);
        $styleDarken20 = darken($styleColor, 20);
        $styleDarken45 = darken($styleColor, 45);
        $styleDarken50 = darken($styleColor, 50);
        $styleDarken60 = darken($styleColor, 60);
    ?>

    :root {
        --unique-color: {{ $uniqueColor }};
        --unique-color-rgba: {{ $uniqueColorRgba }};
        --unique-lighten-3: {{ $uniqueLighten3 }};
        --unique-lighten-5: {{ $uniqueLighten5 }};
        --unique-lighten-10: {{ $uniqueLighten10 }};
        --unique-lighten-20: {{ $uniqueLighten20 }};
        --unique-lighten-45: {{ $uniqueLighten45 }};
        --unique-lighten-50: {{ $uniqueLighten50 }};
        --unique-lighten-60: {{ $uniqueLighten60 }};
        --unique-darken-3: {{ $uniqueDarken3 }};
        --unique-darken-5: {{ $uniqueDarken5 }};
        --unique-darken-10: {{ $uniqueDarken10 }};
        --unique-darken-20: {{ $uniqueDarken20 }};
        --unique-darken-45: {{ $uniqueDarken45 }};
        --unique-darken-50: {{ $uniqueDarken50 }};
        --unique-darken-60: {{ $uniqueDarken60 }};

        --style-color: {{ $styleColor }};
        --style-lighten-3: {{ $styleLighten3 }};
        --style-lighten-5: {{ $styleLighten5 }};
        --style-lighten-10: {{ $styleLighten10 }};
        --style-lighten-20: {{ $styleLighten20 }};
        --style-lighten-45: {{ $styleLighten45 }};
        --style-lighten-50: {{ $styleLighten50 }};
        --style-lighten-60: {{ $styleLighten60 }};
        --style-darken-3: {{ $styleDarken3 }};
        --style-darken-5: {{ $styleDarken5 }};
        --style-darken-10: {{ $styleDarken10 }};
        --style-darken-20: {{ $styleDarken20 }};
        --style-darken-45: {{ $styleDarken45 }};
        --style-darken-50: {{ $styleDarken50 }};
        --style-darken-60: {{ $styleDarken60 }};

        --text-color: {{ $textColor }};
        --gradient-color-unique: linear-gradient(135deg, {{ $uniqueColor }}, {{ $styleColor }});
    }

    .unique_bg {
        background: var(--unique-color) !important;
        color: white !important;
    }

    .unique_color {
        color: var(--unique-color) !important;
    }

    .btn.unique_bg:hover,
    a.unique_bg:hover {
        background: var(--unique-color) !important;
        opacity: 0.9 !important;
        box-shadow: 0 0 5px white !important;
        color: white !important;
    }

    #sidenav-collapse-main a.nav-link:hover {
        background: var(--unique-color) !important;
        opacity: 0.9 !important;
        box-shadow: 0 0 5px white !important;
        color: white !important;
    }

    .style_color {
        background: var(--style-color) !important;
        color: var(--text-color) !important;
    }

    .backGroundColorPrincipal {
        background: var(--style-color) !important;
    }

    .textColorPrincipal {
        color: var(--text-color) !important;
    }

    .selected-button {
        background: var(--style-color) !important;
        color: var(--text-color) !important;
    }

    .multiselect.is-active {
        border: 1px solid var(--unique-color) !important;
        box-shadow: 0 0 5px var(--unique-color-rgba) !important;
    }

    .multiselect-option.is-selected.is-pointed {
        background: var(--unique-color) !important;
        color: var(--ms-option-color-selected-pointed, #fff);
    }

    .multiselect-option.is-selected {
        background: var(--unique-color) !important;
        color: #fff;
    }

    .recover-pass {
        color: var(--text-color) !important;
    }

    .fade-in {
        opacity: 0;
        animation: fadeInAnimation .5s ease-in forwards;
    }

    @keyframes fadeInAnimation {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .screen-width .modal-dialog.modal-lg {
        max-width: 1400px;
    }

    .page-item.active .page-link {
        background: var(--style-color) !important;
        border-color: var(--style-color) !important;
    }

    .gradient-unique {
        background: var(--gradient-color-unique) !important;
        color: white !important;
    }

    .text-gradient-unique {
        background: var(--gradient-color-unique);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

