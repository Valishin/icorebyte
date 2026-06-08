<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols</title>

        <!-- ── SEO base ──────────────────────────────────────────── -->
        <meta name="description" content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles. Venta de equipos. Desarrollo web. ☎ +34 678 758 210">
        <meta name="keywords"    content="tienda informática Sant Feliu de Guíxols, reparación iPhone Sant Feliu, reparación iPhone 15, reparación iPhone 15 Pro, reparación iPhone 15 Pro Max, reparación iPhone 14, reparación iPhone 14 Pro, reparación iPhone 14 Pro Max, reparación iPhone 13, reparación iPhone 13 Pro, reparación iPhone 12, reparación iPhone 11, reparación iPhone X, reparación iPhone XR, reparación iPhone XS, reparación iPhone 8, reparación iPhone 7, reparación iPhone SE, reparación ordenadores Girona, reparación portátiles Sant Feliu, reparación laptops, reparación tablets, servicio técnico informático, tienda informática Girona, informática Costa Brava, desarrollo web Sant Feliu, iCoreByte">
        <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
        <meta name="author"      content="iCoreByte">
        <link  rel="canonical"   href="{{ config('app.url') }}{{ request()->getPathInfo() }}">

        <!-- Open Graph -->
        <meta property="og:type"         content="website">
        <meta property="og:locale"       content="es_ES">
        <meta property="og:site_name"    content="iCoreByte">
        <meta property="og:title"        content="iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols">
        <meta property="og:description"  content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles. Desarrollo web profesional.">
        <meta property="og:url"          content="{{ config('app.url') }}">
        <meta property="og:image"        content="{{ config('app.url') }}/og-image.png">
        <meta property="og:image:width"  content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt"    content="iCoreByte — Tienda Informática Sant Feliu de Guíxols">

        <!-- Twitter Card -->
        <meta name="twitter:card"        content="summary_large_image">
        <meta name="twitter:title"       content="iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols">
        <meta name="twitter:description" content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles.">
        <meta name="twitter:image"       content="{{ config('app.url') }}/og-image.png">

        <!-- Geolocalización -->
        <meta name="geo.region"    content="ES-GI">
        <meta name="geo.placename" content="Sant Feliu de Guíxols, Girona">
        <meta name="geo.position"  content="41.7834;3.0319">
        <meta name="ICBM"          content="41.7834, 3.0319">

        <!-- Favicon -->
        <link rel="icon"             type="image/png" sizes="32x32" href="/favicon.png">
        <link rel="icon"             type="image/x-icon"            href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180"                href="/favicon.png">

        <!-- Preconnect: fuentes + Google Maps -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://maps.googleapis.com">
        <link rel="preconnect" href="https://maps.gstatic.com" crossorigin>

        <!-- Preload LCP: primera imagen del hero -->
        @php
            try { $heroPreload = Vite::asset('resources/assets/images/heroSlide1.jpg'); } catch (\Exception $e) { $heroPreload = null; }
        @endphp
        @if($heroPreload)
        <link rel="preload" as="image" href="{{ $heroPreload }}" fetchpriority="high">
        @endif

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
                    "image": "{{ config('app.url') }}/og-image.png",
                    "logo": {
                        "@@type": "ImageObject",
                        "url": "{{ config('app.url') }}/favicon.png",
                        "width": 512,
                        "height": 512
                    },
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
                        "latitude": "41.783112276338564",
                        "longitude": "3.036094176676324"
                    },
                    "openingHoursSpecification": [
                        {
                            "@@type": "OpeningHoursSpecification",
                            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
                            "opens": "10:00",
                            "closes": "13:30"
                        },
                        {
                            "@@type": "OpeningHoursSpecification",
                            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
                            "opens": "17:00",
                            "closes": "20:00"
                        },
                        {
                            "@@type": "OpeningHoursSpecification",
                            "dayOfWeek": ["Saturday"],
                            "opens": "10:00",
                            "closes": "13:00"
                        }
                    ],
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

        <!-- ── JSON-LD: FAQPage ───────────────────────────────────── -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "FAQPage",
            "mainEntity": [
                {
                    "@@type": "Question",
                    "name": "¿Cuánto cuesta cambiar la pantalla de un iPhone?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "El precio varía según el modelo. Para iPhone 11-15 el cambio de pantalla oscila entre 80€ y 200€ con piezas de calidad. Contáctanos al +34 678 758 210 o por WhatsApp para un presupuesto sin compromiso."
                    }
                },
                {
                    "@@type": "Question",
                    "name": "¿Cuánto tarda una reparación de iPhone?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "La mayoría de reparaciones (cambio de pantalla, batería, conector) se realizan el mismo día en 1-3 horas. Reparaciones de electrónica en placa o casos complejos pueden tardar 24-48 horas."
                    }
                },
                {
                    "@@type": "Question",
                    "name": "¿Ofrecéis garantía en las reparaciones?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "Sí, todas nuestras reparaciones incluyen garantía. El cambio de pantalla y batería tiene 6 meses de garantía y los componentes originales 12 meses."
                    }
                },
                {
                    "@@type": "Question",
                    "name": "¿Reparáis todos los modelos de iPhone?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "Sí, reparamos todos los modelos de iPhone: iPhone SE, 6, 6s, 7, 8, X, XR, XS, 11, 12, 13, 14 y 15, incluyendo versiones Plus, Pro y Pro Max."
                    }
                },
                {
                    "@@type": "Question",
                    "name": "¿Dónde está la tienda iCoreByte?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "Estamos en Carretera de Palamós 57, 17220 Sant Feliu de Guíxols, Girona. Abrimos de lunes a viernes de 10:00 a 13:30 y de 17:00 a 20:00, y los sábados de 10:00 a 13:00."
                    }
                },
                {
                    "@@type": "Question",
                    "name": "¿Reparáis ordenadores, portátiles y tablets?",
                    "acceptedAnswer": {
                        "@@type": "Answer",
                        "text": "Sí, reparamos todo tipo de dispositivos: ordenadores de sobremesa, portátiles y laptops de todas las marcas (Apple, HP, Dell, Lenovo, Asus, Acer), tablets iPad, Samsung, Huawei, e impresoras. También hacemos microsoldadura y reparación a nivel de placa."
                    }
                }
            ]
        }
        </script>

        <!-- Fonts -->
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
