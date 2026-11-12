import { images } from '@/constants/images'

export interface Service {
  slug: string
  title: string
  icon: string
  image: string
  shortDesc: string
  tasks: string[]
  timeEstimate?: string
  priceFrom?: string
  cta: string
  anchor: string
}

export const services: Service[] = [
  {
    slug: 'moviles-tablets',
    anchor: 'moviles-tablets',
    title: 'Móviles y Tablets',
    icon: 'IconMobile',
    image: images.imagePhoneRepairReal,
    shortDesc: 'Reparamos móviles y tablets de todas las marcas con piezas de calidad y diagnóstico previo gratuito.',
    tasks: [
      'Cambio de pantalla y cristal',
      'Sustitución de batería',
      'Reparación de conector de carga',
      'Cambio de tapa trasera',
      'Reparación de cámara',
      'Daños por líquidos',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'portatiles',
    anchor: 'portatiles',
    title: 'Portátiles',
    icon: 'IconLaptop',
    image: images.imageLaptopRepairReal,
    shortDesc: 'Reparación y mantenimiento de portátiles de cualquier marca y modelo, con garantía incluida.',
    tasks: [
      'Cambio de pantalla',
      'Sustitución de teclado',
      'Reparación de bisagras',
      'Limpieza y pasta térmica',
      'Ampliación de SSD y RAM',
      'Carga y conectores',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'pc-sobremesa',
    anchor: 'pc-sobremesa',
    title: 'PC de Sobremesa',
    icon: 'IconDesktop',
    image: images.imagePcRepairReal,
    shortDesc: 'Diagnóstico, reparación, montaje a medida y upgrades para equipos de sobremesa y gaming.',
    tasks: [
      'Diagnóstico y reparación',
      'Montaje a medida',
      'Configuración gaming',
      'Upgrades de componentes',
      'Limpieza y mantenimiento',
      'Instalación de sistemas operativos',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'consolas',
    anchor: 'consolas',
    title: 'Consolas',
    icon: 'IconElectronic',
    image: images.imageMotherboard,
    shortDesc: 'Reparamos PlayStation, Xbox, Nintendo Switch y otras consolas con garantía en la reparación.',
    tasks: [
      'Reparación de salida HDMI',
      'Solución de sobrecalentamiento',
      'Reparación de mandos',
      'Sustitución de lectores ópticos',
      'Problemas de arranque',
      'Daños por líquidos',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'microsoldadura',
    anchor: 'microsoldadura',
    title: 'Microsoldadura y Placa',
    icon: 'IconElectronic',
    image: images.imageSolderingReal,
    shortDesc: 'Reparación avanzada a nivel de componente con microscopio, osciloscopio y estación de soldadura profesional.',
    tasks: [
      'Equipos que no encienden',
      'Daños por líquidos',
      'Sustitución de componentes SMD',
      'Reparación de placas base',
      'Diagnóstico avanzado',
      'Reballing de chips',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'recuperacion-datos',
    anchor: 'recuperacion-datos',
    title: 'Recuperación de Datos',
    icon: 'IconHarddisk',
    image: images.imageMotherboardMobile,
    shortDesc: 'Recuperación de datos en discos duros, SSDs, pendrives y sistemas NAS dañados o corruptos.',
    tasks: [
      'Discos duros mecánicos dañados',
      'SSDs y memorias flash',
      'Sistemas NAS (Synology, QNAP)',
      'Dispositivos con daño físico',
      'Particiones corruptas',
      'Copias de seguridad preventivas',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'mantenimiento-it',
    anchor: 'mantenimiento-it',
    title: 'Mantenimiento IT',
    icon: 'IconMaintenance',
    image: images.imageItMaintanceReal,
    shortDesc: 'Servicios de mantenimiento informático preventivo y correctivo para particulares y empresas.',
    tasks: [
      'Mantenimiento preventivo',
      'Eliminación de virus y malware',
      'Optimización del sistema',
      'Instalación y configuración de software',
      'Actualizaciones de seguridad',
      'Configuración de redes',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar presupuesto',
  },
  {
    slug: 'soporte-empresarial',
    anchor: 'soporte-empresarial',
    title: 'Soporte Empresarial',
    icon: 'IconSupport',
    image: images.imageSupportItReal,
    shortDesc: 'Planes de soporte IT a medida para empresas, con SLA garantizado y atención remota y presencial.',
    tasks: [
      'Planes de mantenimiento a medida',
      'Soporte remoto y presencial',
      'SLA garantizado',
      'Gestión de puestos de trabajo',
      'Configuración de redes y servidores',
      'Sustitución de equipos',
    ],
    timeEstimate: 'TODO: confirmar plazos',
    priceFrom: 'TODO: confirmar precios',
    cta: 'Solicitar plan',
  },
]
