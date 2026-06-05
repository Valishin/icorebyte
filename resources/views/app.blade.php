<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'iCoreByte') }}</title>

        <!-- ── SEO base ──────────────────────────────────────────── -->
        <meta name="robots"  content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
        <meta name="author"  content="iCoreByte">
        <link  rel="canonical" href="{{ config('app.url') }}{{ request()->getPathInfo() }}">

        <!-- Geolocalización -->
        <meta name="geo.region"    content="ES-GI">
        <meta name="geo.placename" content="Sant Feliu de Guíxols, Girona">
        <meta name="geo.position"  content="41.7834;3.0319">
        <meta name="ICBM"          content="41.7834, 3.0319">
        <link rel="icon" type="image/png" href="/favicon.png" />

        <!-- ── JSON-LD: LocalBusiness + ComputerRepair ──────────── -->
        {{-- Los @ del JSON-LD se escapan con @@ para que Blade no los interprete --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@graph": [
                {
                    "@@type": ["LocalBusiness", "ComputerRepair"],
                    "@@id": "{{ config('app.url') }}/#business",
                    "name": "iCoreByte",
                    "description": "Tienda de informática en Sant Feliu de Guíxols (Girona). Reparación de iPhone 6, 6s, 7, 8, X, XR, XS, 11, 12, 13, 14 y 15 (todos los modelos). Reparación de ordenadores, portátiles, tablets e impresoras. Desarrollo web profesional.",
                    "url": "{{ config('app.url') }}",
                    "telephone": "+34678758210",
                    "email": "info@icorebyte.com",
                    "priceRange": "€€",
                    "address": {
                        "@@type": "PostalAddress",
                        "streetAddress": "Carretera de Palamós 57",
                        "addressLocality": "Sant Feliu de Guíxols",
                        "addressRegion": "Girona",
                        "postalCode": "17220",
                        "addressCountry": "ES"
                    },
                    "geo": {
                        "@@type": "GeoCoordinates",
                        "latitude": "41.7834",
                        "longitude": "3.0319"
                    },
                    "hasOfferCatalog": {
                        "@@type": "OfferCatalog",
                        "name": "Servicios iCoreByte",
                        "itemListElement": [
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Reparación iPhone",
                                    "description": "Reparación de iPhone SE, iPhone 6, 6s, 6 Plus, 6s Plus, iPhone 7, 7 Plus, iPhone 8, 8 Plus, iPhone X, iPhone XR, XS, XS Max, iPhone 11, 11 Pro, 11 Pro Max, iPhone 12, 12 mini, 12 Pro, 12 Pro Max, iPhone 13, 13 mini, 13 Pro, 13 Pro Max, iPhone 14, 14 Plus, 14 Pro, 14 Pro Max, iPhone 15, 15 Plus, 15 Pro, 15 Pro Max. Cambio de pantalla, batería, conector, cámara y más."
                                }
                            },
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Reparación de ordenadores y portátiles",
                                    "description": "Diagnóstico, reparación y mantenimiento de ordenadores de sobremesa, laptops y portátiles de todas las marcas: Apple, HP, Dell, Lenovo, Asus, Acer, MSI."
                                }
                            },
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Reparación de tablets",
                                    "description": "Reparación de tablets iPad, Samsung Galaxy Tab, Huawei, Xiaomi y otras marcas. Cambio de pantalla, batería y conectores."
                                }
                            },
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Tienda de informática",
                                    "description": "Venta de componentes, periféricos, ordenadores, portátiles y accesorios informáticos en Sant Feliu de Guíxols."
                                }
                            },
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Desarrollo web",
                                    "description": "Diseño y desarrollo de páginas web, tiendas online, CRM y aplicaciones web a medida para empresas y autónomos."
                                }
                            },
                            {
                                "@@type": "Offer",
                                "itemOffered": {
                                    "@@type": "Service",
                                    "name": "Mantenimiento informático empresarial",
                                    "description": "Soporte técnico remoto y presencial, mantenimiento preventivo y correctivo para empresas de la Costa Brava y Girona."
                                }
                            }
                        ]
                    }
                },
                {
                    "@@type": "WebSite",
                    "@@id": "{{ config('app.url') }}/#website",
                    "url": "{{ config('app.url') }}",
                    "name": "iCoreByte",
                    "description": "Tienda de informática y servicio técnico en Sant Feliu de Guíxols",
                    "inLanguage": "es-ES",
                    "publisher": {
                        "@@id": "{{ config('app.url') }}/#business"
                    }
                }
            ]
        }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased grid-bg">
        @inertia
    </body>
</html>
