<script setup lang="ts">
  import CFeatureGrid from '@/Components/CFeatureGrid.vue'
  import CPricing from '@/Components/CPricing.vue'
  import CShowcase from '@/Components/CShowcase.vue'
  import CTabShowcase from '@/Components/CTabShowcase.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { images } from '@/constants/images'
</script>

<template>
  <PublicLayout>
    <CShowcase
      overline="// SOFTWARE DE GESTIÓN"
      :titleHtml="`Gestiona tu taller <span>SAT</span> sin límites`"
      description="La plataforma web completa para talleres de informática. Gestiona clientes, técnicos, órdenes de reparación y analítica en tiempo real — todo desde un único panel de control."
      :features="[
        { icon: '', label: 'Multi-usuario' },
        { icon: '', label: 'Tiempo real' },
        { icon: '', label: 'Control de permisos' }
      ]"
      :primary-cta="{ label: 'Ver Planes y Precios →', href: '/planes' }"
      :secondary-cta="{ label: 'Ver Demo', href: '/demo' }"
      :image="images.appsatProject"
      image-alt="Captura del software SAT Manager"
    />

    <CTabShowcase
      overline="// Interfaz de la App"
      title="Diseñada para ser usada"
      description="Interfaz limpia e intuitiva. Tus técnicos aprenden a usarla en minutos."
      title-color="primary"
      :tabs="[
        {
          label: 'Dashboard',
          title: 'Vista general en tiempo real',
          description:
            'Panel principal con KPIs del taller: SATs abiertos, en reparación, finalizados hoy y pendientes de entrega. Gráficas semanales y actividad reciente de un vistazo.',
          image: images.appsatProject,
          url: 'app.satmanager.io/dashboard'
        },
        {
          label: 'Nuevo SAT',
          title: 'Abre un SAT en segundos',
          description:
            'Formulario guiado para registrar el equipo, el problema, los accesorios entregados y obtener la firma digital del cliente antes de empezar la reparación.',
          image: images.appsatProject,
          url: 'app.satmanager.io/sat/nuevo'
        },
        {
          label: 'Clientes',
          title: 'Ficha completa de cada cliente',
          description:
            'Historial de SATs, equipos reparados, datos de contacto y notas internas. Busca cualquier cliente en milisegundos por nombre, email o teléfono.',
          image: images.appsatProject,
          url: 'app.satmanager.io/clientes'
        },
        {
          label: 'Analítica',
          title: 'Métricas que importan',
          description:
            'Tiempo medio de reparación, ingresos por período, SATs por técnico y tasa de resolución. Todo en gráficas claras para tomar decisiones informadas.',
          image: images.imageAppSatAnalitics,
          url: 'app.satmanager.io/analitica'
        },
        {
          label: 'Usuarios y Permisos',
          title: 'Control total de accesos',
          description:
            'Define qué puede ver y hacer cada usuario. Roles de administrador, técnico y recepción con accesos granulares a cada sección de la plataforma.',
          image: images.appsatProject,
          url: 'app.satmanager.io/usuarios'
        }
      ]"
    />

    <CFeatureGrid
      overline="// Características"
      title="Todo lo que necesita tu taller"
      description="Diseñado específicamente para talleres de informática. Sin funciones innecesarias, con todo lo esencial."
      title-color="primary"
      :features="[
        {
          icon: 'IconMaintenance',
          title: 'Gestión de SATs',
          description:
            'Crea, asigna y sigue el estado de cada orden de reparación en tiempo real. Historial completo por equipo y cliente.'
        },
        {
          icon: 'IconSupport',
          iconColor: 'primary',
          title: 'Base de Clientes',
          description:
            'Ficha completa de cada cliente con historial de SATs, equipos reparados y datos de contacto centralizados.'
        },
        {
          icon: 'IconSoftware',
          iconColor: 'secondary',
          title: 'Permisos por Rol',
          description:
            'Define qué puede ver y hacer cada usuario. Roles de administrador, técnico y recepción con accesos granulares.'
        },
        {
          icon: 'IconSupport',
          iconColor: 'secondary',
          title: 'Gestión de Técnicos',
          description:
            'Asigna SATs a técnicos, controla la carga de trabajo y mide el rendimiento individual de cada miembro.'
        },
        {
          icon: 'IconEmail',
          iconColor: 'primary',
          title: 'Formulario de Entrada',
          description:
            'Formulario detallado al crear un SAT: descripción del problema, accesorios entregados, fotos y firma digital del cliente.'
        },
        {
          icon: 'IconDesktop',
          iconColor: 'primary',
          title: 'Analítica y Reportes',
          description:
            'Dashboard con métricas clave: SATs por estado, tiempos medios de reparación, ingresos y rendimiento del equipo.'
        }
      ]"
    />
    <CPricing
      overline="// Planes y Precios"
      title="Sin sorpresas. Sin complicaciones."
      description="30 días de prueba gratuita en todos los planes. Sin tarjeta de crédito."
      title-color="primary"
      :plans="[
        {
          name: 'Starter',
          description: 'Perfecto para talleres pequeños que empiezan.',
          price: '19,99€',
          priceSuffix: '/mes',
          features: [
            'Hasta 2 técnicos',
            'SATs ilimitados',
            'Gestión de clientes',
            'Formulario de entrada',
            'Notificaciones por email',
            'Soporte por email'
          ],
          cta: { label: 'Empezar gratis 30 días', href: '/registro', variant: 'ghost' }
        },
        {
          name: 'Pro',
          description: 'Para talleres en crecimiento con equipo completo.',
          price: '99€',
          priceSuffix: '/mes',
          highlighted: true,
          features: [
            'Técnicos ilimitados',
            'SATs ilimitados',
            'Roles y permisos avanzados',
            'Analítica completa',
            'Inventario de piezas',
            'Notificaciones SMS + email',
            'Exportación de reportes PDF',
            'Soporte prioritario'
          ],
          cta: { label: 'Empezar con Pro', href: '/registro?plan=pro', variant: 'primary' }
        }
      ]"
    />
  </PublicLayout>
</template>
